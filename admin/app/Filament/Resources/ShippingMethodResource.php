<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\ShippingMethodForEnum;
use App\Filament\Resources\ShippingMethodResource\Pages\CreateShippingMethod;
use App\Filament\Resources\ShippingMethodResource\Pages\EditShippingMethod;
use App\Filament\Resources\ShippingMethodResource\Pages\ListShippingMethods;
use App\Models\ShippingMethod;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShippingMethodResource extends Resource
{
    protected static ?string $model = ShippingMethod::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return trans('shipping_method.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return trans('shipping_method.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('shipping_method.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('shipping_line_id')
                    ->label(trans('shipping_method.shipping_line_id'))
                    ->relationship('shippingLine', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('shipping_method.shipping_line_id_hint')),
                TextInput::make('name')
                    ->label(trans('shipping_method.name'))
                    ->required()
                    ->maxLength(255)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('shipping_method.name_hint')),
                TextInput::make('type')
                    ->label(trans('shipping_method.type'))
                    ->nullable()
                    ->maxLength(100)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('shipping_method.type_hint')),
                TextInput::make('min_count')
                    ->label(trans('shipping_method.min_count'))
                    ->numeric()
                    ->nullable()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('shipping_method.min_count_hint')),
                TextInput::make('min_amount')
                    ->label(trans('shipping_method.min_amount'))
                    ->numeric()
                    ->nullable()
                    ->prefix('تومان')
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('shipping_method.min_amount_hint')),
                Select::make('for')
                    ->label(trans('shipping_method.for'))
                    ->required()
                    ->options(ShippingMethodForEnum::options())
                    ->default(ShippingMethodForEnum::CUSTOMER->value)
                    ->native(false)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('shipping_method.for_hint')),
                DateTimePicker::make('disable_from')
                    ->label(trans('shipping_method.disable_from'))
                    ->nullable()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('shipping_method.disable_from_hint')),
                DateTimePicker::make('disable_to')
                    ->label(trans('shipping_method.disable_to'))
                    ->nullable()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('shipping_method.disable_to_hint')),
                Toggle::make('status')
                    ->label(trans('shipping_method.status'))
                    ->default(true)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('shipping_method.status_hint')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('shippingLine.name')
                    ->label(trans('shipping_method.carrier'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label(trans('shipping_method.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(trans('shipping_method.type'))
                    ->placeholder('—'),
                TextColumn::make('for')
                    ->label(trans('shipping_method.for'))
                    ->getStateUsing(fn (ShippingMethod $record): string => $record->for->label())
                    ->color(fn (ShippingMethod $record): string => $record->for->color()),
                TextColumn::make('min_amount')
                    ->label(trans('shipping_method.min_amount'))
                    ->money()
                    ->placeholder(trans('shipping_method.no_minimum')),
                IconColumn::make('status')
                    ->label(trans('shipping_method.status'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(trans('shipping_method.created_at'))
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
            'index' => ListShippingMethods::route('/'),
            'create' => CreateShippingMethod::route('/create'),
            'edit' => EditShippingMethod::route('/{record}/edit'),
        ];
    }
}
