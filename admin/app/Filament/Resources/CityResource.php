<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CityResource\Pages\CreateCity;
use App\Filament\Resources\CityResource\Pages\EditCity;
use App\Filament\Resources\CityResource\Pages\ListCities;
use App\Models\City;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CityResource extends Resource
{
    protected static ?string $model = City::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Address';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-map-pin';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('name')
                    ->required()
                    ->columnSpanFull(),
                Select::make('province_id')
                    ->relationship('province', 'name')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->sortable(),
                TextColumn::make('province.name')
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCities::route('/'),
            'create' => CreateCity::route('/create'),
            'edit' => EditCity::route('/{record}/edit'),
        ];
    }
}
