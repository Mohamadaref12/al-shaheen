<?php

namespace App\Filament\Resources\TrainingCourses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TrainingCoursesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(60),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->sortable(),

                TextColumn::make('level')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'beginner'     => 'success',
                        'intermediate' => 'warning',
                        'advanced'     => 'danger',
                        default        => 'gray',
                    }),

                TextColumn::make('price')
                    ->money('usd')
                    ->sortable(),

                TextColumn::make('lessons_count')
                    ->label('Lessons')
                    ->counts('lessons')
                    ->sortable(),

                IconColumn::make('is_premium')
                    ->label('Premium')
                    ->boolean(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('course_category_id')
                    ->label('Category')
                    ->relationship('category', 'name'),

                SelectFilter::make('level')
                    ->options([
                        'beginner'     => 'Beginner',
                        'intermediate' => 'Intermediate',
                        'advanced'     => 'Advanced',
                    ]),

                TernaryFilter::make('is_premium')
                    ->label('Premium'),

                TernaryFilter::make('is_active')
                    ->label('Active'),
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
