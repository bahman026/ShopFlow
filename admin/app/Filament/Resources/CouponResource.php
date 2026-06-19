<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\CouponForEnum;
use App\Enums\CouponStatusEnum;
use App\Filament\Resources\CouponResource\Pages\CreateCoupon;
use App\Filament\Resources\CouponResource\Pages\EditCoupon;
use App\Filament\Resources\CouponResource\Pages\ListCoupons;
use App\Models\Coupon;
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

    protected static string | \UnitEnum | null $navigationGroup = 'Product';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-ticket';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('code')
                    ->required()
                    ->maxLength(255)
                    ->unique(Coupon::class, 'code', ignoreRecord: true),
                Toggle::make('is_percent'),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                TextInput::make('min_price')
                    ->numeric()
                    ->nullable(),
                TextInput::make('max_discount')
                    ->numeric()
                    ->nullable(),
                TextInput::make('total_used')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_uses')
                    ->numeric()
                    ->nullable(),
                Toggle::make('shipping'),
                Select::make('status')
                    ->required()
                    ->options(CouponStatusEnum::options())
                    ->default(CouponStatusEnum::ACTIVE->value),
                Select::make('is_for')
                    ->required()
                    ->options(CouponForEnum::options())
                    ->default(CouponForEnum::EVERYONE->value),
                DateTimePicker::make('started_at'),
                DateTimePicker::make('expired_at'),
                Select::make('user_id')
                    ->relationship('user', 'email')
                    ->searchable()
                    ->preload()
                    ->native(false),
                Select::make('user_creator_id')
                    ->relationship('userCreator', 'email')
                    ->searchable()
                    ->preload()
                    ->native(false),
                Select::make('seller_creator_id')
                    ->relationship('sellerCreator', 'email')
                    ->searchable()
                    ->preload()
                    ->native(false),
                Select::make('products')
                    ->relationship('products', 'heading')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Select::make('varieties')
                    ->relationship('varieties', 'attribute_value')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Select::make('categories')
                    ->relationship('categories', 'heading')
                    ->multiple()
                    ->searchable()
                    ->preload(),
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
