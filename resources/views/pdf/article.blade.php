<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <title>{{ $translation->title }}</title>
    <style>
        body {
            font-family: dejavusans, sans-serif;
            color: #28414e;
            font-size: 11pt;
            line-height: 1.7;
        }

        .brand {
            font-size: 9pt;
            color: #5a6a72;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 18px;
        }

        .category {
            display: inline-block;
            background: #eef3f5;
            color: #28414e;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 9pt;
            margin-bottom: 12px;
        }

        h1 {
            font-size: 24pt;
            line-height: 1.25;
            margin: 0 0 10px;
            color: #1e3340;
        }

        .subtitle {
            font-size: 13pt;
            color: #3d5a6a;
            margin: 0 0 16px;
        }

        .meta {
            font-size: 9pt;
            color: #5a6a72;
            margin-bottom: 22px;
            border-bottom: 1px solid #e8e0d8;
            padding-bottom: 14px;
        }

        .meta span {
            margin-{{ $locale === 'ar' ? 'left' : 'right' }}: 14px;
        }

        .featured-image {
            width: 100%;
            max-height: 280px;
            object-fit: cover;
            border-radius: 10px;
            margin: 0 0 22px;
        }

        .excerpt {
            font-size: 12pt;
            color: #3d5a6a;
            margin-bottom: 22px;
            padding: 14px 16px;
            background: #faf5f0;
            border-{{ $locale === 'ar' ? 'right' : 'left' }}: 4px solid #28414e;
        }

        .content {
            font-size: 11pt;
        }

        .content p {
            margin: 0 0 12px;
        }

        .content h2,
        .content h3,
        .content h4 {
            color: #1e3340;
            margin: 18px 0 10px;
        }

        .content img {
            max-width: 100%;
            height: auto;
        }

        .footer {
            margin-top: 28px;
            padding-top: 12px;
            border-top: 1px solid #e8e0d8;
            font-size: 8pt;
            color: #7a8790;
        }
    </style>
</head>
<body>
    <div class="brand">Al Shaheen Media</div>

    @if ($article->primaryCategory)
        <div class="category">{{ $article->primaryCategory->name }}</div>
    @endif

    <h1>{{ $translation->title }}</h1>

    @if ($translation->subtitle)
        <p class="subtitle">{{ $translation->subtitle }}</p>
    @endif

    <div class="meta">
        @if ($article->author)
            <span>{{ $locale === 'ar' ? 'الكاتب:' : 'Author:' }} {{ $article->author->name }}</span>
        @endif

        @if ($article->published_at)
            <span>{{ $locale === 'ar' ? 'تاريخ النشر:' : 'Published:' }} {{ $article->published_at->format('Y-m-d') }}</span>
        @endif

        @if ($article->read_time)
            <span>{{ $locale === 'ar' ? 'وقت القراءة:' : 'Read time:' }} {{ $article->read_time }} {{ $locale === 'ar' ? 'دقيقة' : 'min' }}</span>
        @endif
    </div>

    @if ($featuredImagePath)
        <img src="{{ $featuredImagePath }}" alt="" class="featured-image">
    @endif

    @if ($translation->excerpt)
        <div class="excerpt">{{ $translation->excerpt }}</div>
    @endif

    <div class="content">
        {!! \App\Support\ArticleContent::toHtml($translation->content) !!}
    </div>

    <div class="footer">
        {{ $locale === 'ar' ? 'تم التصدير من منصة الشاهين الإعلامية' : 'Exported from Al Shaheen Media' }}
        — {{ now()->format('Y-m-d H:i') }}
    </div>
</body>
</html>
