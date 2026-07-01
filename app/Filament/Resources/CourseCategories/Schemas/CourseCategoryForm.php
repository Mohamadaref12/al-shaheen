<?php

namespace App\Filament\Resources\CourseCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class CourseCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Settings')
                    ->columns(2)
                    ->schema([
                        TextInput::make('icon')
                            ->label('Icon Key')
                            ->helperText('Icon identifier used by the frontend (e.g. pencil, camera, globe).')
                            ->maxLength(100),

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
                ->rule(fn ($record) => Rule::unique('course_category_translations', 'slug')
                    ->where('locale', $locale)
                    ->ignore($record?->translate($locale, false)?->id))
                ->columnSpanFull(),
        ];
    }
}
