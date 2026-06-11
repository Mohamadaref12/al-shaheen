@php
    use App\Support\ImageStorage;

    $featuredImageUrl = ImageStorage::url($article->featured_image);

    $publishedAt = $article->published_at ?? $article->created_at;

    $statusLabel = match ($article->status) {
        'published' => 'Published',
        'review'    => 'Under Review',
        'draft'     => 'Draft',
        'archived'  => 'Archived',
        default     => $article->status,
    };

    $statusClass = match ($article->status) {
        'published' => 'as-badge--published',
        'review'    => 'as-badge--review',
        'draft'     => 'as-badge--draft',
        'archived'  => 'as-badge--archived',
        default     => 'as-badge--draft',
    };
@endphp

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&display=swap" rel="stylesheet">

<style>
    .as-article-wrap {
        --as-bg: #faf5f0;
        --as-accent: #28414e;
        --as-accent-light: #3d5a6a;
        --as-ink: #28414e;
        --as-muted: #5a6a72;
        --as-border: #e8e0d8;
        --as-surface: #ffffff;
        --as-cream: #faf5f0;
        --as-radius: 16px;
        font-family: 'Inter', system-ui, sans-serif;
        color: var(--as-ink);
        line-height: 1.6;
        direction: ltr;
        text-align: left;
    }

    .as-article-wrap[dir="rtl"] {
        font-family: 'Amiri', 'Inter', system-ui, serif;
    }

    .as-article {
        background: var(--as-surface);
        border-radius: var(--as-radius);
        overflow: hidden;
        box-shadow:
            0 1px 2px rgba(40, 65, 78, 0.04),
            0 8px 24px rgba(40, 65, 78, 0.06),
            0 24px 48px rgba(40, 65, 78, 0.04);
        border: 1px solid var(--as-border);
    }

    /* Hero */
    .as-hero {
        position: relative;
        width: 100%;
        min-height: 340px;
        background: linear-gradient(135deg, #1e3340 0%, #28414e 50%, #3d5a6a 100%);
        display: flex;
        align-items: flex-end;
    }

    .as-hero--has-image {
        min-height: 420px;
    }

    .as-hero__img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .as-hero__overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(
            to top,
            rgba(40, 65, 78, 0.92) 0%,
            rgba(40, 65, 78, 0.5) 45%,
            rgba(40, 65, 78, 0.15) 100%
        );
    }

    .as-hero__content {
        position: relative;
        z-index: 2;
        width: 100%;
        padding: 1.25rem 1rem 1rem;
    }

    @media (min-width: 640px) {
        .as-hero__content {
            padding: 1.5rem 1.25rem 1.25rem;
        }
    }

    .as-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 0.75rem;
    }

    .as-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.3rem 0.85rem;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        line-height: 1;
    }

    .as-badge--category {
        background: var(--as-accent);
        color: #fff;
    }

    .as-badge--breaking {
        background: #dc2626;
        color: #fff;
        animation: as-pulse 2s ease-in-out infinite;
    }

    @keyframes as-pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.85; }
    }

    .as-badge--published { background: #e8f0ed; color: #28414e; }
    .as-badge--review    { background: #f5efe8; color: #3d5a6a; }
    .as-badge--draft     { background: #f0ebe5; color: #5a6a72; }
    .as-badge--archived  { background: #f5e8e8; color: #7a3a3a; }

    .as-hero__title {
        font-family: 'Playfair Display', 'Amiri', Georgia, serif;
        font-size: clamp(1.75rem, 4vw, 3rem);
        font-weight: 800;
        line-height: 1.15;
        color: #fff;
        margin: 0;
        letter-spacing: -0.02em;
        text-shadow: 0 2px 12px rgba(0, 0, 0, 0.3);
    }

    .as-article-wrap[dir="rtl"] .as-hero__title {
        font-family: 'Amiri', Georgia, serif;
        font-weight: 700;
        letter-spacing: 0;
    }

    .as-hero__subtitle {
        margin: 1rem 0 0;
        font-size: clamp(1rem, 2vw, 1.25rem);
        line-height: 1.55;
        color: rgba(255, 255, 255, 0.88);
        max-width: 100%;
    }

    /* Body shell */
    .as-body {
        max-width: 100%;
        margin: 0;
        padding: 1.25rem 1rem 1.5rem;
    }

    @media (min-width: 640px) {
        .as-body {
            padding: 1.5rem 1.25rem 2rem;
        }
    }

    /* Meta bar */
    .as-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem;
        padding-bottom: 1.25rem;
        margin-bottom: 1.25rem;
        border-bottom: 1px solid var(--as-border);
    }

    .as-meta__author {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .as-meta__avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--as-accent-light), var(--as-accent));
        color: #fff;
        font-weight: 700;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(40, 65, 78, 0.3);
    }

    .as-meta__name {
        font-weight: 600;
        font-size: 0.95rem;
        color: var(--as-ink);
        margin: 0;
    }

    .as-meta__role {
        font-size: 0.75rem;
        color: var(--as-muted);
        margin: 0.1rem 0 0;
    }

    .as-meta__items {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        flex: 1;
    }

    .as-meta__item-label {
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--as-muted);
        margin: 0 0 0.15rem;
    }

    .as-meta__item-value {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--as-ink);
        margin: 0;
    }

    /* Excerpt */
    .as-lead {
        font-family: 'Playfair Display', 'Amiri', Georgia, serif;
        font-size: clamp(1.15rem, 2.5vw, 1.45rem);
        line-height: 1.65;
        color: var(--as-muted);
        margin: 0 0 1.5rem;
        padding: 0.85rem 0 0.85rem 1rem;
        border-inline-start: 4px solid var(--as-accent);
        background: linear-gradient(to right, rgba(40, 65, 78, 0.06), transparent);
        font-style: italic;
    }

    .as-article-wrap[dir="rtl"] .as-lead {
        font-family: 'Amiri', Georgia, serif;
        font-style: normal;
        padding: 1.25rem 1.5rem 1.25rem 0;
        background: linear-gradient(to left, rgba(40, 65, 78, 0.06), transparent);
    }

    /* Article content */
    .as-content {
        font-size: 1.125rem;
        line-height: 1.9;
        color: #3d4f58;
        direction: ltr;
        text-align: left;
    }

    .as-article-wrap[dir="rtl"] .as-content {
        font-size: 1.25rem;
        line-height: 2;
    }

    .as-content > *:first-child {
        margin-top: 0;
    }

    .as-content > *:last-child {
        margin-bottom: 0;
    }

    .as-content p {
        margin: 0 0 1.5rem;
    }

    .as-content h1,
    .as-content h2,
    .as-content h3,
    .as-content h4 {
        font-family: 'Playfair Display', 'Amiri', Georgia, serif;
        font-weight: 700;
        color: var(--as-ink);
        margin: 2.5rem 0 1rem;
        line-height: 1.3;
    }

    .as-article-wrap[dir="rtl"] .as-content h1,
    .as-article-wrap[dir="rtl"] .as-content h2,
    .as-article-wrap[dir="rtl"] .as-content h3,
    .as-article-wrap[dir="rtl"] .as-content h4 {
        font-family: 'Amiri', Georgia, serif;
    }

    .as-content h2 { font-size: 1.65rem; }
    .as-content h3 { font-size: 1.35rem; }

    .as-content a {
        color: var(--as-accent);
        text-decoration: underline;
        text-underline-offset: 3px;
    }

    .as-content a:hover {
        color: var(--as-accent-light);
    }

    .as-content img {
        max-width: 100%;
        height: auto;
        border-radius: 12px;
        margin: 2rem 0;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    }

    .as-content blockquote {
        margin: 2rem 0;
        padding: 1.25rem 1.5rem;
        border-inline-start: 4px solid var(--as-accent);
        background: var(--as-cream);
        border-radius: 0 12px 12px 0;
        font-style: italic;
        color: var(--as-muted);
    }

    .as-article-wrap[dir="rtl"] .as-content blockquote {
        border-radius: 12px 0 0 12px;
        font-style: normal;
    }

    .as-content ul,
    .as-content ol {
        margin: 0 0 1.5rem;
        padding-inline-start: 1.5rem;
    }

    .as-content li {
        margin-bottom: 0.5rem;
    }

    .as-content strong {
        font-weight: 700;
        color: var(--as-ink);
    }

    /* Empty state */
    .as-empty {
        text-align: center;
        padding: 3rem 2rem;
        border: 2px dashed var(--as-border);
        border-radius: 12px;
        color: var(--as-muted);
        background: var(--as-cream);
    }

    /* Footer */
    .as-footer {
        margin-top: 1.5rem;
        padding-top: 1.25rem;
        border-top: 1px solid var(--as-border);
    }

    .as-footer__label {
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: var(--as-muted);
        margin: 0 0 0.75rem;
        font-weight: 600;
    }

    .as-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }

    .as-tag {
        padding: 0.35rem 0.9rem;
        background: var(--as-cream);
        border: 1px solid var(--as-border);
        border-radius: 999px;
        font-size: 0.85rem;
        color: var(--as-accent);
    }

    .as-cat-tag {
        padding: 0.35rem 0.9rem;
        background: var(--as-cream);
        border: 1px solid var(--as-border);
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--as-accent);
    }

    /* Stats */
    .as-stats {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
        margin-top: 1.25rem;
        padding: 0.85rem 1rem;
        background: var(--as-cream);
        border-radius: 12px;
        border: 1px solid var(--as-border);
    }

    @media (min-width: 640px) {
        .as-stats {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    .as-stat__label {
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--as-muted);
        margin: 0 0 0.25rem;
    }

    .as-stat__value {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--as-ink);
        margin: 0;
    }

    /* No-hero title block */
    .as-title-block {
        padding: 1.25rem 1rem 0;
        max-width: 100%;
        margin: 0;
    }

    @media (min-width: 640px) {
        .as-title-block {
            padding: 1.5rem 1.25rem 0;
        }
    }

    .as-title-block .as-hero__title {
        color: var(--as-ink);
        text-shadow: none;
    }

    .as-title-block .as-hero__subtitle {
        color: var(--as-muted);
    }

    /* ── Comments moderation ── */
    .as-comments {
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 2px solid var(--as-border);
    }

    .as-comments__header {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    .as-comments__title {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--as-accent);
        margin: 0;
    }

    .as-comments__subtitle {
        font-size: 0.8rem;
        color: var(--as-muted);
        margin: 0.25rem 0 0;
    }

    .as-comments__stats {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .as-comments__stat {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        border: 1px solid var(--as-border);
        background: var(--as-cream);
        color: var(--as-accent);
    }

    .as-comments__stat--pending {
        border-color: #d4c4a8;
        background: #f5efe8;
        animation: as-cmt-pulse 2.5s ease-in-out infinite;
    }

    .as-comments__stat--approved {
        background: #e8f0ed;
        border-color: #c5d5cf;
    }

    .as-comments__stat--rejected {
        background: #f5e8e8;
        border-color: #e0c5c5;
    }

    @keyframes as-cmt-pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.75; }
    }

    .as-comments__list {
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
    }

    .as-cmt {
        position: relative;
        display: flex;
        gap: 0.75rem;
        padding: 1rem 1rem 1rem 0.85rem;
        background: #fff;
        border: 1px solid var(--as-border);
        border-radius: 14px;
        transition: box-shadow 0.2s ease, transform 0.2s ease;
    }

    .as-cmt:hover {
        box-shadow: 0 6px 20px rgba(40, 65, 78, 0.08);
    }

    .as-cmt--reply {
        margin-inline-start: 2rem;
        background: var(--as-cream);
    }

    .as-cmt__accent {
        width: 4px;
        border-radius: 4px;
        flex-shrink: 0;
        background: #d4c4a8;
    }

    .as-cmt--approved .as-cmt__accent { background: #5a8a7a; }
    .as-cmt--rejected .as-cmt__accent { background: #b86b6b; }
    .as-cmt--pending .as-cmt__accent { background: var(--as-accent); }

    .as-cmt__main {
        flex: 1;
        min-width: 0;
    }

    .as-cmt__head {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.65rem;
    }

    .as-cmt__user {
        display: flex;
        align-items: center;
        gap: 0.65rem;
    }

    .as-cmt__avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--as-accent-light), var(--as-accent));
        color: #fff;
        font-size: 0.85rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .as-cmt__name {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--as-accent);
        margin: 0;
    }

    .as-cmt__date {
        font-size: 0.72rem;
        color: var(--as-muted);
        margin: 0.1rem 0 0;
    }

    .as-cmt__status {
        padding: 0.25rem 0.65rem;
        border-radius: 999px;
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .as-cmt__status--pending  { background: #f5efe8; color: #3d5a6a; }
    .as-cmt__status--approved { background: #e8f0ed; color: #28414e; }
    .as-cmt__status--rejected { background: #f5e8e8; color: #7a3a3a; }

    .as-cmt__body {
        font-size: 0.95rem;
        line-height: 1.65;
        color: #3d4f58;
        margin: 0 0 0.85rem;
        white-space: pre-wrap;
    }

    .as-cmt__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .as-cmt__btn {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.45rem 0.9rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: transform 0.15s ease, opacity 0.15s ease;
    }

    .as-cmt__btn:hover:not(:disabled) {
        transform: translateY(-1px);
    }

    .as-cmt__btn:disabled {
        opacity: 0.6;
        cursor: wait;
    }

    .as-cmt__btn--approve {
        background: var(--as-accent);
        color: #fff;
    }

    .as-cmt__btn--approve:hover:not(:disabled) {
        background: var(--as-accent-light);
    }

    .as-cmt__btn--reject {
        background: #fff;
        color: #7a3a3a;
        border: 1px solid #e0c5c5;
    }

    .as-cmt__btn--reject:hover:not(:disabled) {
        background: #f5e8e8;
    }

    .as-cmt__replies {
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
        margin-top: 0.65rem;
    }

    .as-comments__empty {
        text-align: center;
        padding: 2.5rem 1.5rem;
        border: 2px dashed var(--as-border);
        border-radius: 14px;
        background: var(--as-cream);
        color: var(--as-muted);
    }

    .as-comments__empty-icon {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        opacity: 0.5;
    }
</style>

<div class="as-article-wrap" dir="ltr" lang="en">
    <div class="as-article">

        @if ($featuredImageUrl)
            <div class="as-hero as-hero--has-image">
                <img class="as-hero__img" src="{{ $featuredImageUrl }}" alt="{{ $article->title }}">
                <div class="as-hero__overlay"></div>
                <div class="as-hero__content">
                    <div class="as-badges">
                        @if ($article->primaryCategory)
                            <span class="as-badge as-badge--category">{{ $article->primaryCategory->name }}</span>
                        @endif
                        @if ($article->is_breaking)
                            <span class="as-badge as-badge--breaking">Breaking</span>
                        @endif
                        <span class="as-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                    </div>
                    <h1 class="as-hero__title">{{ $article->title }}</h1>
                    @if (filled($article->subtitle))
                        <p class="as-hero__subtitle">{{ $article->subtitle }}</p>
                    @endif
                </div>
            </div>
        @else
            <div class="as-title-block">
                <div class="as-badges">
                    @if ($article->primaryCategory)
                        <span class="as-badge as-badge--category">{{ $article->primaryCategory->name }}</span>
                    @endif
                    @if ($article->is_breaking)
                        <span class="as-badge as-badge--breaking">Breaking</span>
                    @endif
                    <span class="as-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                </div>
                <h1 class="as-hero__title">{{ $article->title }}</h1>
                @if (filled($article->subtitle))
                    <p class="as-hero__subtitle">{{ $article->subtitle }}</p>
                @endif
            </div>
        @endif

        <div class="as-body">

            <div class="as-meta">
                <div class="as-meta__author">
                    <div class="as-meta__avatar">
                        {{ strtoupper(substr($article->author?->name ?? 'A', 0, 1)) }}
                    </div>
                    <div>
                        <p class="as-meta__name">{{ $article->author?->name ?? '—' }}</p>
                        <p class="as-meta__role">Author</p>
                    </div>
                </div>

                <div class="as-meta__items">
                    @if ($publishedAt)
                        <div>
                            <p class="as-meta__item-label">Published</p>
                            <p class="as-meta__item-value">
                                {{ $publishedAt->format('F j, Y') }}
                            </p>
                        </div>
                    @endif

                    @if ($article->read_time)
                        <div>
                            <p class="as-meta__item-label">Read time</p>
                            <p class="as-meta__item-value">
                                {{ $article->read_time }} min
                            </p>
                        </div>
                    @endif

                    <div>
                        <p class="as-meta__item-label">Language</p>
                        <p class="as-meta__item-value">{{ strtoupper($article->locale) }}</p>
                    </div>
                </div>
            </div>

            @if (filled($article->excerpt))
                <p class="as-lead">{{ $article->excerpt }}</p>
            @endif

            @if (filled($article->content))
                <div class="as-content">
                    {!! $article->content !!}
                </div>
            @else
                <div class="as-empty">
                    This article has no content yet.
                </div>
            @endif

            @if ($article->tags->isNotEmpty() || $article->secondaryCategories->isNotEmpty())
                <div class="as-footer">
                    @if ($article->tags->isNotEmpty())
                        <p class="as-footer__label">Tags</p>
                        <div class="as-tags">
                            @foreach ($article->tags as $tag)
                                <span class="as-tag">#{{ $tag->name }}</span>
                            @endforeach
                        </div>
                    @endif

                    @if ($article->secondaryCategories->isNotEmpty())
                        <p class="as-footer__label">Categories</p>
                        <div class="as-tags">
                            @foreach ($article->secondaryCategories as $category)
                                <span class="as-cat-tag">{{ $category->name }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            <div class="as-stats">
                <div>
                    <p class="as-stat__label">Views</p>
                    <p class="as-stat__value">{{ number_format($article->views_count) }}</p>
                </div>
                <div>
                    <p class="as-stat__label">Premium</p>
                    <p class="as-stat__value">{{ $article->is_premium ? 'Yes' : 'No' }}</p>
                </div>
                <div>
                    <p class="as-stat__label">Editor Pick</p>
                    <p class="as-stat__value">{{ $article->is_editor_pick ? 'Yes' : 'No' }}</p>
                </div>
                <div>
                    <p class="as-stat__label">Approved By</p>
                    <p class="as-stat__value">{{ $article->approvedBy?->name ?? '—' }}</p>
                </div>
            </div>

            <section class="as-comments">
                <div class="as-comments__header">
                    <div>
                        <h2 class="as-comments__title">Reader Comments</h2>
                        <p class="as-comments__subtitle">Review and moderate community feedback</p>
                    </div>
                    <div class="as-comments__stats">
                        <span class="as-comments__stat">{{ $commentCounts['total'] }} Total</span>
                        @if ($commentCounts['pending'] > 0)
                            <span class="as-comments__stat as-comments__stat--pending">
                                {{ $commentCounts['pending'] }} Pending
                            </span>
                        @endif
                        <span class="as-comments__stat as-comments__stat--approved">
                            {{ $commentCounts['approved'] }} Approved
                        </span>
                        <span class="as-comments__stat as-comments__stat--rejected">
                            {{ $commentCounts['rejected'] }} Rejected
                        </span>
                    </div>
                </div>

                @if ($comments->isEmpty())
                    <div class="as-comments__empty">
                        <div class="as-comments__empty-icon">💬</div>
                        <p>No comments on this article yet.</p>
                    </div>
                @else
                    <div class="as-comments__list">
                        @foreach ($comments as $comment)
                            @include('filament.articles.partials.comment-card', ['comment' => $comment, 'isReply' => false])
                        @endforeach
                    </div>
                @endif
            </section>

        </div>
    </div>
</div>
