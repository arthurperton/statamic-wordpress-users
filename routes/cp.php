<?php

use Illuminate\Support\Facades\Route;

// Route::namespace('\Statamic\Addons\WordpressUsers\Http\Controllers')
//     ->prefix('wordpress-users/')
Route::prefix('wordpress-users/')
    ->name('wordpress-users.')
    ->group(function () {
        Route::get('', 'Controller@index')->name('index');
        Route::get('edit/{step}', 'Controller@edit')->name('edit');
        Route::patch('update/{step}', 'Controller@update')->name('update');
        Route::get('import', 'Controller@import')->name('import');
        Route::get('valid', 'Controller@valid')->name('valid');
    });
