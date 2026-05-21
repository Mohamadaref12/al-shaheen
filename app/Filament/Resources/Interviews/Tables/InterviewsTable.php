<?php

namespace App\Filament\Resources\Interviews\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InterviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(60),

                TextColumn::make('guest_name')
                    ->label('Guest')
                    ->searchable(),

                TextColumn::make('author.name')
                    ->label('Author')
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'published'    => 'success',
                        'under_review' => 'warning',
                        'draft'        => 'gray',
                        'archived'     => 'danger',
                        default        => 'gray',
                    }),

                TextColumn::make('locale')->badge(),

                IconColumn::make('is_premium')
                    ->label('Premium')
                    ->boolean(),

                TextColumn::make('views_count')
                    ->label('Views')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft'        => 'Draft',
                        'under_review' => 'Under Review',
                        'published'    => 'Published',
                        'archived'     => 'Archived',
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
