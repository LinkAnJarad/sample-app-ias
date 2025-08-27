<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class DeleteUser extends Command
{
    protected $signature = 'user:delete {email}';
    protected $description = 'Delete a user by email';

    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email {$email} not found.");
            return 1;
        }

        $user->delete();
        $this->info("User {$email} deleted successfully.");
        return 0;
    }
}