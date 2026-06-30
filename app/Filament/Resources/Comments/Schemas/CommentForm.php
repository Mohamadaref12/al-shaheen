<?php

namespace App\Filament\Resources\Comments\Schemas;

use App\Models\Article;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CommentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Comment')
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required(),

                        Select::make('article_id')
                            ->label('Article')
                            ->relationship(
                                name: 'article',
                                titleAttribute: 'id',
                                modifyQueryUsing: fn ($query) => $query->with('translations'),
                            )
                            ->getOptionLabelFromRecordUsing(fn (Article $record): string => $record->display_title)
                            ->searchable()
                            ->required(),

                        Select::make('status')
                            ->options([
                                'pending'  => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->required()
                            ->default('pending'),

                        Textarea::make('body')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),
            ])->columns(1);
    }
}
