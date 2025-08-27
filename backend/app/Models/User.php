<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    public function redirectPath()
    {
        return '/login'; // or wherever you want to redirect after verification
    }

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function redirectTo()
    {
        return '/login';
    }

    public function getEmailForVerification()
    {
        return $this->email;
    }

    public function sendPasswordResetNotification($token)
    {
        $resetUrl = env('FRONTEND_URL', 'http://localhost:5173') . '/reset-password?token=' . $token . '&email=' . urlencode($this->getEmailForPasswordReset());

        // Send a simple HTML email
        Mail::html("
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #333;'>Reset Password</h2>
                <p style='color: #666;'>You are receiving this email because we received a password reset request for your account.</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$resetUrl}' 
                       style='background-color: #3b82f6; color: white; padding: 12px 24px; 
                              text-decoration: none; border-radius: 6px; display: inline-block;'>
                        Reset Password
                    </a>
                </div>
                <p style='color: #666; font-size: 14px;'>
                    If you did not request a password reset, no further action is required.
                </p>
                <p style='color: #999; font-size: 12px; margin-top: 30px;'>
                    This password reset link will expire in 60 minutes.
                </p>
            </div>
        ", function ($message) {
            $message->to($this->email)
                    ->subject('Reset Password Notification');
        });
    }
}