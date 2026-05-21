<?php

namespace App\Filament\Resources\MediaItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MediaItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(60),

                TextColumn::make('type')->badge(),

                TextColumn::make('author.name')
                    ->label('Author')
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'published' => 'success',
                        'draft'     => 'gray',
                        'archived'  => 'danger',
                        default     => 'gray',
                    }),

                TextColumn::make('locale')->badge(),

                IconColumn::make('is_premium')
                    ->label('Premium')
                    ->boolean(),

                TextColumn::make('duration_seconds')
                    ->label('Duration (s)')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'video'   => 'Video',
                        'audio'   => 'Audio',
                        'gallery' => 'Gallery',
                    ]),

                SelectFilter::make('status')
                    ->options([
                        'draft'     => 'Draft',
                        'published' => 'Published',
                        'archived'  => 'Archived',
                    ]),

                SelectFilter::make('locale')
                    ->options(['ar' => 'Arabic', 'en' => 'English']),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
