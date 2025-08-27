<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


// // Main page
// Route::get('/', function () {
//     return view('welcome');
// });

// // Auth routes (protected by web middleware -> CSRF on POST)


// Route::middleware('web')->group(function () {
//     Route::get('/csrf-token', function () {
//         return response()->json(['token' => csrf_token()]);
//     });

//     Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
//     Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:login');
//     Route::post('/logout', [AuthController::class, 'logout']);
    
// });

// Route::middleware('auth')->get('/me', function (Request $request) {
//     return response()->json(['user' => $request->user()]);
// });

// Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
//     try {
//         $user = \App\Models\User::findOrFail($id);
        
//         \Log::info('Verification attempt', [
//             'user_id' => $user->id,
//             'email' => $user->email,
//             'hash_provided' => $hash,
//             'email_hash' => sha1($user->getEmailForVerification()),
//             'hashes_match' => hash_equals((string) $hash, sha1($user->getEmailForVerification()))
//         ]);

//         if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
//             \Log::warning('Invalid verification link');
//             return response()->json(['message' => 'Invalid verification link'], 400);
//         }

//         if ($user->hasVerifiedEmail()) {
//             \Log::info('Email already verified');
//             return response()->json(['message' => 'Email already verified'], 200);
//         }

//         if ($user->markEmailAsVerified()) {
//             event(new \Illuminate\Auth\Events\Verified($user));
//             \Log::info('Email verified successfully', ['user_id' => $user->id]);
//             return response()->json(['message' => 'Email verified successfully. You can now log in.'], 200);
//         }

//         \Log::error('Email verification failed', ['user_id' => $user->id]);
//         return response()->json(['message' => 'Email verification failed'], 400);
//     } catch (\Exception $e) {
//         \Log::error('Verification error', ['error' => $e->getMessage()]);
//         return response()->json(['message' => 'Verification failed'], 500);
//     }
// })->name('verification.verify');

// Route::post('/email/verification-notification', [AuthController::class, 'resendVerificationEmail'])
//     ->middleware(['auth', 'throttle:6,1'])
//     ->name('verification.send');

// // Password Reset Routes
// Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetLink'])
//     ->middleware('throttle:6,1')
//     ->name('password.email');

// Route::post('/reset-password', [AuthController::class, 'resetPassword'])
//     ->name('password.update');

// Route::get('/login', function () {
//     return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/login');
// })->name('login');

// Route::get('/debug-session', function (Illuminate\Http\Request $request) {
//     return response()->json([
//         'session' => $request->session()->all(),
//         '_token'  => csrf_token(),
//     ]);
// });

// use Illuminate\Support\Facades\Mail;

// Route::get('/test-mail', function () {
//     Mail::raw('Test email body', function ($message) {
//         $message->to('linkanjarad@yahoo.com')
//                 ->subject('Test Email');
//     });
    
//     return 'Test email sent!';
// });