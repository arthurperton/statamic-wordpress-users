@extends('statamic::layout')
@section('title', __('WordPress Users'))

@section('content')
    
    <wordpress-users-import-form
        :blueprint="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :meta="{{ json_encode($meta) }}"
        title="{{ 'Import WordPress Users – Step '.$step.' of '.$stepcount }}"
        url="{{ cp_route('wordpress-users.update', $step) }}"
        cancel-url="{{ cp_route('wordpress-users.index') }}"
        previous-url="{{ $step > 1 ? cp_route('wordpress-users.edit', $step - 1) : null }}" 
        previous-text="Previous Step"
        next-url="{{ $step < 3 ? cp_route('wordpress-users.edit', $step + 1) : cp_route('wordpress-users.import') }}"
        next-text="{{ $step < 3 ? 'Next Step' : 'Import Users' }}"
    ></wordpress-users-import-form>

@endsection