<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use App\Models\WriterProfile;
use Illuminate\Database\Seeder;

class WriterProfileSeeder extends Seeder
{
    public function run(): void
    {
        $writers = User::whereIn('role', ['writer', 'contributor'])->get();
        $categories = Category::whereNull('parent_id')->pluck('id')->toArray();

        $levels = ['beginner', 'intermediate', 'senior', 'expert'];
        $langs  = [['ar'], ['en'], ['ar', 'en']];

        foreach ($writers as $user) {
            $profile = WriterProfile::create([
                'user_id'              => $user->id,
                'display_name'         => $user->name,
                'bio'                  => fake('ar_SA')->paragraph(3),
                'profile_photo'        => null,
                'portfolio_link'       => fake()->url(),
                'experience_level'     => fake()->randomElement($levels),
                'languages'            => fake()->randomElement($langs),
                'editorial_specialties' => [fake()->randomElement(['politics', 'economy', 'tech', 'culture', 'sports'])],
                'location'             => fake()->city(),
                'social_links'         => [
                    'twitter'  => 'https://x.com/' . fake()->userName(),
                    'linkedin' => 'https://linkedin.com/in/' . fake()->userName(),
                ],
                'application_status'   => $user->role === 'writer' ? 'approved' : fake()->randomElement(['submitted', 'under_review', 'approved']),
            ]);

            // assign 1-3 categories of interest
            $profile->categories()->attach(
                fake()->randomElements($categories, rand(1, 3))
            );
        }
    }
}
