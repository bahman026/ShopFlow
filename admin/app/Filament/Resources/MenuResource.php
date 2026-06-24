<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\MenuResource\Pages\CreateMenu;
use App\Filament\Resources\MenuResource\Pages\EditMenu;
use App\Filament\Resources\MenuResource\Pages\ListMenus;
use App\Models\Menu;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-bars-3';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return trans('menu.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return trans('menu.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('menu.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(trans('menu.name'))
                    ->required()
                    ->maxLength(255)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('menu.name_hint')),
                TextInput::make('position')
                    ->label(trans('menu.position'))
                    ->required()
                    ->maxLength(255)
                    ->unique(Menu::class, 'position', ignoreRecord: true)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('menu.position_hint')),
                Toggle::make('status')
                    ->label(trans('menu.status'))
                    ->default(true)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('menu.status_hint')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(trans('menu.name'))
                    ->limit(30)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('position')
                    ->label(trans('menu.position'))
                    ->searchable(),
                TextColumn::make('menu_items_count')
                    ->label(trans('menu.items_count'))
                    ->counts('menuItems')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('status')
                    ->label(trans('menu.status'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(trans('menu.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(trans('menu.updated_at'))
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
            'index' => ListMenus::route('/'),
            'create' => CreateMenu::route('/create'),
            'edit' => EditMenu::route('/{record}/edit'),
        ];
    }
}
