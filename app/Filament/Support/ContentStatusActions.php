<?php

namespace App\Filament\Support;

use Closure;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

class ContentStatusActions
{
    /**
     * @param  Closure(): Model  $getRecord
     * @return array<int, Action|ActionGroup>
     */
    public static function forArticle(Closure $getRecord, ?Closure $after = null): array
    {
        return [
            self::approveArticleAction($getRecord, $after),
            self::action($getRecord, $after, 'publish', 'Publish', 'published', Heroicon::OutlinedRocketLaunch, 'success', fn (Model $record): bool => in_array($record->status, ['ready', 'scheduled'], true), extra: fn (): array => [
                'published_at' => now(),
                'approved_at'  => now(),
            ]),
            self::action($getRecord, $after, 'sendToReview', 'Send to Review', 'under_review', Heroicon::OutlinedClock, 'warning', fn (Model $record): bool => in_array($record->status, ['draft', 'rejected'], true)),
            self::rejectAction(
                $getRecord,
                $after,
                'rejected',
                'writer_notes',
                fn (Model $record): bool => ! in_array($record->status, ['rejected', 'archived', 'published'], true),
            ),
            ActionGroup::make([
                self::approveArticleMenuAction($getRecord, $after),
                self::action($getRecord, $after, 'publishMenu', 'Publish', 'published', Heroicon::OutlinedRocketLaunch, 'success', fn (Model $record): bool => in_array($record->status, ['ready', 'scheduled'], true), extra: fn (): array => [
                    'published_at' => now(),
                    'approved_at'  => now(),
                ]),
                self::action($getRecord, $after, 'sendToReviewMenu', 'Send to Review', 'under_review', Heroicon::OutlinedClock, 'warning', fn (Model $record): bool => in_array($record->status, ['draft', 'rejected'], true)),
                self::rejectAction(
                    $getRecord,
                    $after,
                    'rejected',
                    'writer_notes',
                    fn (Model $record): bool => ! in_array($record->status, ['rejected', 'archived', 'published'], true),
                )->name('rejectMenu'),
                self::action($getRecord, $after, 'archive', 'Archive', 'archived', Heroicon::OutlinedArchiveBox, 'gray', fn (Model $record): bool => $record->status !== 'archived'),
                self::action($getRecord, $after, 'restoreDraft', 'Restore to Draft', 'draft', Heroicon::OutlinedDocument, 'gray', fn (Model $record): bool => in_array($record->status, ['archived', 'rejected', 'published'], true)),
                self::action($getRecord, $after, 'schedule', 'Mark Scheduled', 'scheduled', Heroicon::OutlinedCalendar, 'info', fn (Model $record): bool => in_array($record->status, ['ready', 'under_review', 'review'], true)),
            ])
                ->label('Change Status')
                ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                ->color('gray')
                ->button()
                ->dropdownPlacement('bottom-end'),
        ];
    }

    /**
     * @param  Closure(): Model  $getRecord
     */
    private static function approveArticleAction(Closure $getRecord, ?Closure $after): Action
    {
        return self::withAfter(
            Action::make('approve')
                ->label('Approve')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->visible(fn (): bool => self::canApproveArticle($getRecord()))
                ->requiresConfirmation()
                ->modalHeading('Approve article')
                ->modalDescription(fn (): string => $getRecord()->status === 'ready'
                    ? 'Publish this approved article now?'
                    : 'Approve this article and mark it ready for publishing?')
                ->action(fn () => self::runArticleApproval($getRecord())),
            $after,
        );
    }

    /**
     * @param  Closure(): Model  $getRecord
     */
    private static function approveArticleMenuAction(Closure $getRecord, ?Closure $after): Action
    {
        return self::withAfter(
            Action::make('approveMenu')
                ->label('Approve')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->visible(fn (): bool => self::canApproveArticle($getRecord()))
                ->requiresConfirmation()
                ->action(fn () => self::runArticleApproval($getRecord())),
            $after,
        );
    }

    private static function canApproveArticle(Model $record): bool
    {
        return in_array($record->status, ['submitted', 'under_review', 'review', 'ready'], true);
    }

