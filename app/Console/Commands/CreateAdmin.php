<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create The Admin For The Application';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->ask('Please enter the name');
        $username = $this->ask('Enter your username');
        $password = $this->secret('Enter your password');
        $password_confirmation = $this->secret('Please re-enter your password');

        if ($password !== $password_confirmation) {
            $this->error('Passwords do not match');
            return;
        }

        $user = User::create([
            'name' => $name,
            'username' => $username,
            'password' => Hash::make($password),
            'role_name' => 'admin',
            'created_at' => now(),
        ]);

        $this->info('User created successfully: ' . $user);
    }
}
