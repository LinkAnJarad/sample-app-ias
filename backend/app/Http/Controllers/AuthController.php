<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Events\PasswordReset;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        \Log::info('Login attempt', [
            'email' => $request->email,
            'session_id' => $request->session()->getId()
        ]);

        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();

            return response()->json([
                'user' => Auth::user(),
                'message' => 'Login successful'
            ]);
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Automatically login the user after registration
        Auth::login($user);

        // Trigger email verification
        event(new Registered($user));

        return response()->json([
            'message' => 'Registration successful! Please check your email to verify your account.',
            'user' => $user
        ], 201);
    }

    // public function logout(Request $request)
    // {
    //     \Log::error('jojo');
    //     try {
    //         // Revoke all tokens for the user (for Sanctum API tokens)
    //         if (auth()->check() && auth()->user()) {
    //             // If using API tokens
    //             if (method_exists(auth()->user(), 'tokens')) {
    //                 auth()->user()->tokens()->delete();
    //             }
                
    //             // If using session authentication
    //             auth()->guard('web')->logout();
    //         }
            
    //         $request->session()->invalidate();
    //         $request->session()->regenerateToken();


    //         return response()->noContent(); // 204 No Content
    //     } catch (\Exception $e) {
    //         \Log::error('Logout error: ' . $e->getMessage());
    //         return response()->json(['error' => 'Logout failed'], 500);
    //     }
    // }

    public function logout(Request $request)
    {
        try {
            $user = auth()->user();
            $userId = $user ? $user->id : 'Unknown';
            $userEmail = $user ? $user->email : 'Unknown';
            
            // Log the logout attempt
            \Log::info('User logout attempt', [
                'user_id' => $userId,
                'user_email' => $userEmail,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toISOString()
            ]);
            
            // Perform logout
            if (auth()->guard('web')->check()) {
                auth()->guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            // Log successful logout
            \Log::info('User logged out successfully', [
                'user_id' => $userId,
                'user_email' => $userEmail,
                'ip_address' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);

            return response()->noContent(); // 204 No Content
        } catch (\Exception $e) {
            \Log::error('Logout error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id() ?? 'Unknown',
                'ip_address' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);
            return response()->json(['error' => 'Logout failed'], 500);
        }
    }

    // Remove this method since it's now in routes
    // public function me(Request $request)
    // {
    //     return response()->json([
    //         'user' => $request->user()
    //     ]);
    // }

    public function checkAuth(Request $request)
    {
        return response()->json([
            'authenticated' => Auth::check(),
            'user' => Auth::user()
        ]);
    }

    public function verifyEmail(Request $request)
    {
        $user = $request->user();
        
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified'], 200);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json(['message' => 'Email verified successfully'], 200);
    }

    public function resendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified'], 200);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification link sent'], 200);
    }

    public function sendPasswordResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Use Laravel's built-in password reset functionality
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status == Password::RESET_LINK_SENT
                    ? response()->json(['message' => __($status)], 200)
                    : response()->json(['message' => __($status)], 400);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        // Here we will attempt to reset the user's password
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status == Password::PASSWORD_RESET
                    ? response()->json(['message' => __($status)], 200)
                    : response()->json(['message' => __($status)], 400);
    }
}