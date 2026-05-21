<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key'   => 'site_name',
                'value' => ['ar' => 'الشاهين 360', 'en' => 'Al-Shaheen 360'],
            ],
            [
                'key'   => 'site_tagline',
                'value' => ['ar' => 'إعلام بلا حدود', 'en' => 'Media Without Limits'],
            ],
            [
                'key'   => 'site_email',
                'value' => 'info@al-shaheen.test',
            ],
            [
                'key'   => 'site_phone',
                'value' => '+965-XXXX-XXXX',
            ],
            [
                'key'   => 'social_links',
                'value' => [
                    'twitter'   => 'https://twitter.com/alshaheen360',
                    'instagram' => 'https://instagram.com/alshaheen360',
                    'youtube'   => 'https://youtube.com/@alshaheen360',
                    'telegram'  => 'https://t.me/alshaheen360',
                ],
            ],
            [
                'key'   => 'breaking_news_enabled',
                'value' => true,
            ],
            [
                'key'   => 'articles_per_page',
                'value' => 12,
            ],
            [
                'key'   => 'default_locale',
                'value' => 'ar',
            ],
            [
                'key'   => 'allow_comments',
                'value' => true,
            ],
            [
                'key'   => 'comment_moderation',
                'value' => true,
            ],
            [
                'key'   => 'newsletter_enabled',
                'value' => true,
            ],
            [
                'key'   => 'maintenance_mode',
                'value' => false,
            ],
            [
                'key'   => 'analytics',
                'value' => [
                    'google_tag'     => '',
                    'facebook_pixel' => '',
                ],
            ],
            [
                'key'   => 'seo_defaults',
                'value' => [
                    'title'       => 'الشاهين 360 - إعلام بلا حدود',
                    'description' => 'منصة الشاهين 360 الإعلامية الرقمية للأخبار والتقارير والمقابلات',
                    'keywords'    => 'أخبار, تقارير, صحافة, إعلام, الكويت',
                ],
            ],
        ];

        foreach ($settings as $setting) {
            SiteSetting::create($setting);
        }
    }
}
