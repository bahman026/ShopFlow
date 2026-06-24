<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\Variety;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderVarietiesRelationManager extends RelationManager
{
    protected static string $relationship = 'orderVarieties';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                TextInput::make('quantity')
                    ->label(trans('order_variety.quantity'))
                    ->required()
                    ->numeric()
                    ->default(1),
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('product.heading')
                    ->label(trans('order_variety.product_id'))
                    ->limit(30),
                TextColumn::make('variety.attribute_value')
                    ->label(trans('order_variety.variety_id')),
                TextColumn::make('quantity')
                    ->label(trans('order_variety.quantity'))
                    ->numeric(),
                TextColumn::make('price')
                    ->label(trans('order_variety.price'))
                    ->numeric(),
                TextColumn::make('final_price')
                    ->label(trans('order_variety.final_price'))
                    ->numeric(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
