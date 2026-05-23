<?php

namespace Database\Seeders;

use App\Models\Ad;
use App\Models\NewsletterSubscriber;
use App\Models\SubscriptionPackage;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MonetizationSeeder extends Seeder
{
    public function run(): void
    {
        // --- Newsletter Subscribers ---
        $emails = array_unique(array_map(fn() => fake()->safeEmail(), range(1, 30)));
        foreach ($emails as $email) {
            NewsletterSubscriber::create([
                'email'  => $email,
                'name'   => fake()->name(),
                'status' => fake()->randomElement(['active', 'active', 'unsubscribed']),
            ]);
        }

        // --- Ads ---
        $placements = ['leaderboard', 'hero', 'in_feed', 'mid_article', 'right_rail', 'footer'];
        $adCategories = ['News & Media', 'Government', 'Education', 'Tech & Startups', 'Finance', 'Real Estate', 'Healthcare', 'Events & Conferences'];

        foreach ($placements as $placement) {
            Ad::create([
                'title'       => fake()->company() . ' Ad',
                'placement'   => $placement,
                'image_url'   => null,
                'link_url'    => fake()->url(),
                'ad_category' => fake()->randomElement($adCategories),
                'starts_at'   => now()->subDays(5),
                'ends_at'     => now()->addDays(30),
                'is_active'   => true,
            ]);
        }

        // --- Subscription Packages ---
        $packages = [
            [
                'name'          => 'Free',
                'slug'          => 'free',
                'description'   => 'Basic access to open content',
                'price'         => 0.00,
                'duration_days' => 0,
                'features'      => ['Read open articles', 'Comment', 'Follow writers'],
                'is_active'     => true,
            ],
            [
                'name'          => 'Monthly Premium',
                'slug'          => 'premium-monthly',
                'description'   => 'Full access to all reports and exclusive content',
                'price'         => 4.99,
                'duration_days' => 30,
                'features'      => ['All Free features', 'Exclusive reports', 'Ad-free experience', 'Training courses'],
                'is_active'     => true,
            ],
            [
                'name'          => 'Annual Premium',
                'slug'          => 'premium-annual',
                'description'   => 'Annual subscription at a discounted rate',
                'price'         => 39.99,
                'duration_days' => 365,
                'features'      => ['All Monthly Premium features', 'Save 33%', 'Priority support'],
                'is_active'     => true,
            ],
        ];

        foreach ($packages as $pkg) {
            SubscriptionPackage::create($pkg);
        }

        // --- Subscriptions ---
        $premiumPackage = SubscriptionPackage::where('slug', 'premium-monthly')->first();
        $readers = User::whereHas('reader')->inRandomOrder()->take(8)->get();

        foreach ($readers as $user) {
            $start = now()->subDays(rand(0, 20));
            Subscription::create([
                'user_id'    => $user->id,
                'package_id' => $premiumPackage->id,
                'plan'       => 'premium-monthly',
                'starts_at'  => $start,
                'ends_at'    => $start->copy()->addDays(30),
                'status'     => 'active',
            ]);
        }
    }
}
