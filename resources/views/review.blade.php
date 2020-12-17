@extends('statamic::layout')
@section('title', __('WordPress Users'))

@section('content')
    
    <div class="rounded p-3 lg:px-7 lg:py-5 shadow bg-white">
        <header class="text-center mb-3">
            <h1 class="mb-3">Warning</h1> 
            <p class="text-grey">Not all users can be imported. Please review the issues below.</p>
        </header>
 
            @foreach ($errors->take(50) as $error)
                @if ($loop->first)
                <div class="">
                    <table class="data-table no-row-hover">
                        <thead>
                        <tr><th>User</th><th>Problem</th></tr>
                        </thead>
                @endif
                        <tr><td>{{ $error['user'] }}</td><td>{{ $error['message'] }}</td></tr>
                @if ($loop->last)
                        @if ($errors->count() > 50) 
                        <tr><td>...</td><td>...</td></tr>
                        @endif
                    </table>
                </div>
                @endif
        
            @endforeach
        
    </div>

    <div class="flex justify-center mt-4 justify-between">
        <a href="{{ cp_route('wordpress-users.index') }}" class="btn">
            Cancel
        </a>
        <a href="{{ cp_route('wordpress-users.import') }}?force=true" class="btn-primary">
            Continue
        </a>
    </div>

@endsection