<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Writer;
use Illuminate\Database\Seeder;

class WriterProfileSeeder extends Seeder
{
    public function run(): void
    {
        $writers    = Writer::with('user')->get();
        $categories = Category::whereNull('parent_id')->pluck('id')->toArray();

        $levels = ['beginner', 'intermediate', 'senior', 'expert'];
        $langs  = [['ar'], ['en'], ['ar', 'en']];

        foreach ($writers as $profile) {
            $profile->update([
                'display_name'          => $profile->user->name,
                'bio'                   => fake()->paragraph(3),
                'profile_photo'         => null,
                'portfolio_link'        => fake()->url(),
                'experience_level'      => fake()->randomElement($levels),
                'languages'             => fake()->randomElement($langs),
                'editorial_specialties' => [fake()->randomElement(['politics', 'economy', 'tech', 'culture', 'sports'])],
                'location'              => fake()->city(),
                'social_links'          => [
                    'twitter'  => 'https://x.com/' . fake()->userName(),
                    'linkedin' => 'https://linkedin.com/in/' . fake()->userName(),
                ],
                'application_status'    => 'approved',
            ]);

            $profile->categories()->attach(
                fake()->randomElements($categories, rand(1, 3))
            );
        }
    }
}
