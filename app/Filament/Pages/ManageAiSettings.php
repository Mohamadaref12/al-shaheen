<?php

namespace App\Filament\Pages;

use App\Support\AiSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
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
use UnitEnum;

class ManageAiSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCpuChip;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'AI Settings';

    protected static ?string $title = 'AI Settings';

    protected static ?string $slug = 'ai-settings';

    protected static ?int $navigationSort = 99;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(AiSettings::toFormArray());
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('OpenAI Translation')
                    ->description('Configure GPT for article translation (Arabic ↔ English). Suggestions are sent via API for client approval — never auto-applied.')
                    ->schema([
                        Toggle::make('ai_enabled')
                            ->label('Enable AI')
                            ->inline(false),

                        TextInput::make('openai_api_key')
                            ->label('OpenAI API Key')
                            ->password()
                            ->revealable()
                            ->placeholder(fn (): string => AiSettings::hasApiKey()
                                ? '•••••••• (saved — leave blank to keep current key)'
                                : 'sk-...')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->helperText('Stored encrypted. Falls back to OPENAI_API_KEY in .env if empty.'),

                        Select::make('openai_model')
                            ->label('Model')
                            ->options([
                                'gpt-4o-mini' => 'gpt-4o-mini (recommended)',
                                'gpt-4o'      => 'gpt-4o',
                                'gpt-4.1-mini'=> 'gpt-4.1-mini',
                                'gpt-4.1'     => 'gpt-4.1',
                            ])
                            ->required(),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('ai-settings-form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('Save settings')
                                ->submit('save'),
                        ]),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        AiSettings::updateFromForm($data);

        Notification::make()
            ->success()
            ->title('AI settings saved')
            ->send();
    }

    public static function canAccess(): bool
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        return $user?->admin()->exists() ?? false;
    }
}
