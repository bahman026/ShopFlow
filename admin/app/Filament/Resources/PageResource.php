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

    protected static string | \UnitEnum | null $navigationGroup = 'Content';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('heading')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (string $operation, ?string $state, Set $set): void {
                        if ($operation === 'create') {
                            $set('slug', Str::slug((string) $state));
                        }
                    })
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('The page title shown to visitors, e.g. "About Us".'),
                TextInput::make('slug')
                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->maxLength(255)
                    ->unique(PageModel::class, 'slug', ignoreRecord: true)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Auto-generated from the heading. Used as the URL path, e.g. /about-us.'),
                TinyEditor::make('content')
                    ->nullable()
                    ->columnSpanFull()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('The main body content of the page, edited with the rich text editor.'),
                TextInput::make('title')
                    ->maxLength(255)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('SEO page title shown in the browser tab and search results. Leave blank to use the heading.'),
                TextInput::make('description')
                    ->maxLength(255)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Meta description shown in search engine results. Aim for 150-160 characters.'),
                Toggle::make('no_index')
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('When enabled, search engines are instructed not to index this page.'),
                TextInput::make('canonical')
                    ->maxLength(255)
                    ->url()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Canonical URL to prevent duplicate content issues. Leave blank unless this page should point to another URL.'),
                Select::make('status')
                    ->required()
                    ->live()
                    ->options(PageStatusEnum::options())
                    ->default(PageStatusEnum::PUBLISHED->value)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Published: visible on the frontend. Draft: hidden. Scheduled: published on the date set below. Deleted: removed from listings.'),
                DateTimePicker::make('published_at')
                    ->nullable()
                    ->required(fn (Get $get): bool => (int) $get('status') === PageStatusEnum::SCHEDULED->value)
                    ->hidden(fn (Get $get): bool => (int) $get('status') !== PageStatusEnum::SCHEDULED->value)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('The date and time this page will be made public. Only applies when status is Scheduled.'),
                Fieldset::make('Image')
                    ->relationship('image')
                    ->schema([
                        FileUpload::make('path')
                            ->image()
                            ->nullable()
                            ->columnSpanFull(),
                        TextInput::make('alt_text')
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
                    ->limit(30)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('slug')
                    ->limit(30)
                    ->wrap()
                    ->searchable(),
                IconColumn::make('no_index')
                    ->boolean(),
                TextColumn::make('status')
                    ->getStateUsing(fn (PageModel $record): string => $record->status->label())
                    ->color(fn (PageModel $record): string => $record->status->color())
                    ->sortable(),
                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
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
