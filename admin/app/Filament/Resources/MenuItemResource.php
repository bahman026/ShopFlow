<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\MenuItemResource\Pages\CreateMenuItem;
use App\Filament\Resources\MenuItemResource\Pages\EditMenuItem;
use App\Filament\Resources\MenuItemResource\Pages\ListMenuItems;
use App\Models\MenuItem;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MenuItemResource extends Resource
{
    protected static ?string $model = MenuItem::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): ?string
    {
        return trans('menu_item.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return trans('menu_item.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('menu_item.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('menu_id')
                    ->label(trans('menu_item.menu_id'))
                    ->relationship('menu', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('menu_item.menu_id_hint')),
                Select::make('parent_id')
                    ->label(trans('menu_item.parent_id'))
                    ->nullable()
                    ->options(function (Get $get, ?MenuItem $record): array {
                        $menuId = $get('menu_id') ?? $record?->menu_id;
                        if (! $menuId) {
                            return [];
                        }

                        return MenuItem::query()
                            ->where('menu_id', $menuId)
                            ->whereNull('parent_id')
                            ->when($record?->id, fn (Builder $q) => $q->where('id', '!=', $record->id))
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('menu_item.parent_id_hint')),
                TextInput::make('name')
                    ->label(trans('menu_item.name'))
                    ->required()
                    ->maxLength(255)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('menu_item.name_hint')),
                TextInput::make('url')
                    ->label(trans('menu_item.url'))
                    ->nullable()
                    ->maxLength(255)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('menu_item.url_hint')),
                TextInput::make('label')
                    ->label(trans('menu_item.label_field'))
                    ->nullable()
                    ->maxLength(255)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('menu_item.label_field_hint')),
                TextInput::make('order')
                    ->label(trans('menu_item.order'))
                    ->numeric()
                    ->default(0)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('menu_item.order_hint')),
                Fieldset::make(trans('menu_item.image'))
                    ->relationship('image')
                    ->schema([
                        FileUpload::make('path')
                            ->label(trans('menu_item.path'))
                            ->image()
                            ->nullable()
                            ->columnSpanFull(),
                        TextInput::make('alt_text')
                            ->label(trans('menu_item.alt_text'))
                            ->nullable()
                            ->maxLength(255),
                    ])
                    ->mutateRelationshipDataBeforeSaveUsing(function (array $data, MenuItem $record): array {
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
                TextColumn::make('menu.name')
                    ->label(trans('menu_item.menu'))
                    ->limit(20)
                    ->wrap()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('parent.name')
                    ->label(trans('menu_item.parent'))
                    ->limit(20)
                    ->placeholder('—'),
                TextColumn::make('name')
                    ->label(trans('menu_item.name'))
                    ->limit(30)
                    ->wrap()
                    ->searchable(),
                ImageColumn::make('image.path')
                    ->label(trans('menu_item.image'))
                    ->square(),
                TextColumn::make('url')
                    ->label(trans('menu_item.url'))
                    ->limit(30),
                TextColumn::make('order')
                    ->label(trans('menu_item.order'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(trans('menu_item.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(trans('menu_item.updated_at'))
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
            'index' => ListMenuItems::route('/'),
            'create' => CreateMenuItem::route('/create'),
            'edit' => EditMenuItem::route('/{record}/edit'),
        ];
    }
}
