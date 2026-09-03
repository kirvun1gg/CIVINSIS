<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
                ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
                ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // Custom Password Reset (PHPMailer)
    Route::get('custom-forgot-password', [\App\Http\Controllers\Auth\CustomPasswordResetController::class, 'showLinkRequestForm'])
                ->name('custom.password.request');

    Route::post('custom-forgot-password', [\App\Http\Controllers\Auth\CustomPasswordResetController::class, 'sendResetLinkEmail'])
                ->name('custom.password.email');

    Route::get('custom-reset-password/{token}', [\App\Http\Controllers\Auth\CustomPasswordResetController::class, 'showResetForm'])
                ->name('custom.password.reset');

    Route::post('custom-reset-password', [\App\Http\Controllers\Auth\CustomPasswordResetController::class, 'reset'])
                ->name('custom.password.update');

    // Google Auth
    Route::get('auth/google', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'redirect'])->name('google.login');
    Route::get('auth/google/callback', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'callback']);
    Route::get('auth/google/verify', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'showVerifyView'])->name('google.verify.view');
    Route::post('auth/google/verify', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'verify'])->name('google.verify');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', [EmailVerificationPromptController::class, '__invoke'])
                ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
                ->middleware(['signed', 'throttle:6,1'])
                ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
                ->middleware('throttle:6,1')
                ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
                ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
                ->name('logout');
});
