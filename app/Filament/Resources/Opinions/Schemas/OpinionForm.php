<?php

namespace App\Filament\Resources\Opinions\Schemas;

use App\Filament\Schemas\Concerns\HasTranslatableContentFields;
use App\Models\Category;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class OpinionForm
{
    use HasTranslatableContentFields;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Opinion')
                    ->tabs([
                        Tab::make('Setup')
                            ->icon(Heroicon::OutlinedCog6Tooth)
                            ->schema([
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

                                Section::make('Media')
                                    ->schema([
                                        FileUpload::make('featured_image')
                                            ->label('Featured Image')
                                            ->image()
                                            ->disk('images')
                                            ->directory('opinions')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Tab::make('English')
                            ->icon(Heroicon::OutlinedLanguage)
                            ->schema(self::translatableContentFields('en', 'opinion_translations', 'Headline')),

                        Tab::make('Arabic')
                            ->icon(Heroicon::OutlinedLanguage)
                            ->schema(self::translatableContentFields('ar', 'opinion_translations', 'Headline')),

                        Tab::make('SEO')
                            ->icon(Heroicon::OutlinedMagnifyingGlass)
                            ->schema(self::translatableSeoSections('opinion_translations')),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
