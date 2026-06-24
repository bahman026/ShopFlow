<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use App\Enums\PageStatusEnum;
use App\Filament\Resources\PageResource\Pages\CreatePage;
use App\Filament\Resources\PageResource\Pages\EditPage;
use App\Filament\Resources\PageResource\Pages\ListPages;
use App\Models\Page as PageModel;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PageResource extends Resource
{
    protected static ?string $model = PageModel::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 6;

    public static function getNavigationGroup(): ?string
    {
        return trans('page.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return trans('page.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('page.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('heading')
                    ->label(trans('page.heading'))
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (string $operation, ?string $state, Set $set): void {
                        if ($operation === 'create') {
                            $set('slug', Str::slug((string) $state));
                        }
                    })
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('page.heading_hint')),
                TextInput::make('slug')
                    ->label(trans('page.slug'))
                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->maxLength(255)
                    ->unique(PageModel::class, 'slug', ignoreRecord: true)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('page.slug_hint')),
                TinyEditor::make('content')
                    ->label(trans('page.content'))
                    ->nullable()
                    ->columnSpanFull()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('page.content_hint')),
                TextInput::make('title')
                    ->label(trans('page.title'))
                    ->maxLength(255)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('page.title_hint')),
                TextInput::make('description')
                    ->label(trans('page.description'))
                    ->maxLength(255)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('page.description_hint')),
                Toggle::make('no_index')
                    ->label(trans('page.no_index'))
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('page.no_index_hint')),
                TextInput::make('canonical')
                    ->label(trans('page.canonical'))
                    ->maxLength(255)
                    ->url()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('page.canonical_hint')),
                Select::make('status')
                    ->label(trans('page.status'))
                    ->required()
                    ->live()
                    ->options(PageStatusEnum::options())
                    ->default(PageStatusEnum::PUBLISHED->value)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('page.status_hint')),
                DateTimePicker::make('published_at')
                    ->label(trans('page.published_at'))
                    ->nullable()
                    ->required(fn (Get $get): bool => (int) $get('status') === PageStatusEnum::SCHEDULED->value)
                    ->hidden(fn (Get $get): bool => (int) $get('status') !== PageStatusEnum::SCHEDULED->value)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('page.published_at_hint')),
                Fieldset::make(trans('page.image'))
                    ->relationship('image')
                    ->schema([
                        FileUpload::make('path')
                            ->label(trans('page.path'))
                            ->image()
                            ->nullable()
                            ->columnSpanFull(),
                        TextInput::make('alt_text')
                            ->label(trans('page.alt_text'))
                            ->nullable()
                            ->maxLength(255),
                    ])
                    ->mutateRelationshipDataBeforeSaveUsing(function (array $data, PageModel $record): array {
                        if (empty($data['path'])) {
                            $record->image?->delete();
                        }

                        return $data;
                    })
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('heading')
                    ->label(trans('page.heading'))
                    ->limit(30)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('slug')
                    ->label(trans('page.slug'))
                    ->limit(30)
                    ->wrap()
                    ->searchable(),
                IconColumn::make('no_index')
                    ->label(trans('page.no_index'))
                    ->boolean(),
                TextColumn::make('status')
                    ->label(trans('page.status'))
                    ->getStateUsing(fn (PageModel $record): string => $record->status->label())
                    ->color(fn (PageModel $record): string => $record->status->color())
                    ->sortable(),
                TextColumn::make('published_at')
                    ->label(trans('page.published_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(trans('page.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(trans('page.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPages::route('/'),
            'create' => CreatePage::route('/create'),
            'edit' => EditPage::route('/{record}/edit'),
        ];
    }
}
