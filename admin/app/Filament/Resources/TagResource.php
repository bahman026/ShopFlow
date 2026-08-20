<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use App\Enums\PermissionGroupEnum;
use App\Filament\Resources\TagResource\Pages\CreateTag;
use App\Filament\Resources\TagResource\Pages\EditTag;
use App\Filament\Resources\TagResource\Pages\ListTags;
use App\Models\Tag;
use App\Traits\AuthorizesWithPermissions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class TagResource extends Resource
{
    use AuthorizesWithPermissions;

    public static function permissionGroup(): PermissionGroupEnum
    {
        return PermissionGroupEnum::CATALOG;
    }

    protected static ?string $model = Tag::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-hashtag';

    public static function getNavigationGroup(): ?string
    {
        return trans('tag.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return trans('tag.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('tag.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make(trans('tag.section_main'))
                    ->schema([
                        TextInput::make('name')
                            ->label(trans('tag.name'))
                            ->required()
                            ->live(onBlur: true)
                            ->maxLength(255)
                            ->afterStateUpdated(function (string $operation, ?string $state, Set $set): void {
                                if ($operation === 'create') {
                                    $set('slug', Str::slug((string) $state));
                                }
                            }),
                        TextInput::make('slug')
                            ->label(trans('tag.slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(Tag::class, 'slug', ignoreRecord: true)
                            ->hintIcon('heroicon-o-information-circle')
                            ->hintIconTooltip(trans('tag.slug_hint')),
                        Select::make('category_id')
                            ->label(trans('tag.category_id'))
                            ->relationship('category', 'heading')
                            ->requiredWithout('attributes')
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->hintIcon('heroicon-o-information-circle')
                            ->hintIconTooltip(trans('tag.category_id_hint')),
                        Select::make('attributes')
                            ->label(trans('tag.attributes'))
                            ->relationship('attributes', 'value')
                            ->multiple()
                            ->requiredWithout('category_id')
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->hintIcon('heroicon-o-information-circle')
                            ->hintIconTooltip(trans('tag.attributes_hint')),
                    ]),
                Fieldset::make(trans('tag.section_home'))
                    ->schema([
                        Toggle::make('show_on_home')
                            ->label(trans('tag.show_on_home'))
                            ->live()
                            ->hintIcon('heroicon-o-information-circle')
                            ->hintIconTooltip(trans('tag.show_on_home_hint')),
                        // Featured tags have no position column — they always
                        // land in the same slot — so the guide is shown only
                        // once the toggle is on, to say where that slot is.
                        // UI only: never written to the model.
                        ViewField::make('position_guide')
                            ->label(trans('position_guide.label'))
                            ->helperText(trans('position_guide.hint'))
                            ->view('filament.forms.position-guide')
                            ->viewData([
                                'kind' => 'tags',
                                'selected' => 'home-tags',
                                'alpineExpression' => "'home-tags'",
                            ])
                            ->visible(fn (Get $get): bool => (bool) $get('show_on_home'))
                            ->dehydrated(false)
                            ->columnSpanFull(),
                        TextInput::make('home_order')
                            ->label(trans('tag.home_order'))
                            ->numeric()
                            ->default(0)
                            ->required(),
                        Fieldset::make('image')
                            ->label(trans('tag.image'))
                            ->relationship('image')
                            ->schema([
                                FileUpload::make('path')
                                    ->label(trans('tag.path'))
                                    ->image()
                                    ->nullable()
                                    ->columnSpanFull(),
                            ])
                            ->mutateRelationshipDataBeforeSaveUsing(function (array $data, Tag $record): array {
                                if ($data['path'] === null) {
                                    $record->image?->delete();
                                }

                                return $data;
                            })
                            ->columnSpanFull(),
                    ]),
                Fieldset::make(trans('tag.section_seo'))
                    ->schema([
                        TextInput::make('title')
                            ->label(trans('tag.title'))
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label(trans('tag.description'))
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('canonical')
                            ->label(trans('tag.canonical'))
                            ->maxLength(255),
                        Toggle::make('no_index')
                            ->label(trans('tag.no_index')),
                    ]),
                TinyEditor::make('content')
                    ->label(trans('tag.content'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label(trans('tag.name'))
                    ->searchable(),
                TextColumn::make('slug')
                    ->label(trans('tag.slug'))
                    ->searchable(),
                TextColumn::make('category.heading')
                    ->label(trans('tag.category_id'))
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('attributes.value')
                    ->label(trans('tag.attributes'))
                    ->badge()
                    ->placeholder('—'),
                IconColumn::make('show_on_home')
                    ->label(trans('tag.show_on_home'))
                    ->boolean(),
                IconColumn::make('no_index')
                    ->label(trans('tag.no_index'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(trans('tag.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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

    public static function getPages(): array
    {
        return [
            'index' => ListTags::route('/'),
            'create' => CreateTag::route('/create'),
            'edit' => EditTag::route('/{record}/edit'),
        ];
    }
}
