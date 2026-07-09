<?php

namespace App\Filament\Resources\Writers\Support;

use App\Models\Writer;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class WriterApplicationStatusActions
{
    /**
     * @var list<string>
     */
    public const PENDING_APPROVAL_STATUSES = ['submitted', 'under_review'];

    public static function transition(Writer $writer, string $status, ?string $reviewerNotes = null): void
    {
        $data = ['application_status' => $status];

        if ($reviewerNotes !== null) {
            $data['reviewer_notes'] = $reviewerNotes;
        }

        $writer->update($data);

        Notification::make()
            ->title('Writer status updated')
            ->body('Application status is now: '.self::labelFor($status))
            ->success()
            ->send();
    }

    /**
     * @return array<int, Action>
     */
    public static function tableQuickActions(): array
    {
        return [
            self::approveTableAction(),
            self::rejectTableAction(),
        ];
    }

    public static function approveTableAction(): Action
    {
        return Action::make('approve')
            ->label('Approve')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->visible(fn (Writer $record): bool => $record->application_status !== 'approved')
            ->requiresConfirmation()
            ->modalHeading('Approve writer application')
            ->modalDescription(fn (Writer $record): string => 'Approve '.self::displayName($record).' and allow them to publish as a writer?')
            ->action(fn (Writer $record) => self::transition($record, 'approved'));
    }

    public static function rejectTableAction(): Action
    {
        return Action::make('reject')
            ->label('Reject')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->visible(fn (Writer $record): bool => $record->application_status !== 'rejected')
            ->requiresConfirmation()
            ->modalHeading('Reject writer application')
            ->schema([
                Textarea::make('reviewer_notes')
                    ->label('Reviewer notes')
                    ->rows(3)
                    ->maxLength(2000),
            ])
            ->action(fn (Writer $record, array $data) => self::transition(
                $record,
                'rejected',
                $data['reviewer_notes'] ?? null,
            ));
    }

    /**
     * @return array<int, Action|ActionGroup>
     */
    public static function make(Closure $getRecord, ?Closure $after = null): array
    {
        $record = fn (): Writer => $getRecord();

        return [
            self::approveAction($record, $after),
            self::rejectAction($record, $after),
            ActionGroup::make([
                self::underReviewAction($record, $after),
                self::suspendAction($record, $after),
                self::reinstateAction($record, $after),
                self::markSubmittedAction($record, $after),
                self::resetToDraftAction($record, $after),
            ])
                ->label('Change Status')
                ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                ->color('gray')
                ->button()
                ->dropdownPlacement('bottom-end'),
            ActionGroup::make([
                self::verifyAction($record, $after),
                self::removeVerificationAction($record, $after),
            ])
                ->label('Verification')
                ->icon(Heroicon::OutlinedShieldCheck)
                ->color('gray')
                ->button()
                ->dropdownPlacement('bottom-end'),
        ];
    }

    private static function approveAction(Closure $record, ?Closure $after): Action
    {
        return self::withAfter(
            Action::make('approve')
                ->label('Approve')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->visible(fn (): bool => $record()->application_status !== 'approved')
                ->requiresConfirmation()
                ->modalHeading('Approve writer')
                ->modalDescription(fn (): string => 'Approve '.self::displayName($record()).'?')
                ->action(fn () => self::transition($record(), 'approved')),
            $after,
        );
    }

    private static function rejectAction(Closure $record, ?Closure $after): Action
    {
        return self::withAfter(
            Action::make('reject')
                ->label('Reject')
                ->icon(Heroicon::OutlinedXCircle)
                ->color('danger')
                ->visible(fn (): bool => $record()->application_status !== 'rejected')
                ->requiresConfirmation()
                ->modalHeading('Reject writer application')
                ->schema([
                    Textarea::make('reviewer_notes')
                        ->label('Reviewer notes')
                        ->rows(3)
                        ->maxLength(2000),
                ])
                ->action(fn (array $data) => self::transition(
                    $record(),
                    'rejected',
                    $data['reviewer_notes'] ?? null,
                )),
            $after,
        );
    }

    private static function underReviewAction(Closure $record, ?Closure $after): Action
    {
        return self::withAfter(
            Action::make('underReview')
                ->label('Mark Under Review')
                ->icon(Heroicon::OutlinedClock)
                ->visible(fn (): bool => in_array($record()->application_status, ['submitted', 'draft'], true))
                ->requiresConfirmation()
                ->action(fn () => self::transition($record(), 'under_review')),
            $after,
        );
    }

    private static function suspendAction(Closure $record, ?Closure $after): Action
    {
        return self::withAfter(
            Action::make('suspend')
                ->label('Suspend')
                ->icon(Heroicon::OutlinedNoSymbol)
                ->color('danger')
                ->visible(fn (): bool => $record()->application_status === 'approved')
                ->requiresConfirmation()
                ->modalHeading('Suspend writer')
                ->modalDescription('This writer will no longer appear as approved in the app.')
                ->action(fn () => self::transition($record(), 'suspended')),
            $after,
        );
    }

    private static function reinstateAction(Closure $record, ?Closure $after): Action
    {
        return self::withAfter(
            Action::make('reinstate')
                ->label('Reinstate')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('success')
                ->visible(fn (): bool => in_array($record()->application_status, ['suspended', 'rejected'], true))
                ->requiresConfirmation()
                ->action(fn () => self::transition($record(), 'approved')),
            $after,
        );
    }

    private static function markSubmittedAction(Closure $record, ?Closure $after): Action
    {
        return self::withAfter(
            Action::make('markSubmitted')
                ->label('Mark as Submitted')
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->visible(fn (): bool => $record()->application_status === 'draft')
                ->requiresConfirmation()
                ->action(fn () => self::transition($record(), 'submitted')),
            $after,
        );
    }

    private static function resetToDraftAction(Closure $record, ?Closure $after): Action
    {
        return self::withAfter(
            Action::make('resetToDraft')
                ->label('Reset to Draft')
                ->icon(Heroicon::OutlinedDocument)
                ->visible(fn (): bool => $record()->application_status !== 'draft')
                ->requiresConfirmation()
                ->action(fn () => self::transition($record(), 'draft')),
            $after,
        );
    }

    private static function verifyAction(Closure $record, ?Closure $after): Action
    {
        return self::withAfter(
            Action::make('verify')
                ->label('Review & Grant Verified Tier')
                ->icon(Heroicon::OutlinedCheckBadge)
                ->color('success')
                ->visible(fn (): bool => ! $record()->is_verified_writer && $record()->application_status === 'approved')
                ->slideOver()
                ->modalWidth('2xl')
                ->modalHeading('Verified Writer review')
                ->modalDescription(function () use ($record): string {
                    $missing = WriterVerificationReview::missingRequirements($record());

                    if ($missing === []) {
                        return 'All required documents are on file. Complete the reviewer checklist to grant Verified tier.';
                    }

                    return 'Missing from application: '.implode(', ', $missing).'. You can add them below before granting Verified tier.';
                })
                ->fillForm(fn (): array => WriterVerificationReview::fillFromWriter($record()))
                ->schema(WriterVerificationReview::formSchema())
                ->action(fn (array $data) => WriterVerificationReview::grantVerifiedTier($record(), $data)),
            $after,
        );
    }

    private static function removeVerificationAction(Closure $record, ?Closure $after): Action
    {
        return self::withAfter(
            Action::make('removeVerification')
                ->label('Remove Verification')
                ->icon(Heroicon::OutlinedShieldExclamation)
                ->color('warning')
                ->visible(fn (): bool => (bool) $record()->is_verified_writer)
                ->requiresConfirmation()
                ->modalHeading('Remove Verified tier')
                ->schema([
                    Textarea::make('reason')
                        ->label('Reason')
                        ->rows(3)
                        ->maxLength(2000),
                ])
                ->action(fn (array $data) => WriterVerificationReview::revokeVerifiedTier(
                    $record(),
                    $data['reason'] ?? null,
                )),
            $after,
        );
    }

    private static function withAfter(Action $action, ?Closure $after): Action
    {
        if ($after) {
            $action->after($after);
        }

        return $action;
    }

    private static function displayName(Writer $writer): string
    {
        return $writer->display_name ?: $writer->user?->name ?: 'this writer';
    }

    private static function labelFor(string $status): string
    {
        return match ($status) {
            'under_review' => 'Under Review',
            'submitted'    => 'Submitted',
            'approved'     => 'Approved',
            'rejected'     => 'Rejected',
            'suspended'    => 'Suspended',
            'draft'        => 'Draft',
            default        => ucfirst(str_replace('_', ' ', $status)),
        };
    }
}
