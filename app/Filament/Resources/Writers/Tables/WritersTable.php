<?php

namespace App\Filament\Resources\Writers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WritersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('display_name')
                    ->searchable(),

                TextColumn::make('experience_level')
                    ->badge(),

                TextColumn::make('application_status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'approved'     => 'success',
                        'rejected'     => 'danger',
                        'suspended'    => 'danger',
                        'under_review' => 'warning',
                        'submitted'    => 'info',
                        default        => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('application_status')
                    ->options([
                        'draft'        => 'Draft',
                        'submitted'    => 'Submitted',
                        'under_review' => 'Under Review',
                        'approved'     => 'Approved',
                        'rejected'     => 'Rejected',
                        'suspended'    => 'Suspended',
                    ]),

                SelectFilter::make('experience_level')
                    ->options([
                        'beginner'     => 'Beginner',
                        'intermediate' => 'Intermediate',
                        'senior'       => 'Senior',
                        'expert'       => 'Expert',
                    ]),
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