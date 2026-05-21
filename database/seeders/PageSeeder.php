<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'title'    => 'من نحن',
                'slug'     => 'about',
                'locale'   => 'ar',
                'content'  => '<h2>عن الشاهين 360</h2><p>منصة إعلامية رقمية متكاملة تجمع بين الصحافة الاحترافية وأدوات العصر الرقمي.</p><p>نسعى إلى تقديم محتوى موثوق وشامل يلبي احتياجات القارئ العربي في عصر المعلومات.</p>',
                'is_active' => true,
            ],
            [
                'title'    => 'About Us',
                'slug'     => 'about-en',
                'locale'   => 'en',
                'content'  => '<h2>About Al-Shaheen 360</h2><p>A comprehensive digital media platform combining professional journalism with modern digital tools.</p><p>We strive to deliver reliable, in-depth content that meets the needs of the Arab reader in the information age.</p>',
                'is_active' => true,
            ],
            [
                'title'    => 'سياسة الخصوصية',
                'slug'     => 'privacy-policy',
                'locale'   => 'ar',
                'content'  => '<h2>سياسة الخصوصية</h2><p>نحن في الشاهين 360 نلتزم بحماية خصوصيتك وأمان بياناتك الشخصية.</p><p>لا نشارك بياناتك مع أطراف ثالثة دون موافقتك الصريحة.</p>',
                'is_active' => true,
            ],
            [
                'title'    => 'شروط الاستخدام',
                'slug'     => 'terms',
                'locale'   => 'ar',
                'content'  => '<h2>شروط الاستخدام</h2><p>باستخدامك لمنصة الشاهين 360 فإنك توافق على الالتزام بهذه الشروط والأحكام.</p><p>يُرجى قراءة هذه الشروط بعناية قبل استخدام المنصة.</p>',
                'is_active' => true,
            ],
            [
                'title'    => 'تواصل معنا',
                'slug'     => 'contact',
                'locale'   => 'ar',
                'content'  => '<h2>تواصل معنا</h2><p>نسعد بتواصلك معنا عبر البريد الإلكتروني: info@al-shaheen.test</p><p>أو عبر نماذج التواصل المتاحة على الموقع.</p>',
                'is_active' => true,
            ],
            [
                'title'    => 'الإعلان معنا',
                'slug'     => 'advertise',
                'locale'   => 'ar',
                'content'  => '<h2>الإعلان في الشاهين 360</h2><p>تواصل مع فريق الإعلانات للحصول على أفضل الفرص الإعلانية على منصتنا.</p>',
                'is_active' => true,
            ],
        ];

        foreach ($pages as $page) {
            Page::create($page);
        }
    }
}
