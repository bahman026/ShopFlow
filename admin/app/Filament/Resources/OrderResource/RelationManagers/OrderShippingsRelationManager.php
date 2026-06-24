<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Enums\OrderShippingPaymentTypeEnum;
use App\Models\OrderShipping;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderShippingsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderShippings';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('payment_type')
                    ->label(trans('order_shipping.payment_type'))
                    ->options(OrderShippingPaymentTypeEnum::options()),
                TextInput::make('pack_count')
                    ->label(trans('order_shipping.pack_count'))
                    ->numeric(),
                TextInput::make('confirm_code')
                    ->label(trans('order_shipping.confirm_code'))
                    ->maxLength(255),
                Toggle::make('cheque_is_require')
                    ->label(trans('order_shipping.cheque_is_require')),
                DateTimePicker::make('shipped_at')
                    ->label(trans('order_shipping.shipped_at')),
                DateTimePicker::make('courier_delivered_at')
                    ->label(trans('order_shipping.courier_delivered_at')),
                Textarea::make('description')
                    ->label(trans('order_shipping.description'))
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('pack_count')
                    ->label(trans('order_shipping.pack_count'))
                    ->numeric(),
                TextColumn::make('payment_type')
                    ->label(trans('order_shipping.payment_type'))
                    ->badge()
                    ->getStateUsing(fn (OrderShipping $record): ?string => $record->payment_type?->label())
                    ->color(fn (OrderShipping $record): string => $record->payment_type?->color() ?? 'gray'),
                TextColumn::make('shipped_at')
                    ->label(trans('order_shipping.shipped_at'))
                    ->dateTime(),
                TextColumn::make('courier_delivered_at')
                    ->label(trans('order_shipping.courier_delivered_at'))
                    ->dateTime(),
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
