<?php

namespace App\Filament\Resources\SecondaryCategories\Schemas;

use App\Filament\Support\CategorySelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class SecondaryCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Secondary Category Settings')
                    ->columns(2)
                    ->schema([
                        CategorySelect::configure(
                            Select::make('parent_id')
                                ->label('Primary Category')
                                ->relationship(
                                    'parent',
                                    modifyQueryUsing: fn ($query) => $query
                                        ->with('translations')
                                        ->whereNull('parent_id')
                                        ->orderBy('sort_order')
                                )
                        )
                            ->searchable()
                            ->required()
                            ->preload(),

                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),

                Section::make('English Content')
                    ->schema(self::translationFields('en')),

                Section::make('Arabic Content')
                    ->schema(self::translationFields('ar')),
            ])->columns(1);
    }

    private static function translationFields(string $locale): array
    {
        $label = strtoupper($locale);

        return [
            TextInput::make("name_{$locale}")
                ->label("Name ({$label})")
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            TextInput::make("slug_{$locale}")
                ->label("Slug ({$label})")
                ->required()
                ->maxLength(255)
                ->rule(fn ($record) => Rule::unique('category_translations', 'slug')
                    ->where('locale', $locale)
                    ->ignore($record?->translate($locale, false)?->id))
                ->columnSpanFull(),

            Textarea::make("description_{$locale}")
                ->label("Description ({$label})")
                ->columnSpanFull(),
        ];
    }
}
