<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\OrderShippingPaymentTypeEnum;
use App\Filament\Resources\OrderShippingResource\Pages\CreateOrderShipping;
use App\Filament\Resources\OrderShippingResource\Pages\EditOrderShipping;
use App\Filament\Resources\OrderShippingResource\Pages\ListOrderShippings;
use App\Models\OrderShipping;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderShippingResource extends Resource
{
    protected static ?string $model = OrderShipping::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-truck';

    public static function getNavigationGroup(): ?string
    {
        return trans('order_shipping.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return trans('order_shipping.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('order_shipping.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('order_id')
                    ->label(trans('order_shipping.order_id'))
                    ->relationship('order', 'id')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->native(false),
                Fieldset::make(trans('order_shipping.label'))
                    ->schema([
                        Select::make('payment_type')
                            ->label(trans('order_shipping.payment_type'))
                            ->options(OrderShippingPaymentTypeEnum::options()),
                        TextInput::make('confirm_code')
                            ->label(trans('order_shipping.confirm_code'))
                            ->maxLength(255),
                        TextInput::make('pack_count')
                            ->label(trans('order_shipping.pack_count'))
                            ->numeric(),
                        Toggle::make('cheque_is_require')
                            ->label(trans('order_shipping.cheque_is_require')),
                    ]),
                Fieldset::make(trans('order_shipping.pack_user'))
                    ->schema([
                        Select::make('checker_id')
                            ->label(trans('order_shipping.checker_id'))
                            ->relationship('checker', 'email')
                            ->searchable()
                            ->preload()
                            ->native(false),
                        Select::make('pack_user')
                            ->label(trans('order_shipping.pack_user'))
                            ->relationship('packer', 'email')
                            ->searchable()
                            ->preload()
                            ->native(false),
                        DateTimePicker::make('checked_at')
                            ->label(trans('order_shipping.checked_at')),
                        DateTimePicker::make('shipped_at')
                            ->label(trans('order_shipping.shipped_at')),
                    ]),
                Fieldset::make(trans('order_shipping.courier_id'))
                    ->schema([
                        Select::make('sender_id')
                            ->label(trans('order_shipping.sender_id'))
                            ->relationship('sender', 'email')
                            ->searchable()
                            ->preload()
                            ->native(false),
                        Select::make('courier_id')
                            ->label(trans('order_shipping.courier_id'))
                            ->relationship('courier', 'email')
                            ->searchable()
                            ->preload()
                            ->native(false),
                        Select::make('courier2_id')
                            ->label(trans('order_shipping.courier2_id'))
                            ->relationship('courier2', 'email')
                            ->searchable()
                            ->preload()
                            ->native(false),
                        DateTimePicker::make('courier_received_at')
                            ->label(trans('order_shipping.courier_received_at')),
                        DateTimePicker::make('courier_delivered_at')
                            ->label(trans('order_shipping.courier_delivered_at')),
                    ]),
                Textarea::make('description')
                    ->label(trans('order_shipping.description'))
                    ->columnSpanFull(),
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
                    ->label(trans('order_shipping.order_id'))
                    ->sortable(),
                TextColumn::make('pack_count')
                    ->label(trans('order_shipping.pack_count'))
                    ->numeric()
                    ->toggleable(),
                TextColumn::make('payment_type')
                    ->label(trans('order_shipping.payment_type'))
                    ->badge()
                    ->getStateUsing(fn (OrderShipping $record): ?string => $record->payment_type?->label())
                    ->color(fn (OrderShipping $record): string => $record->payment_type?->color() ?? 'gray'),
                IconColumn::make('cheque_is_require')
                    ->label(trans('order_shipping.cheque_is_require'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('shipped_at')
                    ->label(trans('order_shipping.shipped_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('courier_delivered_at')
                    ->label(trans('order_shipping.courier_delivered_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(trans('order_shipping.created_at'))
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
            'index' => ListOrderShippings::route('/'),
            'create' => CreateOrderShipping::route('/create'),
            'edit' => EditOrderShipping::route('/{record}/edit'),
        ];
    }
}
