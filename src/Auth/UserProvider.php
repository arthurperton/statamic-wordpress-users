<?php

namespace Statamic\Addons\WordpressUsers\Auth;

use Hautelook\Phpass\PasswordHash;
use Illuminate\Contracts\Auth\Authenticatable;
use Statamic\Auth\User;

class UserProvider extends \Statamic\Auth\UserProvider 
{
    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  array                                      $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        if (parent::validateCredentials($user, $credentials)) {
            return true;
        }

        if (! ($user instanceof User)) {
            return false;
        }

        $password = $credentials['password'];
      
        if (! $this->checkWordpressPassword($user, $password)) {
            return false;
        }

        $user->password($password);
        $user->save();

        return true;
    }

    /**
     * Check the given password for an imported WordPress user.
     *
     * @param  Statamic\Auth\User $user
     * @param  string             $password
     * @return bool
     */
    protected function checkWordpressPassword(User $user, string $password)
    {
        if (! ($hash = $user->get('wp__password_hash'))) {
            return false;
        }

        $hasher = new PasswordHash(8, false);
        
        return $hasher->CheckPassword($password, $hash);
    }

}