<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\OrderSrcEnum;
use App\Enums\OrderStatusEnum;
use App\Filament\Resources\OrderResource\Pages\CreateOrder;
use App\Filament\Resources\OrderResource\Pages\EditOrder;
use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Filament\Resources\OrderResource\RelationManagers\OrderNotesRelationManager;
use App\Filament\Resources\OrderResource\RelationManagers\OrderShippingsRelationManager;
use App\Filament\Resources\OrderResource\RelationManagers\OrderVarietiesRelationManager;
use App\Models\Order;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shopping-bag';

    public static function getNavigationGroup(): ?string
    {
        return trans('order.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return trans('order.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('order.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make(trans('order.section_main'))
                    ->schema([
                        Select::make('user_id')
                            ->label(trans('order.user_id'))
                            ->relationship('user', 'email')
                            ->searchable()
                            ->preload()
                            ->native(false),
                        Select::make('coupon_id')
                            ->label(trans('order.coupon_id'))
                            ->relationship('coupon', 'code')
                            ->searchable()
                            ->preload()
                            ->native(false),
                        Select::make('status')
                            ->label(trans('order.status'))
                            ->required()
                            ->options(OrderStatusEnum::options())
                            ->default(OrderStatusEnum::PENDING->value),
                        Select::make('src')
                            ->label(trans('order.src'))
                            ->required()
                            ->options(OrderSrcEnum::options())
                            ->default(OrderSrcEnum::WEB->value),
                        Toggle::make('for_partner')
                            ->label(trans('order.for_partner')),
                    ]),
                Fieldset::make(trans('order.section_amounts'))
                    ->schema([
                        TextInput::make('total_products_price')
                            ->label(trans('order.total_products_price'))
                            ->required()
                            ->numeric()
                            ->default(0),
                        TextInput::make('discount')
                            ->label(trans('order.discount'))
                            ->required()
                            ->numeric()
                            ->default(0),
                        TextInput::make('coupon_discount')
                            ->label(trans('order.coupon_discount'))
                            ->required()
                            ->numeric()
                            ->default(0),
                        TextInput::make('tax')
                            ->label(trans('order.tax'))
                            ->required()
                            ->numeric()
                            ->default(0),
                        TextInput::make('shipping_cost')
                            ->label(trans('order.shipping_cost'))
                            ->required()
                            ->numeric()
                            ->default(0),
                        TextInput::make('total_price')
                            ->label(trans('order.total_price'))
                            ->required()
                            ->numeric()
                            ->default(0),
                    ]),
                Fieldset::make(trans('order.section_shipping'))
                    ->schema([
                        Select::make('shipping_line_id')
                            ->label(trans('order.shipping_line_id'))
                            ->relationship('shippingLine', 'name')
                            ->searchable()
                            ->preload()
                            ->native(false),
                        Select::make('shipping_method_id')
                            ->label(trans('order.shipping_method_id'))
                            ->relationship('shippingMethod', 'name')
                            ->searchable()
                            ->preload()
                            ->native(false),
                        TimePicker::make('receive_from')
                            ->label(trans('order.receive_from')),
                        TimePicker::make('receive_to')
                            ->label(trans('order.receive_to')),
                        Textarea::make('send_description')
                            ->label(trans('order.send_description'))
                            ->columnSpanFull(),
                    ]),
                Fieldset::make(trans('order.section_crm'))
                    ->schema([
                        TextInput::make('version')
                            ->label(trans('order.version'))
                            ->maxLength(255),
                        TextInput::make('accounting_id')
                            ->label(trans('order.accounting_id'))
                            ->numeric(),
                        TextInput::make('bijack_image_id')
                            ->label(trans('order.bijack_image_id'))
                            ->numeric(),
                        TextInput::make('bijack_description')
                            ->label(trans('order.bijack_description'))
                            ->maxLength(255),
                        Textarea::make('content')
                            ->label(trans('order.content'))
                            ->columnSpanFull(),
                    ]),
                Fieldset::make(trans('order.section_confirmation'))
                    ->schema([
                        Select::make('confirmed_id')
                            ->label(trans('order.confirmed_id'))
                            ->relationship('confirmer', 'email')
                            ->searchable()
                            ->preload()
                            ->native(false),
                        DateTimePicker::make('confirmed_at')
                            ->label(trans('order.confirmed_at')),
                        Textarea::make('confirm_description')
                            ->label(trans('order.confirm_description'))
                            ->columnSpanFull(),
                    ]),
                Fieldset::make(trans('order.section_collection'))
                    ->schema([
                        Select::make('collector_id')
                            ->label(trans('order.collector_id'))
                            ->relationship('collector', 'email')
                            ->searchable()
                            ->preload()
                            ->native(false),
                        DateTimePicker::make('collected_at')
                            ->label(trans('order.collected_at')),
                        DateTimePicker::make('collector_reminded_at')
                            ->label(trans('order.collector_reminded_at')),
                        Textarea::make('collector_description')
                            ->label(trans('order.collector_description'))
                            ->columnSpanFull(),
                    ]),
                Fieldset::make(trans('order.section_notification'))
                    ->schema([
                        Select::make('notifier_id')
                            ->label(trans('order.notifier_id'))
                            ->relationship('notifier', 'email')
                            ->searchable()
                            ->preload()
                            ->native(false),
                        DateTimePicker::make('notified_at')
                            ->label(trans('order.notified_at')),
                    ]),
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
                TextColumn::make('user.email')
                    ->label(trans('order.user_id'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(trans('order.status'))
                    ->badge()
                    ->getStateUsing(fn (Order $record): string => $record->status->label())
                    ->color(fn (Order $record): string => $record->status->color())
                    ->sortable(),
                TextColumn::make('src')
                    ->label(trans('order.src'))
                    ->badge()
                    ->getStateUsing(fn (Order $record): string => $record->src->label())
                    ->color(fn (Order $record): string => $record->src->color())
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('for_partner')
                    ->label(trans('order.for_partner'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total_products_price')
                    ->label(trans('order.total_products_price'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_price')
                    ->label(trans('order.total_price'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(trans('order.created_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(trans('order.updated_at'))
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
            OrderVarietiesRelationManager::class,
            OrderShippingsRelationManager::class,
            OrderNotesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }
}
