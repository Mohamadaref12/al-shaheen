<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\News;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class FeaturedImageSeeder extends Seeder
{
    public const ASSET_FILENAME = 'al-shaheen-360-banner.png';

    public const STORAGE_PATH = 'defaults/al-shaheen-360-banner.png';

    public function run(): void
    {
        $source = database_path('seeders/assets/'.self::ASSET_FILENAME);

        if (! File::exists($source)) {
            $this->command?->error('Featured image asset not found: '.$source);

            return;
        }

        $disk = Storage::disk('images');

        if (! $disk->directoryExists('defaults')) {
            $disk->makeDirectory('defaults');
        }

        $disk->put(self::STORAGE_PATH, File::get($source));

        $articlesUpdated = Article::query()->update(['featured_image' => self::STORAGE_PATH]);
        $newsUpdated = News::query()->update(['featured_image' => self::STORAGE_PATH]);

        $this->command?->info(sprintf(
            'Featured image applied to %d articles and %d news items (%s).',
            $articlesUpdated,
            $newsUpdated,
            self::STORAGE_PATH,
        ));
    }
}
