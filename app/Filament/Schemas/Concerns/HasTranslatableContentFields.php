<?php

namespace App\Filament\Schemas\Concerns;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Illuminate\Validation\Rule;

trait HasTranslatableContentFields
{
    /**
     * @return array<int, mixed>
     */
    protected static function translatableContentFields(
        string $locale,
        string $translationTable,
        string $headlineLabel = 'Title',
        string $bodyLabel = 'Content',
    ): array {
        $label = strtoupper($locale);

        return [
            TextInput::make("title_{$locale}")
                ->label("{$headlineLabel} ({$label})")
                ->required()
                ->maxLength(500)
                ->columnSpanFull(),

            TextInput::make("subtitle_{$locale}")
                ->label("Subtitle ({$label})")
                ->maxLength(500)
                ->columnSpanFull(),

            TextInput::make("slug_{$locale}")
                ->label("Slug ({$label})")
                ->required()
                ->maxLength(500)
                ->rule(fn ($record) => Rule::unique($translationTable, 'slug')
                    ->where('locale', $locale)
                    ->ignore($record?->translate($locale, false)?->id))
                ->columnSpanFull(),

            Textarea::make("excerpt_{$locale}")
                ->label("Excerpt ({$label})")
                ->rows(3)
                ->columnSpanFull(),

            RichEditor::make("content_{$locale}")
                ->label("{$bodyLabel} ({$label})")
                ->columnSpanFull(),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    protected static function translatableSeoFields(string $locale, string $translationTable): array
    {
        $label = strtoupper($locale);

        return [
            TextInput::make("seo_title_{$locale}")
                ->label("SEO Title ({$label})")
                ->maxLength(200)
                ->columnSpanFull(),

            Textarea::make("seo_description_{$locale}")
                ->label("SEO Description ({$label})")
                ->rows(2)
                ->maxLength(400)
                ->columnSpanFull(),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    protected static function translatableSeoSections(string $translationTable): array
    {
        return [
            Section::make('English SEO')
                ->collapsed()
                ->schema(self::translatableSeoFields('en', $translationTable)),

            Section::make('Arabic SEO')
                ->collapsed()
                ->schema(self::translatableSeoFields('ar', $translationTable)),
        ];
    }
}
