<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\OrderVarietyResource\Pages\CreateOrderVariety;
use App\Filament\Resources\OrderVarietyResource\Pages\EditOrderVariety;
use App\Filament\Resources\OrderVarietyResource\Pages\ListOrderVarieties;
use App\Models\OrderVariety;
use App\Models\Variety;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderVarietyResource extends Resource
{
    protected static ?string $model = OrderVariety::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-list-bullet';

    public static function getNavigationGroup(): ?string
    {
        return trans('order_variety.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return trans('order_variety.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('order_variety.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('order_id')
                    ->label(trans('order_variety.order_id'))
                    ->relationship('order', 'id')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->native(false),
                Select::make('product_id')
                    ->label(trans('order_variety.product_id'))
                    ->relationship('product', 'heading')
                    ->searchable()
                    ->preload()
                    ->native(false),
                Select::make('variety_id')
                    ->label(trans('order_variety.variety_id'))
                    ->relationship('variety', 'attribute_value')
                    ->getOptionLabelFromRecordUsing(fn (Variety $record): string => implode(' — ', array_filter([
                        $record->product->heading,
                        $record->attribute_value ?? ('Variety #' . $record->id),
                    ])))
                    ->searchable()
                    ->preload()
                    ->native(false),
                TextInput::make('sub_order_id')
                    ->label(trans('order_variety.sub_order_id'))
                    ->numeric(),
                TextInput::make('quantity')
                    ->label(trans('order_variety.quantity'))
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('gather_quantity')
                    ->label(trans('order_variety.gather_quantity'))
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('invoice_quantity')
                    ->label(trans('order_variety.invoice_quantity'))
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('price')
                    ->label(trans('order_variety.price'))
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('discount')
                    ->label(trans('order_variety.discount'))
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('coupon_discount')
                    ->label(trans('order_variety.coupon_discount'))
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('final_price')
                    ->label(trans('order_variety.final_price'))
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('order_id')
                    ->label(trans('order_variety.order_id'))
                    ->sortable(),
                TextColumn::make('product.heading')
                    ->label(trans('order_variety.product_id'))
                    ->limit(30)
                    ->searchable(),
                TextColumn::make('variety.attribute_value')
                    ->label(trans('order_variety.variety_id'))
                    ->toggleable(),
                TextColumn::make('quantity')
                    ->label(trans('order_variety.quantity'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('price')
                    ->label(trans('order_variety.price'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('discount')
                    ->label(trans('order_variety.discount'))
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('final_price')
                    ->label(trans('order_variety.final_price'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(trans('order_variety.created_at'))
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
            'index' => ListOrderVarieties::route('/'),
            'create' => CreateOrderVariety::route('/create'),
            'edit' => EditOrderVariety::route('/{record}/edit'),
        ];
    }
}
