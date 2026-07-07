<?php

namespace App\Filament\Widgets\Concerns;

use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

trait ConfiguresDashboardTable
{
    protected function configureDashboardTable(Table $table, int $limit = 5): Table
    {
        return $table
            ->paginated(false)
            ->recordActions([])
            ->toolbarActions([]);
    }

    protected function dashboardTitleColumn(string $name = 'display_title'): TextColumn
    {
        return TextColumn::make($name)
            ->label('Title')
            ->wrap()
            ->lineClamp(2)
            ->size(TextSize::Small)
            ->extraCellAttributes(['dir' => 'auto'])
            ->tooltip(fn ($record): ?string => $record->display_title ?? null);
    }

    protected function dashboardSinceColumn(string $name, string $label): TextColumn
    {
        return TextColumn::make($name)
            ->label($label)
            ->since()
            ->size(TextSize::Small)
            ->color('gray')
            ->alignEnd()
            ->grow(false);
    }
}
