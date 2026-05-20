<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name'               => 'Admin',
            'email'              => 'admin@al-shaheen.test',
            'password'           => Hash::make('password'),
            'role'               => 'admin',
            'locale'             => 'ar',
            'language'           => 'ar',
            'country'            => 'Kuwait',
            'is_verified'        => true,
            'is_active'          => true,
            'email_verified_at'  => now(),
        ]);

        // Editor
        User::create([
            'name'               => 'Editor',
            'email'              => 'editor@al-shaheen.test',
            'password'           => Hash::make('password'),
            'role'               => 'editor',
            'locale'             => 'ar',
            'language'           => 'ar',
            'country'            => 'Kuwait',
            'is_verified'        => true,
            'is_active'          => true,
            'email_verified_at'  => now(),
        ]);

        // Writers (verified)
        User::factory(5)->create([
            'role'        => 'writer',
            'is_verified' => true,
            'is_active'   => true,
        ]);

        // Contributors
        User::factory(8)->create([
            'role'        => 'contributor',
            'is_verified' => false,
            'is_active'   => true,
        ]);

        // Readers
        User::factory(20)->create([
            'role'        => 'reader',
            'is_verified' => false,
            'is_active'   => true,
        ]);
    }
}
