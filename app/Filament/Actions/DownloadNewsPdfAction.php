<?php

namespace App\Filament\Actions;

use App\Models\News;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Livewire\Component;

class DownloadNewsPdfAction
{
    public static function make(): Action
    {
        return Action::make('downloadPdf')
            ->label('Download PDF')
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->color('gray')
            ->form([
                Select::make('locale')
                    ->label('Language')
                    ->options([
                        'ar' => 'Arabic',
                        'en' => 'English',
                    ])
                    ->default('ar')
                    ->required(),
            ])
            ->action(function (array $data, News $record, Component $livewire): void {
                $livewire->redirect(
                    route('admin.news.pdf', [
                        'news'   => $record->getKey(),
                        'locale' => $data['locale'],
                    ]),
                    navigate: false
                );
            });
    }
}
