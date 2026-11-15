<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Sandi admin diambil dari file .env (ADMIN_EMAIL dan ADMIN_PASSWORD),
        // jadi tidak tertulis di kode dan tidak ikut terunggah ke GitHub.
        $email    = env('ADMIN_EMAIL', 'admin@karymatch.test');
        $password = env('ADMIN_PASSWORD');

        if (! $password) {
            $this->command->error('Isi ADMIN_PASSWORD di file .env dulu, lalu jalankan seeder lagi.');
            return;
        }

        $admin = User::firstOrNew(['email' => $email]);
        $admin->forceFill([
            'name'              => 'Admin KaryMatch',
            'role'              => 'admin',
            'password'          => Hash::make($password),
            'email_verified_at' => now(),
        ])->save();
    }
}
