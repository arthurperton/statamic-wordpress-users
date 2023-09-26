<?php

use Illuminate\Support\Facades\Route;
use Statamic\Addons\WordpressUsers\Http\Controllers\Controller;

Route::prefix('wordpress-users/')
    ->name('wordpress-users.')
    ->group(function () {
        Route::get('', [Controller::class, 'index'])->name('index');
        Route::get('edit/{step}', [Controller::class, 'edit'])->name('edit');
        Route::patch('update/{step}', [Controller::class, 'update'])->name('update');
        Route::get('import', [Controller::class, 'import'])->name('import');
        Route::get('valid', [Controller::class, 'valid'])->name('valid');
    });
