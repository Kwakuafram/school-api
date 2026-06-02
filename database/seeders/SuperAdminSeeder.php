<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => 'admin@sms.local'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'System Super Admin',
                'password' => 'password123',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        if (method_exists($user, 'assignRole')) {
            $user->assignRole('super_admin');
        }
    }
}