<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ShippingLineResource\Pages\CreateShippingLine;
use App\Filament\Resources\ShippingLineResource\Pages\EditShippingLine;
use App\Filament\Resources\ShippingLineResource\Pages\ListShippingLines;
use App\Models\ShippingLine;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShippingLineResource extends Resource
{
    protected static ?string $model = ShippingLine::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-truck';

    public static function getNavigationGroup(): ?string
    {
        return trans('shipping_line.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return trans('shipping_line.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('shipping_line.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(trans('shipping_line.name'))
                    ->required()
                    ->maxLength(255)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('shipping_line.name_hint')),
                TextInput::make('cost')
                    ->label(trans('shipping_line.cost'))
                    ->required()
                    ->numeric()
                    ->prefix('تومان')
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('shipping_line.cost_hint')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(trans('shipping_line.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('cost')
                    ->label(trans('shipping_line.cost'))
                    ->money()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(trans('shipping_line.created_at'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(trans('shipping_line.updated_at'))
                    ->dateTime()
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
            'index' => ListShippingLines::route('/'),
            'create' => CreateShippingLine::route('/create'),
            'edit' => EditShippingLine::route('/{record}/edit'),
        ];
    }
}
