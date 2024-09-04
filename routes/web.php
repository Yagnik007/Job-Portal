<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AccountController;

// Account routes with prefix 'account'
Route::group(['prefix' => 'account'], function () {

    // Guest Routes - only accessible when not authenticated
    Route::middleware(['guest'])->group(function () {
        Route::get('register', [AccountController::class, 'registration'])->name('account.registration'); // Correct route name
        Route::post('process-register', [AccountController::class, 'processRegistration'])->name('account.processRegistration');
        Route::get('login', [AccountController::class, 'login'])->name('account.login');
        Route::post('authenticate', [AccountController::class, 'authenticate'])->name('account.authenticate');
    });

    // Authenticated Routes - only accessible when authenticated
    Route::middleware(['auth'])->group(function () {
        Route::get('profile', [AccountController::class, 'profile'])->name('account.profile');
        Route::put('update-profile', [AccountController::class, 'updateProfile'])->name('account.updateProfile');
        Route::post('logout', [AccountController::class, 'logout'])->name('account.logout');
    });
});

// Home route outside of the 'account' prefix group
Route::get('/', [HomeController::class, 'index'])->name('home');
