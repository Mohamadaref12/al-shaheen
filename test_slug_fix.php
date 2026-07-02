<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$news = App\Models\News::query()->with('translations')->first();
if (! $news) {
    echo "no news\n";
    exit(1);
}

$news->setAttribute('content_ar', 'Updated content only ' . time());
$controller = new class {
    use App\Traits\NormalizesTranslatableApiInput;

    public function run(App\Models\News $news): void
    {
        $this->persistModelTranslations($news);
    }
};

(new $controller)->run($news);
$news->refresh()->load('translations');
$t = $news->translations->firstWhere('locale', 'ar');
echo 'slug: ' . ($t?->slug ?? 'null') . "\n";
echo 'content saved: ' . (str_contains((string) $t?->content, 'Updated content only') ? 'yes' : 'no') . "\n";