    private static function runArticleApproval(Model $record): void
    {
        if ($record->status === 'ready') {
            $record->update([
                'status'       => 'published',
                'published_at' => $record->published_at ?? now(),
                'approved_at'  => now(),
            ]);

            Notification::make()
                ->title('Article published')
                ->body('The article is now live.')
                ->success()
                ->send();

            return;
        }

        $record->update([
            'status'      => 'ready',
            'approved_at' => now(),
        ]);

        Notification::make()
            ->title('Article approved')
            ->body('Status is now: Ready for publishing')
            ->success()
            ->send();
    }

    /**
     * @param  Closure(): Model  $getRecord
     * @return array<int, Action|ActionGroup>
     */
    public static function forNews(Closure $getRecord, ?Closure $after = null): array
    {
        return self::publishableContentActions($getRecord, $after, usesUnderReview: true);
    }

    /**
     * @param  Closure(): Model  $getRecord
     * @return array<int, Action|ActionGroup>
     */
    public static function forOpinion(Closure $getRecord, ?Closure $after = null): array
    {
        return self::publishableContentActions($getRecord, $after, usesUnderReview: true);
    }

    /**
     * @param  Closure(): Model  $getRecord
     * @return array<int, Action|ActionGroup>
     */
    public static function forInterview(Closure $getRecord, ?Closure $after = null): array
    {
        return self::publishableContentActions($getRecord, $after, usesUnderReview: true);
    }

    /**
     * @param  Closure(): Model  $getRecord
     * @return array<int, Action|ActionGroup>
     */
    public static function forReport(Closure $getRecord, ?Closure $after = null): array
    {
        return self::publishableContentActions($getRecord, $after, usesUnderReview: false);
    }

    /**
     * @param  Closure(): Model  $getRecord
     * @return array<int, Action|ActionGroup>
     */
    public static function forMediaItem(Closure $getRecord, ?Closure $after = null): array
    {
        return self::publishableContentActions($getRecord, $after, usesUnderReview: false);
    }

    /**
     * @param  Closure(): Model  $getRecord
     * @return array<int, Action|ActionGroup>
     */
    public static function forComment(Closure $getRecord, ?Closure $after = null): array
    {
        return [
            self::action($getRecord, $after, 'approve', 'Approve', 'approved', Heroicon::OutlinedCheckCircle, 'success', fn (Model $record): bool => $record->status !== 'approved'),
            self::rejectAction($getRecord, $after, 'rejected'),
        ];
    }

    /**
     * @param  Closure(): Model  $getRecord
     * @return array<int, Action|ActionGroup>
     */
    public static function forContentSubmission(Closure $getRecord, ?Closure $after = null): array
    {
        return [
            self::action($getRecord, $after, 'approve', 'Approve', 'approved', Heroicon::OutlinedCheckCircle, 'success', fn (Model $record): bool => $record->status !== 'approved'),
            self::action($getRecord, $after, 'review', 'Mark Under Review', 'review', Heroicon::OutlinedClock, 'warning', fn (Model $record): bool => $record->status === 'pending'),
            self::rejectAction($getRecord, $after, 'rejected', 'reviewer_notes'),
        ];
    }

    /**
     * @param  Closure(): Model  $getRecord
     * @return array<int, Action|ActionGroup>
     */
    public static function forPayment(Closure $getRecord, ?Closure $after = null): array
    {
        return [
            self::action($getRecord, $after, 'markPaid', 'Mark as Paid', 'paid', Heroicon::OutlinedCheckCircle, 'success', fn (Model $record): bool => $record->status !== 'paid', extra: fn (): array => ['paid_at' => now()]),
            ActionGroup::make([
                self::action($getRecord, $after, 'markFailed', 'Mark as Failed', 'failed', Heroicon::OutlinedXCircle, 'danger', fn (Model $record): bool => $record->status !== 'failed'),
                self::action($getRecord, $after, 'markRefunded', 'Mark as Refunded', 'refunded', Heroicon::OutlinedArrowPath, 'warning', fn (Model $record): bool => $record->status !== 'refunded'),
                self::action($getRecord, $after, 'markPending', 'Mark as Pending', 'pending', Heroicon::OutlinedClock, 'gray', fn (Model $record): bool => $record->status !== 'pending'),
            ])
                ->label('Change Status')
                ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                ->color('gray')
                ->button()
                ->dropdownPlacement('bottom-end'),
        ];
    }

