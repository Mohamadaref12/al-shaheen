<?php

namespace App\Filament\Resources\Writers\Support;

use App\Models\Writer;
use App\Support\TransactionalMailer;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;

class WriterVerificationReview
{
    /**
     * Verified tier requirements — AL_SHAHEEN_360_SCENARIO_AR.md (step 4).
     *
     * @return list<string>
     */
    public static function missingRequirements(Writer $writer): array
    {
        $missing = [];

        if (blank($writer->id_verification_file)) {
            $missing[] = 'ID verification document';
        }

        if (! self::hasSampleWork($writer->sample_publications, $writer->portfolio_link)) {
            $missing[] = 'Sample publications or portfolio link';
        }

        if (blank($writer->media_affiliation)) {
            $missing[] = 'Media affiliation';
        }

        return $missing;
    }

    /**
     * @return array<string, mixed>
     */
    public static function fillFromWriter(Writer $writer): array
    {
        return [
            'id_verification_file' => $writer->id_verification_file,
            'sample_publications'  => $writer->sample_publications ?? [],
            'portfolio_link'       => $writer->portfolio_link,
            'media_affiliation'    => $writer->media_affiliation,
            'experience_level'     => $writer->experience_level,
            'verification_notes'   => $writer->reviewer_notes,
        ];
    }

    /**
     * @return array<int, \Filament\Forms\Components\Component>
     */
    public static function formSchema(): array
    {
        return [
            Placeholder::make('verification_guide')
                ->label('Verified Writer tier')
                ->content(new HtmlString(
                    '<p class="text-sm text-gray-600 dark:text-gray-400">Grant Verified tier only after reviewing the writer\'s identity document, sample work, and media affiliation.</p>'
                    .'<ul class="mt-2 list-disc ps-5 text-sm text-gray-600 dark:text-gray-400">'
                    .'<li>ID verification file</li>'
                    .'<li>Sample publications <em>or</em> portfolio link</li>'
                    .'<li>Media affiliation</li>'
                    .'</ul>'
                ))
                ->columnSpanFull(),

            FileUpload::make('id_verification_file')
                ->label('ID Verification File')
                ->disk('images')
                ->directory('writers/verification')
                ->required()
                ->columnSpanFull(),

            KeyValue::make('sample_publications')
                ->label('Sample Publications')
                ->keyLabel('Title')
                ->valueLabel('URL')
                ->columnSpanFull(),

            TextInput::make('portfolio_link')
                ->label('Portfolio Link')
                ->url()
                ->maxLength(255),

            TextInput::make('media_affiliation')
                ->label('Media Affiliation')
                ->required()
                ->maxLength(255),

            Select::make('experience_level')
                ->label('Experience Level')
                ->options([
                    'junior' => 'Junior',
                    'mid'    => 'Mid-level',
                    'senior' => 'Senior',
                    'expert' => 'Expert',
                ]),

            Checkbox::make('id_document_reviewed')
                ->label('I reviewed the ID verification document')
                ->rule('accepted')
                ->validationAttribute('ID document review'),

            Checkbox::make('samples_reviewed')
                ->label('I reviewed the sample work / portfolio')
                ->rule('accepted')
                ->validationAttribute('sample work review'),

            Textarea::make('verification_notes')
                ->label('Verification notes')
                ->helperText('Saved in reviewer notes for audit trail.')
                ->rows(3)
                ->maxLength(2000)
                ->columnSpanFull(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function grantVerifiedTier(Writer $writer, array $data): void
    {
        $samples = $data['sample_publications'] ?? [];

        if (! self::hasSampleWork($samples, $data['portfolio_link'] ?? null)) {
            Notification::make()
                ->title('Sample work required')
                ->body('Add at least one sample publication or a portfolio link before granting Verified tier.')
                ->danger()
                ->send();

            return;
        }

        $writer->update([
            'id_verification_file' => $data['id_verification_file'],
            'sample_publications'  => $samples,
            'portfolio_link'       => $data['portfolio_link'] ?? null,
            'media_affiliation'    => $data['media_affiliation'],
            'experience_level'     => $data['experience_level'] ?? $writer->experience_level,
            'reviewer_notes'       => self::buildVerificationNotes($writer, $data['verification_notes'] ?? null),
            'is_verified_writer'   => true,
        ]);

        $writer->loadMissing('user');

        if ($writer->user) {
            TransactionalMailer::sendToUser($writer->user, 'writer.verified_granted', [
                'name' => $writer->user->name,
            ]);
        }

        Notification::make()
            ->title('Verified tier granted')
            ->body(($writer->display_name ?: $writer->user?->name ?: 'Writer').' now has the Verified Writer badge.')
            ->success()
            ->send();
    }

    public static function revokeVerifiedTier(Writer $writer, ?string $reason = null): void
    {
        $notes = $writer->reviewer_notes;

        if (filled($reason)) {
            $notes = trim(($notes ? $notes."\n\n" : '').'Verification removed: '.$reason);
        }

        $writer->update([
            'is_verified_writer' => false,
            'reviewer_notes'     => $notes,
        ]);

        $writer->loadMissing('user');

        if ($writer->user) {
            TransactionalMailer::sendToUser($writer->user, 'writer.verified_revoked', [
                'name'  => $writer->user->name,
                'notes' => (string) ($reason ?? ''),
            ]);
        }

        Notification::make()
            ->title('Verification removed')
            ->warning()
            ->send();
    }

    /**
     * @param  array<string, mixed>|null  $samples
     */
    private static function hasSampleWork(?array $samples, ?string $portfolioLink): bool
    {
        if (filled($portfolioLink)) {
            return true;
        }

        if (! is_array($samples)) {
            return false;
        }

        foreach ($samples as $title => $url) {
            if (filled($title) || filled($url)) {
                return true;
            }
        }

        return false;
    }

    private static function buildVerificationNotes(Writer $writer, ?string $verificationNotes): ?string
    {
        $stamp = 'Verified tier granted on '.now()->format('Y-m-d H:i');

        if (blank($verificationNotes)) {
            return trim(($writer->reviewer_notes ? $writer->reviewer_notes."\n\n" : '').$stamp);
        }

        return trim(($writer->reviewer_notes ? $writer->reviewer_notes."\n\n" : '').$verificationNotes."\n\n".$stamp);
    }
}
