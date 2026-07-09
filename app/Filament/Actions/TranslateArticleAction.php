<?php

namespace App\Filament\Actions;

use App\Contracts\ArticleTranslationService;
use App\Filament\Support\ArticleFormTranslator;
use App\Models\Article;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Livewire\Component;

class TranslateArticleAction
{
    public static function make(): Action
    {
        return Action::make('translateArticle')
            ->label(__('filament.actions.translate_article'))
            ->icon(Heroicon::OutlinedLanguage)
            ->color('info')
            ->requiresConfirmation()
            ->modalHeading(__('filament.actions.translate_article'))
            ->modalDescription(__('filament.actions.translate_article_confirm'))
            ->disabled(fn (): bool => ! app(ArticleTranslationService::class)->isAvailable())
            ->tooltip(fn (): ?string => app(ArticleTranslationService::class)->isAvailable()
                ? null
                : __('filament.actions.translate_article_unavailable'))
            ->action(function (Component $livewire): void {
                $article = self::resolveArticle($livewire);
                $state = $livewire->form->getRawState();
                $state = is_array($state) ? $state : $state->toArray();

                $result = app(ArticleFormTranslator::class)->translate(
                    $state,
                    auth()->user(),
                    $article,
                );

                if (! $result['success']) {
                    Notification::make()
                        ->title($result['message'])
                        ->danger()
                        ->send();

                    return;
                }

                $updates = $result['updates'];

                $livewire->form->fillPartially($updates, array_keys($updates));
                self::hydrateRichEditorFields($livewire, $updates);

                Notification::make()
                    ->title($result['message'])
                    ->success()
                    ->send();
            });
    }

    protected static function resolveArticle(Component $livewire): ?Article
    {
        if (! method_exists($livewire, 'getRecord')) {
            return null;
        }

        $record = $livewire->getRecord();

        if (! $record instanceof Article || ! $record->exists) {
            return null;
        }

        return $record;
    }

    /**
     * @param  array<string, mixed>  $updates
     */
    protected static function hydrateRichEditorFields(Component $livewire, array $updates): void
    {
        foreach (['content_ar', 'content_en'] as $key) {
            if (! array_key_exists($key, $updates)) {
                continue;
            }

            $component = $livewire->form->getComponentByStatePath($key);

            if (! $component instanceof RichEditor) {
                continue;
            }

            $rawState = $updates[$key];

            foreach ($component->getStateCasts() as $stateCast) {
                $rawState = $stateCast->set($rawState);
            }

            $component->state($rawState);
            $component->callAfterStateHydrated();
        }
    }
}