    /**
     * @param  Closure(): Model  $getRecord
     * @return array<int, Action|ActionGroup>
     */
    public static function forNewsletterSubscriber(Closure $getRecord, ?Closure $after = null): array
    {
        return [
            self::action($getRecord, $after, 'activate', 'Activate', 'active', Heroicon::OutlinedCheckCircle, 'success', fn (Model $record): bool => $record->status !== 'active'),
            self::action($getRecord, $after, 'unsubscribe', 'Unsubscribe', 'unsubscribed', Heroicon::OutlinedXCircle, 'warning', fn (Model $record): bool => $record->status !== 'unsubscribed'),
        ];
    }

    /**
     * @return array<int, Action|ActionGroup>
     */
    private static function publishableContentActions(Closure $getRecord, ?Closure $after, bool $usesUnderReview): array
    {
        $actions = [
            self::action($getRecord, $after, 'publish', 'Publish', 'published', Heroicon::OutlinedRocketLaunch, 'success', fn (Model $record): bool => $record->status !== 'published', extra: fn (): array => ['published_at' => now()]),
        ];

        if ($usesUnderReview) {
            $actions[] = self::action($getRecord, $after, 'sendToReview', 'Send to Review', 'under_review', Heroicon::OutlinedClock, 'warning', fn (Model $record): bool => in_array($record->status, ['draft', 'archived'], true));
        }

        $actions[] = ActionGroup::make([
            self::action($getRecord, $after, 'archive', 'Archive', 'archived', Heroicon::OutlinedArchiveBox, 'gray', fn (Model $record): bool => $record->status !== 'archived'),
            self::action($getRecord, $after, 'restoreDraft', 'Restore to Draft', 'draft', Heroicon::OutlinedDocument, 'gray', fn (Model $record): bool => in_array($record->status, ['archived', 'published'], true)),
        ])
            ->label('Change Status')
            ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
            ->color('gray')
            ->button()
            ->dropdownPlacement('bottom-end');

        return $actions;
    }

    /**
     * @param  Closure(Model): bool  $visible
     * @param  (Closure(): array<string, mixed>)|null  $extra
     */
    private static function action(
        Closure $getRecord,
        ?Closure $after,
        string $name,
        string $label,
        string $status,
        Heroicon $icon,
        string $color,
        Closure $visible,
        ?Closure $extra = null,
    ): Action {
        $action = Action::make($name)
            ->label($label)
            ->icon($icon)
            ->color($color)
            ->visible(fn (): bool => $visible($getRecord()))
            ->requiresConfirmation()
            ->action(function () use ($getRecord, $status, $extra, $label): void {
                $record = $getRecord();
                $data = ['status' => $status];

                if ($extra) {
                    $data = array_merge($data, $extra());
                }

                $record->update($data);

                Notification::make()
                    ->title($label)
                    ->body('Status is now: '.self::labelFor($status))
                    ->success()
                    ->send();
            });

        return self::withAfter($action, $after);
    }

    private static function rejectAction(
        Closure $getRecord,
        ?Closure $after,
        string $status,
        ?string $notesField = null,
        ?Closure $visible = null,
    ): Action {
        $action = Action::make('reject')
            ->label('Reject')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->visible(fn (): bool => $visible
                ? $visible($getRecord())
                : $getRecord()->status !== $status)
            ->requiresConfirmation()
            ->modalHeading('Reject')
            ->schema([
                Textarea::make('notes')
                    ->label('Notes')
                    ->rows(3)
                    ->maxLength(2000),
            ])
            ->action(function (array $data) use ($getRecord, $status, $notesField): void {
                $record = $getRecord();
                $update = ['status' => $status];

                if ($notesField && filled($data['notes'] ?? null)) {
                    $existing = $record->{$notesField};
                    $update[$notesField] = trim(($existing ? $existing."\n\n" : '').'Rejected: '.$data['notes']);
                }

                $record->update($update);

                Notification::make()
                    ->title('Rejected')
                    ->warning()
                    ->send();
            });

        return self::withAfter($action, $after);
    }

