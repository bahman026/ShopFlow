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

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-ticket';

    public static function getNavigationGroup(): ?string
    {
        return trans('coupon.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return trans('coupon.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('coupon.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(trans('coupon.name'))
                    ->required()
                    ->maxLength(255)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('coupon.name_hint')),
                TextInput::make('code')
                    ->label(trans('coupon.code'))
                    ->required()
                    ->maxLength(255)
                    ->unique(Coupon::class, 'code', ignoreRecord: true)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('coupon.code_hint')),
                Toggle::make('is_percent')
                    ->label(trans('coupon.is_percent'))
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('coupon.is_percent_hint')),
                TextInput::make('amount')
                    ->label(trans('coupon.amount'))
                    ->required()
                    ->numeric()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('coupon.amount_hint')),
                TextInput::make('min_price')
                    ->label(trans('coupon.min_price'))
                    ->numeric()
                    ->nullable()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('coupon.min_price_hint')),
                TextInput::make('max_discount')
                    ->label(trans('coupon.max_discount'))
                    ->numeric()
                    ->nullable()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('coupon.max_discount_hint')),
                TextInput::make('total_used')
                    ->label(trans('coupon.total_used'))
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('coupon.total_used_hint')),
                TextInput::make('total_uses')
                    ->label(trans('coupon.total_uses'))
                    ->numeric()
                    ->nullable()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('coupon.total_uses_hint')),
                Toggle::make('shipping')
                    ->label(trans('coupon.shipping'))
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('coupon.shipping_hint')),
                Select::make('status')
                    ->label(trans('coupon.status'))
                    ->required()
                    ->options(CouponStatusEnum::options())
                    ->default(CouponStatusEnum::ACTIVE->value),
                Select::make('is_for')
                    ->label(trans('coupon.is_for'))
                    ->required()
                    ->options(CouponForEnum::options())
                    ->default(CouponForEnum::EVERYONE->value)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('coupon.is_for_hint')),
                DateTimePicker::make('started_at')
                    ->label(trans('coupon.started_at'))
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('coupon.started_at_hint')),
                DateTimePicker::make('expired_at')
                    ->label(trans('coupon.expired_at'))
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('coupon.expired_at_hint')),
                Select::make('user_id')
                    ->label(trans('coupon.user_id'))
                    ->relationship('user', 'email')
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('coupon.user_id_hint')),
                Select::make('user_creator_id')
                    ->label(trans('coupon.user_creator_id'))
                    ->relationship('userCreator', 'email')
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('coupon.user_creator_id_hint')),
                Select::make('products')
                    ->label(trans('coupon.products'))
                    ->relationship('products', 'heading')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('coupon.products_hint')),
                Select::make('varieties')
                    ->label(trans('coupon.varieties'))
                    ->relationship('varieties', 'attribute_value')
                    ->getOptionLabelFromRecordUsing(fn (Variety $record): string => implode(' — ', array_filter([
                        $record->product->heading,
                        $record->attribute_value ?? ('Variety #' . $record->id),
                    ])))
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('coupon.varieties_hint')),
                Select::make('categories')
                    ->label(trans('coupon.categories'))
                    ->relationship('categories', 'heading')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('coupon.categories_hint')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(trans('coupon.name'))
                    ->limit(30)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('code')
                    ->label(trans('coupon.code'))
                    ->searchable(),
                IconColumn::make('is_percent')
                    ->label(trans('coupon.is_percent'))
                    ->boolean(),
                TextColumn::make('amount')
                    ->label(trans('coupon.amount'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('min_price')
                    ->label(trans('coupon.min_price'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_discount')
                    ->label(trans('coupon.max_discount'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_used')
                    ->label(trans('coupon.total_used'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_uses')
                    ->label(trans('coupon.total_uses'))
                    ->numeric()
                    ->sortable(),
                IconColumn::make('shipping')
                    ->label(trans('coupon.shipping'))
                    ->boolean(),
                TextColumn::make('status')
                    ->label(trans('coupon.status'))
                    ->getStateUsing(fn (Coupon $record): string => $record->status->label())
                    ->color(fn (Coupon $record): string => $record->status->color())
                    ->sortable(),
                TextColumn::make('is_for')
                    ->label(trans('coupon.is_for'))
                    ->getStateUsing(fn (Coupon $record): string => $record->is_for->label())
                    ->color(fn (Coupon $record): string => $record->is_for->color())
                    ->sortable(),
                TextColumn::make('started_at')
                    ->label(trans('coupon.started_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('expired_at')
                    ->label(trans('coupon.expired_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(trans('coupon.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(trans('coupon.updated_at'))
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
