<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Widgets\Concerns\ConfiguresDashboardTable;
use App\Models\ContactMessage;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class UnreadContactMessagesWidget extends TableWidget
{
    protected static bool $isDiscovered = false;

    use ConfiguresDashboardTable;

    protected static bool $isLazy = false;

    protected static ?int $sort = 9;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $this->configureDashboardTable(
            $table
                ->heading('Unread Contact Messages')
                ->description('Messages from the contact form awaiting review')
                ->query(fn (): Builder => ContactMessage::query()
                    ->unread()
                    ->latest()
                    ->limit(5))
                ->emptyStateHeading('No unread messages')
                ->emptyStateDescription('New contact form submissions will appear here.')
                ->columns([
                    TextColumn::make('name')
                        ->label('Sender')
                        ->weight(FontWeight::SemiBold)
                        ->size(TextSize::Small)
                        ->description(fn (ContactMessage $record): string => $record->email)
                        ->grow(false),

                    TextColumn::make('subject')
                        ->label('Subject')
                        ->wrap()
                        ->lineClamp(2)
                        ->size(TextSize::Small)
                        ->extraCellAttributes(['dir' => 'auto']),

                    $this->dashboardSinceColumn('created_at', 'Received'),
                ])
                ->recordUrl(fn (ContactMessage $record): string => ContactMessageResource::getUrl('view', ['record' => $record]))
        );
    }
}
