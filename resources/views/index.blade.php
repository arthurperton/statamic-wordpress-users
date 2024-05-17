@extends('statamic::layout')
@section('title', __('WordPress Users'))

@section('content')

    @if ($userCount)

        <div class="max-w-lg mt-2 mx-auto">
            <div class="rounded p-3 lg:px-7 lg:py-5 shadow bg-white">
                <header class="text-center mb-3">
                    <h1 class="mb-3">WordPress Users</h1>
                    <p class="text-grey">You have successfully imported <strong>{{ $userCount }} WordPress
                            {{ str()->plural('users', $userCount) }}</strong> into Statamic. These users can now log in with
                        their WordPress email address and password.</p>
                </header>
                <div class="wordpress-users-reminder text-center text-grey mb-3">
                    <p>If this addon is useful to you, please <a
                            href="https://statamic.com/addons/arthurperton/wordpress-users" target="_blank"
                            rel="noopener noopener" class="font-bold text-blue hover:text-blue-dark">buy it</a> if you haven't
                        already. And if you did, thanks for your purchase!</p>
                </div>
                <div class="text-center text-grey">
                    <p class="">
                        <!-- If this addon is useful to you, please buy it if you haven't done so already. And if you did, thanks for your purchase! -->
                        @if ($doneCount < $userCount)
                            So far, {{ $doneCount ?: 'none' }} of the imported users
                            {{ $doneCount === 1 ? 'has' : 'have' }} logged in. When all of them did, you can safely
                            uninstall this addon if you want to.
                        @else
                            All users have logged in now. You can safely uninstall this addon if you want to.
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex justify-center mt-4">
                <a href="{{ cp_route('wordpress-users.edit', 1) }}" class="btn-primary mx-auto">
                    Import More Users
                </a>
            </div>
        </div>
    @else
        @include('statamic::partials.empty-state', [
            'title' => __('WordPress Users'),
            'description' =>
                'Import your WordPress users into Statamic and allow them to use their original passwords to log in.',
            'svg' => 'empty/users',
            'button_url' => cp_route('wordpress-users.edit', 1),
            'button_text' => __('Let\'s Get Started'),
        ])

    @endif

@endsection
