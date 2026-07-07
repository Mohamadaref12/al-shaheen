<?php

namespace App\Filament\Resources\Opinions\Schemas;

use App\Models\Category;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class OpinionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Opinion Details')
                    ->columns(2)
                    ->schema([
                        Select::make('author_id')
                            ->label('Author')
                            ->relationship('author', 'name')
                            ->searchable()
                            ->required(),

                        Select::make('category_id')
                            ->label('Category')
                            ->options(fn () => Category::query()
                                ->whereHas('parent', fn ($q) => $q->where('slug', 'opinion'))
                                ->orWhere('slug', 'opinion')
                                ->orderBy('name')
                                ->pluck('name', 'id'))
                            ->searchable(),

                        TextInput::make('read_time')
                            ->label('Read Time (minutes)')
                            ->numeric()
                            ->minValue(1)
                            ->default(5),

                        Select::make('status')
                            ->options([
                                'draft'        => 'Draft',
                                'under_review' => 'Under Review',
                                'published'    => 'Published',
                                'archived'     => 'Archived',
                            ])
                            ->required()
                            ->default('draft'),

                        Toggle::make('is_premium')
                            ->label('Premium'),

                        DateTimePicker::make('published_at')
                            ->label('Published At'),
                    ]),

                Section::make('English Content')
                    ->schema(self::translationFields('en')),

                Section::make('Arabic Content')
                    ->schema(self::translationFields('ar')),

                Section::make('Media')
                    ->schema([
                        FileUpload::make('featured_image')
                            ->label('Featured Image')
                            ->image()
                            ->disk('images')
                            ->directory('opinions')
                            ->columnSpanFull(),
                    ]),
            ])->columns(1);
    }

    private static function translationFields(string $locale): array
    {
        $label = strtoupper($locale);

        return [
            TextInput::make("title_{$locale}")
                ->label("Headline ({$label})")
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
                ->rule(fn ($record) => Rule::unique('opinion_translations', 'slug')
                    ->where('locale', $locale)
                    ->ignore($record?->translate($locale, false)?->id))
                ->columnSpanFull(),

            Textarea::make("excerpt_{$locale}")
                ->label("Excerpt ({$label})")
                ->rows(3)
                ->columnSpanFull(),

            RichEditor::make("content_{$locale}")
                ->label("Content ({$label})")
                ->columnSpanFull(),

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
}
