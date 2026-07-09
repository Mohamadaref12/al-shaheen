<x-filament-widgets::widget>
    <div class="as-dashboard-hero">
        <div class="as-dashboard-hero__content">
            <p class="as-dashboard-hero__eyebrow">{{ __('filament.brand.eyebrow') }}</p>
            <h2 class="as-dashboard-hero__title">
                @if ($userName)
                    {{ __('filament.dashboard.welcome_back', ['name' => $userName]) }}
                @else
                    {{ __('filament.dashboard.welcome_back_short') }}
                @endif
            </h2>
            <p class="as-dashboard-hero__subtitle">{{ $dateLabel }}</p>
        </div>

        @if ($attentionCount > 0)
            <div class="as-dashboard-hero__badge" role="status">
                <span class="as-dashboard-hero__badge-count">{{ $attentionCount }}</span>
                <span class="as-dashboard-hero__badge-label">{{ __('filament.dashboard.attention_items') }}</span>
            </div>
        @else
            <div class="as-dashboard-hero__badge as-dashboard-hero__badge--clear" role="status">
                <span class="as-dashboard-hero__badge-label">{{ __('filament.dashboard.all_caught_up') }}</span>
            </div>
        @endif
    </div>
</x-filament-widgets::widget>
