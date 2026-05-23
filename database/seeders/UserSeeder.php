<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Contributor;
use App\Models\Editor;
use App\Models\Reader;
use App\Models\User;
use App\Models\Writer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        $admin = User::create([
            'name'              => 'Admin',
            'email'             => 'admin@sawatech.com',
            'password'          => Hash::make('password'),
            'locale'            => 'ar',
            'language'          => 'ar',
            'country'           => 'Kuwait',
            'is_verified'       => true,
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);
        Admin::create(['user_id' => $admin->id]);

        // Editor
        $editor = User::create([
            'name'              => 'Editor',
            'email'             => 'editor@sawatech.com',
            'password'          => Hash::make('password'),
            'locale'            => 'ar',
            'language'          => 'ar',
            'country'           => 'Kuwait',
            'is_verified'       => true,
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);
        Editor::create(['user_id' => $editor->id]);

        // Writers (verified)
        $writers = User::factory(5)->create([
            'is_verified' => true,
            'is_active'   => true,
        ]);
        foreach ($writers as $writer) {
            Writer::create([
                'user_id'            => $writer->id,
                'display_name'       => $writer->name,
                'application_status' => 'approved',
            ]);
        }

        // Contributors
        $contributors = User::factory(8)->create([
            'is_verified' => false,
            'is_active'   => true,
        ]);
        foreach ($contributors as $contributor) {
            Contributor::create(['user_id' => $contributor->id]);
        }

        // Readers
        $readers = User::factory(20)->create([
            'is_verified' => false,
            'is_active'   => true,
        ]);
        foreach ($readers as $reader) {
            Reader::create(['user_id' => $reader->id]);
        }
    }
}
