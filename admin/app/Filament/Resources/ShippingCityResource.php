<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ShippingCityResource\Pages\CreateShippingCity;
use App\Filament\Resources\ShippingCityResource\Pages\EditShippingCity;
use App\Filament\Resources\ShippingCityResource\Pages\ListShippingCities;
use App\Models\ShippingCity;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShippingCityResource extends Resource
{
    protected static ?string $model = ShippingCity::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Logistics';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-map-pin';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('shipping_method_id')
                    ->relationship('shippingMethod', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->columnSpanFull()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('The shipping method this city config applies to.'),
                Select::make('province_id')
                    ->relationship('province', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->native(false)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Set a province for a province-wide rule. Leave empty if targeting a specific city.'),
                Select::make('city_id')
                    ->relationship('city', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->native(false)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Set a city for city-specific rules. City rules take priority over province rules. At least one of city or province must be set.'),
                TextInput::make('amount')
                    ->numeric()
                    ->nullable()
                    ->prefix('تومان')
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Shipping cost for this location. Leave empty for postpaid. Set 0 for free shipping.'),
                Toggle::make('pay_on_delivery')
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('When on, the customer pays at delivery — no online payment required.'),
                TextInput::make('sending_days')
                    ->nullable()
                    ->placeholder('e.g. 1,2,3,4,5')
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Days this method ships for this location (comma-separated day numbers). Leave empty for every day.'),
                TextInput::make('delay')
                    ->numeric()
                    ->nullable()
                    ->suffix('days')
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Extra delivery delay in days for this location (e.g. 1 for next-day).'),
                Textarea::make('description')
                    ->nullable()
                    ->rows(2)
                    ->columnSpanFull()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Human-readable delivery details shown to the customer (e.g. "Delivered within 72 hours").'),
                DateTimePicker::make('disable_from')
                    ->nullable()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Start of a period when this method is unavailable in this location.'),
                DateTimePicker::make('disable_to')
                    ->nullable()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('End of the unavailability period.'),
                Toggle::make('status')
                    ->default(true)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('When off, this entry is ignored at checkout.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('shippingMethod.name')
                    ->label('Method')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('province.name')
                    ->label('Province')
                    ->placeholder('—'),
                TextColumn::make('city.name')
                    ->label('City')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('amount')
                    ->money()
                    ->placeholder('Postpaid'),
                IconColumn::make('pay_on_delivery')
                    ->boolean(),
                TextColumn::make('delay')
                    ->suffix(' d')
                    ->placeholder('—'),
                IconColumn::make('status')
                    ->boolean(),
                TextColumn::make('created_at')
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
            'index' => ListShippingCities::route('/'),
            'create' => CreateShippingCity::route('/create'),
            'edit' => EditShippingCity::route('/{record}/edit'),
        ];
    }
}
