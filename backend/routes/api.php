<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;

// Sanctum CSRF Cookie route
Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['message' => 'CSRF cookie set']);
});

// Public auth routes
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:login');

// Protected auth routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', function (Request $request) {
        return response()->json(['user' => $request->user()]);
    });
    
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::get('/dashboard', function (Request $request) {
        return response()->json(['message' => 'Welcome to your dashboard', 'user' => $request->user()]);
    });
});

// Email verification
Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    try {
        $user = \App\Models\User::findOrFail($id);
        
        \Log::info('Verification attempt', [
            'user_id' => $user->id,
            'email' => $user->email,
            'hash_provided' => $hash,
            'email_hash' => sha1($user->getEmailForVerification()),
            'hashes_match' => hash_equals((string) $hash, sha1($user->getEmailForVerification()))
        ]);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            \Log::warning('Invalid verification link');
            return response()->json(['message' => 'Invalid verification link'], 400);
        }

        if ($user->hasVerifiedEmail()) {
            \Log::info('Email already verified');
            return response()->json(['message' => 'Email already verified'], 200);
        }

        if ($user->markEmailAsVerified()) {
            event(new \Illuminate\Auth\Events\Verified($user));
            \Log::info('Email verified successfully', ['user_id' => $user->id]);
            return response()->json(['message' => 'Email verified successfully. You can now log in.'], 200);
        }

        \Log::error('Email verification failed', ['user_id' => $user->id]);
        return response()->json(['message' => 'Email verification failed'], 400);
    } catch (\Exception $e) {
        \Log::error('Verification error', ['error' => $e->getMessage()]);
        return response()->json(['message' => 'Verification failed'], 500);
    }
})->name('verification.verify');

Route::post('/email/verification-notification', [AuthController::class, 'resendVerificationEmail'])
    ->middleware(['auth:sanctum', 'throttle:6,1'])
    ->name('verification.send');

// Password reset
Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetLink'])
    ->middleware('throttle:6,1')
    ->name('password.email');

Route::post('/reset-password', [AuthController::class, 'resetPassword']) // ← Make sure this exists
    ->name('password.update');