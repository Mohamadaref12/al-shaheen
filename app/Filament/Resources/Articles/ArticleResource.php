<?php

namespace App\Filament\Resources\Articles;

use App\Filament\Concerns\HasTranslatedLabels;
use App\Filament\Concerns\SearchesTranslatableTitles;
use App\Filament\Resources\Articles\Pages\CreateArticle;
use App\Filament\Resources\Articles\Pages\EditArticle;
use App\Filament\Resources\Articles\Pages\ListArticles;
use App\Filament\Resources\Articles\Pages\ViewArticle;
use App\Filament\Resources\Articles\Schemas\ArticleForm;
use App\Filament\Resources\Articles\Tables\ArticlesTable;
use App\Models\Article;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ArticleResource extends Resource
{
    use HasTranslatedLabels;
    use SearchesTranslatableTitles;

    protected static ?string $model = Article::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static function translationKey(): string
    {
        return 'articles';
    }

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'display_title';

    /**
     * @var list<string>
     */
    public const AWAITING_APPROVAL_STATUSES = ['submitted', 'under_review', 'review', 'ready'];

    public static function getNavigationBadge(): ?string
    {
        $count = Article::query()
            ->whereIn('status', self::AWAITING_APPROVAL_STATUSES)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getRecordTitle(?Model $record): string | \Illuminate\Contracts\Support\Htmlable | null
    {
        return $record instanceof Article
            ? $record->display_title
            : parent::getRecordTitle($record);
    }

    public static function form(Schema $schema): Schema
    {
        return ArticleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ArticlesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListArticles::route('/'),
            'create' => CreateArticle::route('/create'),
            'view'   => ViewArticle::route('/{record}'),
            'edit'   => EditArticle::route('/{record}/edit'),
        ];
    }
}
