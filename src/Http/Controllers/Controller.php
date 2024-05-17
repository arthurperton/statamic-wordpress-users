<?php

namespace Statamic\Addons\WordpressUsers\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use ParseCsv\Csv;
use Statamic\Addons\WordpressUsers\Exceptions\CsvFileException;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Blueprint;
use Statamic\Facades\User;
use Statamic\Licensing\LicenseManager as Licenses;

class Controller extends BaseController
{
    use AuthorizesRequests;

    private const PACKAGE_NAME = 'arthurperton/wordpress-users';

    private const PERMISSION = 'access wordpress-users addon';

    private const KEY_CSV = 'csv';

    private const KEY_CSV_HASH = 'csv-hash';

    private const KEY_CONFIG = 'config';

    public function index()
    {
        $this->authorize(self::PERMISSION);

        $users = User::all()->filter(function ($user) {
            return $user->get('wp__password_hash', false);
        });

        $userCount = $users->count();

        $doneCount = $users->filter(function ($user) {
            return $user->password();
        })->count();

        return view(
            'wordpress-users::index',
            compact('userCount', 'doneCount')
        );
    }

    public function edit(Request $request, $step)
    {
        $this->authorize(self::PERMISSION);

        try {
            $values = $this->getValues($step);

            $blueprint = $this->getBlueprint($step);

            $fields = $blueprint->fields()->addValues($values)->preProcess();

            return view('wordpress-users::import', [
                'step' => $step,
                'stepcount' => 3,
                'blueprint' => $blueprint->toPublishArray(),
                'values' => $fields->values(),
                'meta' => $fields->meta(),
            ]);
        } catch (CsvFileException $e) {
            // $this->configPut('file', null);
            return redirect($step == 1 ? cp_route('wordpress-users.index') : cp_route('wordpress-users.edit', 1))
                ->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, $step)
    {
        $this->authorize(self::PERMISSION);

        $patch = $request->all();

        $fields = $this->getBlueprint($step)->fields()->addValues($patch);

        $fields->validate();

        try {
            if (isset($patch['file'])) {
                $hash = $this->assetHash($patch['file']);
                if ($hash !== $this->csvHash()) {
                    $this->clearCache();
                    $this->csvHash($hash);
                }
            }
        } catch (CsvFileException $e) {
            return response(['message' => $e->getMessage(), 'errors' => ['file' => [$e->getMessage()]]], 422);
        }

        $this->configPatch($patch);

        return response('');
    }

    public function import(Request $request)
    {
        $this->authorize(self::PERMISSION);

        $csv = $this->csv();

        $extra_fields = collect($this->configGet('field_mapping_extra'))->map(function ($field) {
            if (! is_string($field['handle']) || strlen($field['handle']) === 0) {
                $field['handle'] = $field['column'];
            }

            return $field;
        });

        $field_mapping = collect($this->configGet('field_mapping'))->merge($extra_fields);

        $role_mapping = collect($this->configGet('role_mapping'));

        $rows = collect($csv->data);

        $errors = collect();

        $users = $rows
            ->map(function ($row) use ($field_mapping) {
                return $field_mapping->mapWithKeys(function ($map) use ($row) {
                    return [$map['handle'] => $row[$map['column']] ?? null];
                });
            })
            ->map(function ($user, $i) use (&$errors, $rows) {
                $problems = [];
                if (! isset($user['email'])) {
                    $problems[] = 'email address is missing';
                }
                if (! isset($user['name'])) {
                    $problems[] = 'name is missing';
                }
                if (! isset($user['wp__password_hash'])) {
                    $problems[] = 'password hash is missing';
                }
                if (User::findByEmail($user['email'])) {
                    $problems[] = 'email address in use';
                }
                if ($problems) {
                    $errors->push([
                        'user' => ($user['name'] ?? 'NO NAME').' ('.($user['email'] ?? 'NO EMAIL').')',
                        'message' => implode(', ', $problems),
                        'row' => implode(', ', $rows[$i]),
                    ]);
                }

                return $problems ? null : $user;
            });

        if ($errors->count() > 0 && ! $request->query('force', false)) {
            $userCount = $users->count();

            return view('wordpress-users::review', compact('errors', 'userCount'));
        }

        $users = $users->filter();

        $users
            ->map(function ($user) use ($role_mapping) {
                $wpRole = $user['roles'] ?? null;
                if (! $wpRole) {
                    return $user;
                }

                $map = $role_mapping->firstWhere('wp_role', $wpRole);

                if (! $map) {
                    return $user;
                }

                $user['roles'] = $map['roles'];
                $user['groups'] = $map['groups'];

                return $user;
            })
            ->each(function ($user) {
                $email = $user['email'];

                unset($user['email']);

                User::make()
                    ->email($email)
                    ->data($user)
                    ->save();
            });

        return redirect(cp_route('wordpress-users.index'))->with('success', 'You just imported '.$users->count().' users.');
    }

    public function valid(Licenses $licenses)
    {
        $this->authorize(self::PERMISSION);

        return optional($licenses->addons()->get(self::PACKAGE_NAME))->valid();
    }

    private function getBlueprint($step)
    {
        $fields = [];

        if ($step == 1) {
            $container = config('statamic.wordpress_users.asset_container') ?? AssetContainer::all()->first()->handle();

            $fields = [
                'section' => [
                    'type' => 'section',
                    'display' => 'File Upload',
                    'instructions' => 'Please select or upload your CSV export file here. You can export your WordPress users using a free plugin like <a href="https://wordpress.org/plugins/import-users-from-csv-with-meta/" target="_blank" rel="noopener noreferrer">this one</a>.',
                ],
                'file' => [
                    'type' => 'assets',
                    'display' => 'CSV Export File',
                    // 'instructions' => 'Please select or upload your .csv file.',
                    'container' => $container,
                    'max_files' => 1,
                    'required' => true,
                ],
            ];
        } elseif ($step == 2) {
            $fields = [
                'section' => [
                    'type' => 'section',
                    'display' => 'Field Mapping',
                    'instructions' => 'Now you can specify how the data will be imported.',
                ],
                'field_mapping' => [
                    'type' => 'grid',
                    'display' => 'Required Fields',
                    'instructions' => 'If you used the recommended plugin for the export, these should have already been filled in for you.',
                    'classes' => 'wordpress-users-grid',
                    'reorderable' => false,
                    'fields' => [
                        ['handle' => 'column', 'field' => [
                            'type' => 'select',
                            'display' => 'CSV Column',
                            'options' => array_combine($this->csv()->titles, $this->csv()->titles),
                            'required' => true,
                        ]],
                        ['handle' => 'handle', 'field' => [
                            'type' => 'hidden',
                        ]],
                        ['handle' => 'title', 'field' => [
                            'type' => 'text',
                            'display' => 'User Field',
                            'read_only' => true,
                        ]],
                    ],
                ],
                'field_mapping_extra' => [
                    'type' => 'grid',
                    'display' => 'Custom Fields',
                    'instructions' => 'You can also import custom data if you want.',
                    'fields' => [
                        ['handle' => 'column', 'field' => [
                            'type' => 'select',
                            'display' => 'CSV Column',
                            'options' => array_combine($this->csv()->titles, $this->csv()->titles),
                            'required' => true,
                        ]],
                        ['handle' => 'handle', 'field' => [
                            'type' => 'text',
                            'display' => 'Rename (optional)',
                            'options' => array_combine($this->csv()->titles, $this->csv()->titles),
                            'required' => false,
                        ]],
                    ],
                ],
            ];
        } elseif ($step == 3) {
            $fields = [
                'section' => [
                    'type' => 'section',
                    'display' => 'Role Mapping',
                    'instructions' => 'You can optionally map your WordPress roles to any of your Statamic roles and groups.',
                ],
                'role_mapping' => [
                    'type' => 'grid',
                    'display' => 'Role Mapping',
                    'instructions' => Arr::get($this->getValues(3), 'role_mapping')
                        ? null 
                        : 'No roles were found in your export file.',
                    'classes' => 'wordpress-users-grid',
                    'reorderable' => false,
                    'fields' => [
                        ['handle' => 'wp_role', 'field' => ['type' => 'text', 'display' => 'WordPress Role', 'read_only' => true]],
                        ['handle' => 'roles', 'field' => ['type' => 'user_roles', 'display' => 'Roles']],
                        ['handle' => 'groups', 'field' => ['type' => 'user_groups', 'display' => 'Groups']],
                    ],
                ],
            ];
        }

        return Blueprint::makeFromFields($fields);
    }

    private function getValues($step)
    {
        $values = [];

        if ($step == 2) {
            $columns = collect($this->csv()->titles);

            $values = [
                'field_mapping' => collect([
                    ['handle' => 'name', 'title' => 'Name', 'column' => ['display_name', 'nickname']],
                    ['handle' => 'email', 'title' => 'Email address', 'column' => ['user_email', 'email']],
                    ['handle' => 'wp__password_hash', 'title' => 'Password hash', 'column' => ['user_pass', 'user_password', 'password']],
                    ['handle' => 'wp__id', 'title' => 'WordPress user ID', 'column' => ['source_user_id', 'user_id', 'id']],
                    ['handle' => 'roles', 'title' => 'Role', 'column' => ['role', 'user_role']],
                ])->map(function ($value) use ($columns) {
                    $value['column'] = collect($value['column'])->first(function ($column) use ($columns) {
                        return $columns->contains($column);
                    });

                    return $value;
                })->all(),
            ];
        } elseif ($step == 3) {
            $values = [
                'role_mapping' => collect($this->csv()->data)->pluck('role')->filter()->unique()->sort()->values()->map(function ($role) {
                    return ['wp_role' => $role];
                })->all(),
            ];
        }

        return array_merge($values, $this->config());
    }

    private function configGet($key)
    {
        return $this->config()[$key] ?? null;
    }

    private function configPut($key, $value)
    {
        $this->configPatch([$key => $value]);
    }

    private function configPatch($patch)
    {
        $this->config(
            array_merge($this->config(), $patch)
        );
    }

    private function config($config = null)
    {
        if (is_null($config)) {
            return Cache::get($this->getCacheKey(self::KEY_CONFIG), []);
        }

        Cache::put($this->getCacheKey(self::KEY_CONFIG), $config, now()->addDays(3));
    }

    private function csv()
    {
        if ($csv = Cache::get($this->getCacheKey(self::KEY_CSV))) {
            return $csv;
        }

        $asset = $this->findCsvAsset($this->configGet('file'));

        $csv = new Csv();
        $csv->load_data($asset->disk()->get($asset->path()));

        if (! $csv->auto()) {
            throw new CsvFileException('Unable to parse the users CSV file.');
        }

        Cache::put($this->getCacheKey(self::KEY_CSV), $csv, now()->addMinutes(15));

        return $csv;
    }

    private function csvHash($hash = null)
    {
        if (is_null($hash)) {
            return Cache::get($this->getCacheKey(self::KEY_CSV_HASH));
        }

        return Cache::put($this->getCacheKey(self::KEY_CSV_HASH), now()->addDays(3));
    }

    private function assetHash($asset)
    {
        if (! ($asset instanceof AssetContract)) {
            $asset = $this->findCsvAsset($asset);
        }

        // return md5($asset->disk()->get($asset->path()));
        return $asset->filename().'-'.$asset->lastModified();
    }

    private function findCsvAsset($asset)
    {
        if (is_array($asset)) {
            $asset = empty($asset) ? null : $asset[0];
        }
        if (! $asset) {
            throw new CsvFileException('Users CSV file not set.');
        }
        $asset = Asset::find($asset);
        if (! $asset) {
            throw new CsvFileException('Users CSV file asset not found.');
        }

        return $asset;
    }

    private function clearCache()
    {
        Cache::forget($this->getCacheKey(self::KEY_CSV));
        Cache::forget($this->getCacheKey(self::KEY_CSV_HASH));
        Cache::forget($this->getCacheKey(self::KEY_CONFIG));
    }

    private function getCacheKey($key)
    {
        return "wordpress-users.$key";
    }
}
