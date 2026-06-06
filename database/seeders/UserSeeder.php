<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin',
                'last_name' => 'System',
                'email' => 'admin@admin.com',
                'status' => 'active',
                'role_name' => User::ROLE_ADMIN,
                'username_verified_at' => now(),
                'password' => 'password',
                'remember_token' => Str::random(10),
            ]
        );

        User::updateOrCreate(
            ['username' => 'staff'],
            [
                'name' => 'Staff',
                'last_name' => 'System',
                'email' => 'staff@admin.com',
                'status' => 'active',
                'role_name' => User::ROLE_STAFF,
                'username_verified_at' => now(),
                'password' => 'password',
                'remember_token' => Str::random(10),
            ]
        );

        User::updateOrCreate(
            ['username' => 'driver'],
            [
                'name' => 'Driver',
                'last_name' => 'System',
                'email' => 'driver@admin.com',
                'status' => 'active',
                'role_name' => User::ROLE_DRIVER,
                'username_verified_at' => now(),
                'password' => 'password',
                'remember_token' => Str::random(10),
            ]
        );
    }
}
