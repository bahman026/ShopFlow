<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\DiscountForEnum;
use App\Filament\Resources\DiscountResource\Pages\CreateDiscount;
use App\Filament\Resources\DiscountResource\Pages\EditDiscount;
use App\Filament\Resources\DiscountResource\Pages\ListDiscounts;
use App\Models\Discount;
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

class DiscountResource extends Resource
{
    protected static ?string $model = Discount::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Promotions';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-receipt-percent';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('variety_id')
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('The specific variety this discount applies to. Label shows: Product — Variety.')
                    ->relationship('variety', 'attribute_value')
                    ->getOptionLabelFromRecordUsing(fn (Variety $record): string => implode(' — ', array_filter([
                        $record->product->heading,
                        $record->attribute_value ?? ('Variety #' . $record->id),
                    ])))
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->required(),
                TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Minimum quantity the customer must purchase for this discount to apply.'),
                TextInput::make('priority')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('When multiple discounts qualify, the one with the highest priority wins.'),
                Toggle::make('is_percent')
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('When on, the amount is treated as a percentage (e.g. 20 = 20% off). When off, it is a fixed amount in Tomans.'),
                TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('The discount value. Interpreted as percent or fixed amount based on the toggle above.'),
                DateTimePicker::make('started_at')
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('When the discount becomes active. Leave empty to start immediately.'),
                DateTimePicker::make('ended_at')
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('When the discount expires. Leave empty for no expiry.'),
                TextInput::make('sold')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Number of times this discount has already been used. Usually managed automatically.'),
                TextInput::make('max_sell')
                    ->numeric()
                    ->nullable()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Total usage cap across all customers. Leave empty for unlimited.'),
                TextInput::make('max_sell_by_user')
                    ->numeric()
                    ->nullable()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Maximum times a single user can use this discount. Leave empty for unlimited.'),
                Select::make('is_for')
                    ->required()
                    ->options(DiscountForEnum::options())
                    ->default(DiscountForEnum::EVERYONE->value)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Restricts who can benefit from this discount: everyone, registered users only, or partners only.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('variety.attribute_value')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('priority')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_percent')
                    ->boolean(),
                TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('started_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ended_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('sold')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_sell')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_sell_by_user')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('is_for')
                    ->getStateUsing(fn (Discount $record): string => $record->is_for->label())
                    ->color(fn (Discount $record): string => $record->is_for->color())
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
            'index' => ListDiscounts::route('/'),
            'create' => CreateDiscount::route('/create'),
            'edit' => EditDiscount::route('/{record}/edit'),
        ];
    }
}
