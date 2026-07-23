<?php

namespace App\Filament\Pages;

use App\Support\ComingSoonSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ManageComingSoonSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLockClosed;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    public static function getNavigationLabel(): string
    {
        return __('filament.pages.coming_soon_settings');
    }

    public function getTitle(): string
    {
        return __('filament.pages.coming_soon_settings');
    }

    protected static ?string $slug = 'coming-soon-settings';

    protected static ?int $navigationSort = 98;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(ComingSoonSettings::toFormArray());
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament.pages.coming_soon_settings'))
                    ->description(__('filament.pages.coming_soon_settings_description'))
                    ->schema([
                        Toggle::make('coming_soon_enabled')
                            ->label(__('filament.pages.coming_soon_enabled'))
                            ->helperText(__('filament.pages.coming_soon_enabled_help'))
                            ->inline(false),

                        TextInput::make('coming_soon_access_key')
                            ->label(__('filament.pages.coming_soon_access_key'))
                            ->password()
                            ->revealable()
                            ->placeholder(fn (): string => ComingSoonSettings::hasAccessKey()
                                ? __('filament.pages.coming_soon_access_key_saved')
                                : __('filament.pages.coming_soon_access_key_placeholder'))
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->helperText(__('filament.pages.coming_soon_access_key_help')),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('coming-soon-settings-form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label(__('filament.pages.coming_soon_save'))
                                ->submit('save'),
                        ]),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $willEnable = (bool) ($data['coming_soon_enabled'] ?? false);
        $newKey = $data['coming_soon_access_key'] ?? null;

        if ($willEnable && blank($newKey) && ! ComingSoonSettings::hasAccessKey()) {
            Notification::make()
                ->danger()
                ->title(__('filament.pages.coming_soon_key_required'))
                ->send();

            return;
        }

        ComingSoonSettings::updateFromForm($data);

        Notification::make()
            ->success()
            ->title(__('filament.pages.coming_soon_saved'))
            ->send();
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user?->admin()->exists() ?? false;
    }
}
