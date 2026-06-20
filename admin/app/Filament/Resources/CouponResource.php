<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\CouponForEnum;
use App\Enums\CouponStatusEnum;
use App\Filament\Resources\CouponResource\Pages\CreateCoupon;
use App\Filament\Resources\CouponResource\Pages\EditCoupon;
use App\Filament\Resources\CouponResource\Pages\ListCoupons;
use App\Models\Coupon;
use App\Models\Variety;
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

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Promotions';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-ticket';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Internal name for this coupon. Not shown to customers.'),
                TextInput::make('code')
                    ->required()
                    ->maxLength(255)
                    ->unique(Coupon::class, 'code', ignoreRecord: true)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('The code the customer enters at checkout. Must be unique.'),
                Toggle::make('is_percent')
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('When on, the amount is a percentage (e.g. 20 = 20% off). When off, it is a fixed amount in Tomans.'),
                TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Discount value. Interpreted as percent or fixed amount based on the toggle above.'),
                TextInput::make('min_price')
                    ->numeric()
                    ->nullable()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Minimum cart total required to use this coupon. Leave empty for no minimum.'),
                TextInput::make('max_discount')
                    ->numeric()
                    ->nullable()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Maximum discount amount even if the percentage would give more. Leave empty for no cap.'),
                TextInput::make('total_used')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Total times this coupon has been used. Usually managed automatically.'),
                TextInput::make('total_uses')
                    ->numeric()
                    ->nullable()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Maximum total uses across all customers. Leave empty for unlimited.'),
                Toggle::make('shipping')
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('When on, this coupon also grants free shipping.'),
                Select::make('status')
                    ->required()
                    ->options(CouponStatusEnum::options())
                    ->default(CouponStatusEnum::ACTIVE->value),
                Select::make('is_for')
                    ->required()
                    ->options(CouponForEnum::options())
                    ->default(CouponForEnum::EVERYONE->value)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Restricts who can use this coupon: everyone, registered users only, or partners only.'),
                DateTimePicker::make('started_at')
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('When the coupon becomes valid. Leave empty to activate immediately.'),
                DateTimePicker::make('expired_at')
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('When the coupon expires. Leave empty for no expiry.'),
                Select::make('user_id')
                    ->relationship('user', 'email')
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Lock this coupon to one specific user. Leave empty for any user.'),
                Select::make('user_creator_id')
                    ->relationship('userCreator', 'email')
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('The admin user who created this coupon.'),
                Select::make('products')
                    ->relationship('products', 'heading')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Scope this coupon to specific products only. Leave empty to apply to all products.'),
                Select::make('varieties')
                    ->relationship('varieties', 'attribute_value')
                    ->getOptionLabelFromRecordUsing(fn (Variety $record): string => implode(' — ', array_filter([
                        $record->product->heading,
                        $record->attribute_value ?? ('Variety #' . $record->id),
                    ])))
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Scope this coupon to specific varieties only. Leave empty to apply to all varieties.'),
                Select::make('categories')
                    ->relationship('categories', 'heading')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Scope this coupon to specific categories only. Leave empty to apply to all categories.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->limit(30)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('code')
                    ->searchable(),
                IconColumn::make('is_percent')
                    ->boolean(),
                TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('min_price')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_discount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_used')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_uses')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('shipping')
                    ->boolean(),
                TextColumn::make('status')
                    ->getStateUsing(fn (Coupon $record): string => $record->status->label())
                    ->color(fn (Coupon $record): string => $record->status->color())
                    ->sortable(),
                TextColumn::make('is_for')
                    ->getStateUsing(fn (Coupon $record): string => $record->is_for->label())
                    ->color(fn (Coupon $record): string => $record->is_for->color())
                    ->sortable(),
                TextColumn::make('started_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('expired_at')
                    ->dateTime()
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
            'index' => ListCoupons::route('/'),
            'create' => CreateCoupon::route('/create'),
            'edit' => EditCoupon::route('/{record}/edit'),
        ];
    }
}
