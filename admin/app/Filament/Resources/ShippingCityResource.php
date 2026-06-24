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

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-map-pin';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return trans('shipping_city.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return trans('shipping_city.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('shipping_city.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('shipping_method_id')
                    ->label(trans('shipping_city.shipping_method_id'))
                    ->relationship('shippingMethod', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->columnSpanFull()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('shipping_city.shipping_method_id_hint')),
                Select::make('province_id')
                    ->label(trans('shipping_city.province_id'))
                    ->relationship('province', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->native(false)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('shipping_city.province_id_hint')),
                Select::make('city_id')
                    ->label(trans('shipping_city.city_id'))
                    ->relationship('city', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->native(false)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('shipping_city.city_id_hint')),
                TextInput::make('amount')
                    ->label(trans('shipping_city.amount'))
                    ->numeric()
                    ->nullable()
                    ->prefix('تومان')
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('shipping_city.amount_hint')),
                Toggle::make('pay_on_delivery')
                    ->label(trans('shipping_city.pay_on_delivery'))
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('shipping_city.pay_on_delivery_hint')),
                TextInput::make('sending_days')
                    ->label(trans('shipping_city.sending_days'))
                    ->nullable()
                    ->placeholder('e.g. 1,2,3,4,5')
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('shipping_city.sending_days_hint')),
                TextInput::make('delay')
                    ->label(trans('shipping_city.delay'))
                    ->numeric()
                    ->nullable()
                    ->suffix(trans('shipping_city.delay_suffix'))
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('shipping_city.delay_hint')),
                Textarea::make('description')
                    ->label(trans('shipping_city.description'))
                    ->nullable()
                    ->rows(2)
                    ->columnSpanFull()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('shipping_city.description_hint')),
                DateTimePicker::make('disable_from')
                    ->label(trans('shipping_city.disable_from'))
                    ->nullable()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('shipping_city.disable_from_hint')),
                DateTimePicker::make('disable_to')
                    ->label(trans('shipping_city.disable_to'))
                    ->nullable()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('shipping_city.disable_to_hint')),
                Toggle::make('status')
                    ->label(trans('shipping_city.status'))
                    ->default(true)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('shipping_city.status_hint')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('shippingMethod.name')
                    ->label(trans('shipping_city.method'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('province.name')
                    ->label(trans('shipping_city.province'))
                    ->placeholder('—'),
                TextColumn::make('city.name')
                    ->label(trans('shipping_city.city'))
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('amount')
                    ->label(trans('shipping_city.amount'))
                    ->money()
                    ->placeholder(trans('shipping_city.postpaid')),
                IconColumn::make('pay_on_delivery')
                    ->label(trans('shipping_city.pay_on_delivery'))
                    ->boolean(),
                TextColumn::make('delay')
                    ->label(trans('shipping_city.delay'))
                    ->suffix(' d')
                    ->placeholder('—'),
                IconColumn::make('status')
                    ->label(trans('shipping_city.status'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(trans('shipping_city.created_at'))
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
