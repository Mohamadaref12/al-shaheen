<?php

namespace App\Filament\Resources\CourseCategories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CourseCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('translations'))
            ->columns([
                TextColumn::make('display_name')
                    ->label('Name')
                    ->searchable(query: function (Builder $query, string $search): void {
                        $query->whereHas('translations', fn (Builder $q) => $q
                            ->where('name', 'like', "%{$search}%"));
                    }),

                TextColumn::make('slug_en')
                    ->label('Slug (EN)')
                    ->toggleable(),

                TextColumn::make('slug_ar')
                    ->label('Slug (AR)')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('icon')
                    ->placeholder('—'),

                TextColumn::make('courses_count')
                    ->label('Courses')
                    ->counts('courses')
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                TernaryFilter::make('is_active')->label('Active'),
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
