<x-filament-widgets::widget>
    <div class="as-dashboard-hero">
        <div class="as-dashboard-hero__content">
            <p class="as-dashboard-hero__eyebrow">Al Shaheen · Newsroom</p>
            <h2 class="as-dashboard-hero__title">
                @if ($userName)
                    Welcome back, {{ $userName }}
                @else
                    Welcome back
                @endif
            </h2>
            <p class="as-dashboard-hero__subtitle">{{ $dateLabel }}</p>
        </div>

        @if ($attentionCount > 0)
            <div class="as-dashboard-hero__badge" role="status">
                <span class="as-dashboard-hero__badge-count">{{ $attentionCount }}</span>
                <span class="as-dashboard-hero__badge-label">items need your attention</span>
            </div>
        @else
            <div class="as-dashboard-hero__badge as-dashboard-hero__badge--clear" role="status">
                <span class="as-dashboard-hero__badge-label">All caught up</span>
            </div>
        @endif
    </div>
</x-filament-widgets::widget>
