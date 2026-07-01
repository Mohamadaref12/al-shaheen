<?php

namespace App\Filament\Resources\News\Tables;

use App\Filament\Actions\DownloadNewsPdfAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class NewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('translations'))
            ->defaultSort('published_at', 'desc')
            ->columns([
                ImageColumn::make('featured_image')
                    ->label('')
                    ->disk('images')
                    ->circular()
                    ->imageHeight(44),

                TextColumn::make('title_ar')
                    ->label('Title (AR)')
                    ->description(fn ($record): ?string => $record->title_en)
                    ->searchable(query: fn ($query, string $search) => $query->whereHas(
                        'translations',
                        fn ($q) => $q->where('title', 'like', "%{$search}%")
                    ))
                    ->limit(50),

                TextColumn::make('author.name')
                    ->label('Author')
                    ->icon(Heroicon::OutlinedUser)
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'under_review' => 'warning',
                        'draft' => 'gray',
                        'archived' => 'danger',
                        default => 'gray',
                    }),

                IconColumn::make('is_breaking')
                    ->label('Breaking')
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedBolt)
                    ->trueColor('danger'),

                IconColumn::make('is_premium')
                    ->label('Premium')
                    ->boolean(),

                TextColumn::make('views_count')
                    ->label('Views')
                    ->icon(Heroicon::OutlinedEye)
                    ->numeric()
                    ->sortable(),

                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'under_review' => 'Under Review',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ]),

                TernaryFilter::make('has_arabic')
                    ->label('Has Arabic')
                    ->queries(
                        true: fn ($query) => $query->translatedIn('ar'),
                        false: fn ($query) => $query->whereDoesntHave('translations', fn ($q) => $q->where('locale', 'ar')),
                    ),

                TernaryFilter::make('has_english')
                    ->label('Has English')
                    ->queries(
                        true: fn ($query) => $query->translatedIn('en'),
                        false: fn ($query) => $query->whereDoesntHave('translations', fn ($q) => $q->where('locale', 'en')),
                    ),

                TernaryFilter::make('is_breaking')
                    ->label('Breaking'),

                TernaryFilter::make('is_premium')
                    ->label('Premium'),
            ])
            ->recordActions([
                DownloadNewsPdfAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
