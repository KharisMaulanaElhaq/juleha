<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateSuperAdminCommand extends Command
{
    protected $signature = 'juleha:create-admin 
                            {--name= : Nama lengkap super admin}
                            {--email= : Email akun admin}
                            {--password= : Password akun admin}';

    protected $description = 'Membuat akun Super Admin DPD JULEHA Brebes';

    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Nama Lengkap Admin', 'Super Admin JULEHA');
        $email = $this->option('email') ?: $this->ask('Email Admin', 'admin@julehabrebes.org');

        $user = User::where('email', $email)->first();

        $password = $this->option('password');
        if (! $password) {
            $password = $this->secret('Password Admin (minimal 8 karakter)');
        }

        if (! $password || strlen($password) < 8) {
            $this->error('Password minimal 8 karakter!');

            return self::FAILURE;
        }

        if ($user) {
            $user->update([
                'name' => $name,
                'password' => Hash::make($password),
                'role' => User::ROLE_SUPER_ADMIN,
            ]);
            $this->info("Akun admin [{$email}] berhasil diperbarui menjadi Super Admin.");
        } else {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => User::ROLE_SUPER_ADMIN,
            ]);
            $this->info("Akun Super Admin [{$email}] berhasil dibuat!");
        }

        return self::SUCCESS;
    }
}
