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

    protected static string | \UnitEnum | null $navigationGroup = 'Content';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('menu_id')
                    ->relationship('menu', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('The menu this item belongs to. Changing it reloads the available parent options below.'),
                Select::make('parent_id')
                    ->label('Parent Item')
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
                    ->hintIconTooltip('Optional. Select a top-level item to nest this item under it. Only top-level items are shown to prevent deep nesting.'),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('The link text shown to the visitor, e.g. "About Us".'),
                TextInput::make('url')
                    ->nullable()
                    ->maxLength(255)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('The URL this item links to. Use a relative path (e.g. /about) for internal pages or a full URL for external links.'),
                TextInput::make('label')
                    ->nullable()
                    ->maxLength(255)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Optional secondary badge or tag shown beside the item name, e.g. "New" or "Sale".'),
                TextInput::make('order')
                    ->numeric()
                    ->default(0)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Display order within the menu or under the parent item. Lower numbers appear first.'),
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
                    ->label('Menu')
                    ->limit(20)
                    ->wrap()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('parent.name')
                    ->label('Parent')
                    ->limit(20)
                    ->placeholder('—'),
                TextColumn::make('name')
                    ->limit(30)
                    ->wrap()
                    ->searchable(),
                ImageColumn::make('image.path')
                    ->label('Image')
                    ->square(),
                TextColumn::make('url')
                    ->limit(30),
                TextColumn::make('order')
                    ->numeric()
                    ->sortable(),
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
            'index' => ListMenuItems::route('/'),
            'create' => CreateMenuItem::route('/create'),
            'edit' => EditMenuItem::route('/{record}/edit'),
        ];
    }
}