    private static function withAfter(Action $action, ?Closure $after): Action
    {
        if ($after) {
            $action->after($after);
        }

        return $action;
    }

    /**
     * @return array<int, Action>
     */
    public static function articleTableActions(): array
    {
        return [
            self::tableApproveArticleAction(),
            self::tableStatusAction('publish', 'Publish', 'published', 'success', Heroicon::OutlinedRocketLaunch, fn (Model $record): bool => in_array($record->status, ['ready', 'scheduled'], true), ['published_at' => 'now', 'approved_at' => 'now']),
            self::tableStatusAction('sendToReview', 'Review', 'under_review', 'warning', Heroicon::OutlinedClock, fn (Model $record): bool => in_array($record->status, ['draft', 'rejected'], true)),
        ];
    }

    private static function tableApproveArticleAction(): Action
    {
        return Action::make('approve')
            ->label('Approve')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->visible(fn (Model $record): bool => self::canApproveArticle($record))
            ->requiresConfirmation()
            ->action(fn (Model $record) => self::runArticleApproval($record));
    }

    /**
     * @return array<int, Action>
     */
    public static function newsTableActions(): array
    {
        return [
            self::tableStatusAction('publish', 'Publish', 'published', 'success', Heroicon::OutlinedRocketLaunch, fn (Model $record): bool => $record->status !== 'published', ['published_at' => 'now']),
            self::tableStatusAction('sendToReview', 'Review', 'under_review', 'warning', Heroicon::OutlinedClock, fn (Model $record): bool => $record->status === 'draft'),
        ];
    }

    /**
     * @return array<int, Action>
     */
    public static function opinionTableActions(): array
    {
        return self::newsTableActions();
    }

    /**
     * @return array<int, Action>
     */
    public static function commentTableActions(): array
    {
        return [
            self::tableStatusAction('approve', 'Approve', 'approved', 'success', Heroicon::OutlinedCheckCircle, fn (Model $record): bool => $record->status !== 'approved'),
            self::tableStatusAction('reject', 'Reject', 'rejected', 'danger', Heroicon::OutlinedXCircle, fn (Model $record): bool => $record->status !== 'rejected'),
        ];
    }

    /**
     * @return array<int, Action>
     */
    public static function contentSubmissionTableActions(): array
    {
        return [
            self::tableStatusAction('approve', 'Approve', 'approved', 'success', Heroicon::OutlinedCheckCircle, fn (Model $record): bool => $record->status !== 'approved'),
            self::tableStatusAction('reject', 'Reject', 'rejected', 'danger', Heroicon::OutlinedXCircle, fn (Model $record): bool => $record->status !== 'rejected'),
        ];
    }

    /**
     * @param  Closure(Model): bool  $visible
     * @param  array<string, string>  $extra
     */
    private static function tableStatusAction(
        string $name,
        string $label,
        string $status,
        string $color,
        Heroicon $icon,
        Closure $visible,
        array $extra = [],
    ): Action {
        return Action::make($name)
            ->label($label)
            ->icon($icon)
            ->color($color)
            ->visible($visible)
            ->requiresConfirmation()
            ->action(function (Model $record) use ($status, $label, $extra): void {
                $data = ['status' => $status];

                foreach ($extra as $field => $value) {
                    $data[$field] = $value === 'now' ? now() : $value;
                }

                $record->update($data);

                Notification::make()
                    ->title($label)
                    ->body('Status is now: '.self::labelFor($status))
                    ->success()
                    ->send();
            });
    }

    private static function labelFor(string $status): string
    {
        return match ($status) {
            'under_review' => 'Under Review',
            'published'    => 'Published',
            'approved'     => 'Approved',
            'rejected'     => 'Rejected',
            'archived'     => 'Archived',
            'draft'        => 'Draft',
            'ready'        => 'Ready',
            'scheduled'    => 'Scheduled',
            'submitted'    => 'Submitted',
            'review'       => 'Under Review',
            'pending'      => 'Pending',
            'paid'         => 'Paid',
            'failed'       => 'Failed',
            'refunded'     => 'Refunded',
            'active'       => 'Active',
            'unsubscribed' => 'Unsubscribed',
            default        => ucfirst(str_replace('_', ' ', $status)),
        };
    }
}
