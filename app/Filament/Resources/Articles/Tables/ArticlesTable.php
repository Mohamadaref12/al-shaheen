<?php

namespace App\Filament\Resources\Articles\Tables;

use App\Filament\Resources\Articles\ArticleResource;
use App\Models\Article;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ArticlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount([
                'comments',
                'comments as pending_comments_count' => fn (Builder $q) => $q->where('status', 'pending'),
            ]))
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (Article $record): string => ArticleResource::getUrl('view', ['record' => $record]))
            ->columns([
                ImageColumn::make('featured_image')
                    ->label('')
                    ->disk('images')
                    ->circular()
                    ->imageHeight(44)
                    ->defaultImageUrl(fn (Article $record): string => 'https://ui-avatars.com/api/?name=' . urlencode(Str::limit($record->title, 1, '')) . '&background=28414e&color=fff&size=88'),

                TextColumn::make('title')
                    ->description(fn (Article $record): ?string => $record->subtitle
                        ?: ($record->excerpt ? Str::limit($record->excerpt, 70) : null))
                    ->weight(FontWeight::SemiBold)
                    ->searchable()
                    ->sortable()
                    ->limit(45)
                    ->tooltip(fn (Article $record): string => $record->title),

                TextColumn::make('author.name')
                    ->label('Author')
                    ->icon(Heroicon::OutlinedUser)
                    ->sortable()
                    ->searchable(),

                TextColumn::make('primaryCategory.name')
                    ->label('Category')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'published'    => 'Published',
                        'draft'        => 'Draft',
                        'archived'     => 'Archived',
                        'rejected'     => 'Rejected',
                        'scheduled'    => 'Scheduled',
                        'ready'        => 'Ready',
                        'submitted'    => 'Submitted',
                        'under_review', 'review' => 'Under Review',
                        default        => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->icon(fn (string $state) => match ($state) {
                        'published' => Heroicon::OutlinedCheckCircle,
                        'rejected'  => Heroicon::OutlinedXCircle,
                        'draft'     => Heroicon::OutlinedPencilSquare,
                        'archived'  => Heroicon::OutlinedArchiveBox,
                        default     => Heroicon::OutlinedClock,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'rejected'  => 'danger',
                        'draft'     => 'gray',
                        'archived'  => 'danger',
                        'scheduled' => 'info',
                        'ready'     => 'info',
                        default     => 'warning',
                    }),

                TextColumn::make('locale')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => strtoupper($state))
                    ->color(fn (string $state): string => $state === 'ar' ? 'warning' : 'info'),

                IconColumn::make('is_breaking')
                    ->label('Breaking')
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedBolt)
                    ->falseIcon(Heroicon::OutlinedMinus)
                    ->trueColor('danger')
                    ->falseColor('gray'),

                TextColumn::make('pending_comments_count')
                    ->label('Comments')
                    ->badge()
                    ->formatStateUsing(fn (int $state, Article $record): string => $state > 0
                        ? "{$state} pending"
                        : (string) $record->comments_count)
                    ->icon(fn (int $state) => $state > 0
                        ? Heroicon::OutlinedChatBubbleLeftEllipsis
                        : Heroicon::OutlinedChatBubbleLeft)
                    ->color(fn (int $state): string => $state > 0 ? 'warning' : 'gray'),

                TextColumn::make('views_count')
                    ->label('Views')
                    ->icon(Heroicon::OutlinedEye)
                    ->numeric()
                    ->sortable(),

                TextColumn::make('published_at')
                    ->label('Published')
                    ->since()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft'        => 'Draft',
                        'submitted'    => 'Submitted',
                        'under_review' => 'Under Review',
                        'review'       => 'Review',
                        'ready'        => 'Ready',
                        'scheduled'    => 'Scheduled',
                        'published'    => 'Published',
                        'rejected'     => 'Rejected',
                        'archived'     => 'Archived',
                    ]),

                SelectFilter::make('locale')
                    ->options(['ar' => 'Arabic', 'en' => 'English']),

                TernaryFilter::make('is_breaking')
                    ->label('Breaking'),

                TernaryFilter::make('has_pending_comments')
                    ->label('Pending Comments')
                    ->queries(
                        true: fn (Builder $query) => $query->whereHas('comments', fn (Builder $q) => $q->where('status', 'pending')),
                        false: fn (Builder $query) => $query->whereDoesntHave('comments', fn (Builder $q) => $q->where('status', 'pending')),
                    ),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Preview')
                    ->icon(Heroicon::OutlinedEye)
                    ->color('gray')
                    ->url(fn (Article $record): string => ArticleResource::getUrl('view', ['record' => $record])),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
