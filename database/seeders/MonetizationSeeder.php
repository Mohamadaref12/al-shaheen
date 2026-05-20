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
                'name'          => 'مجاني',
                'slug'          => 'free',
                'description'   => 'الوصول الأساسي للمحتوى المفتوح',
                'price'         => 0.00,
                'duration_days' => 0,
                'features'      => ['قراءة المقالات المفتوحة', 'التعليق', 'متابعة الكتّاب'],
                'is_active'     => true,
            ],
            [
                'name'          => 'بريميوم شهري',
                'slug'          => 'premium-monthly',
                'description'   => 'وصول كامل لجميع التقارير والمحتوى الحصري',
                'price'         => 4.99,
                'duration_days' => 30,
                'features'      => ['كل مزايا المجاني', 'التقارير الحصرية', 'تجربة بلا إعلانات', 'دورات التدريب'],
                'is_active'     => true,
            ],
            [
                'name'          => 'بريميوم سنوي',
                'slug'          => 'premium-annual',
                'description'   => 'اشتراك سنوي بسعر مخفض',
                'price'         => 39.99,
                'duration_days' => 365,
                'features'      => ['كل مزايا البريميوم الشهري', 'توفير 33%', 'أولوية الدعم'],
                'is_active'     => true,
            ],
        ];

        foreach ($packages as $pkg) {
            SubscriptionPackage::create($pkg);
        }

        // --- Subscriptions ---
        $premiumPackage = SubscriptionPackage::where('slug', 'premium-monthly')->first();
        $readers = User::where('role', 'reader')->inRandomOrder()->take(8)->get();

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
