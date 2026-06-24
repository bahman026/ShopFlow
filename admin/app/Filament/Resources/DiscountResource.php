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

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-receipt-percent';

    public static function getNavigationGroup(): ?string
    {
        return trans('discount.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return trans('discount.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('discount.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('variety_id')
                    ->label(trans('discount.variety_id'))
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('discount.variety_id_hint'))
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
                    ->label(trans('discount.quantity'))
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('discount.quantity_hint')),
                TextInput::make('priority')
                    ->label(trans('discount.priority'))
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('discount.priority_hint')),
                Toggle::make('is_percent')
                    ->label(trans('discount.is_percent'))
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('discount.is_percent_hint')),
                TextInput::make('amount')
                    ->label(trans('discount.amount'))
                    ->required()
                    ->numeric()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('discount.amount_hint')),
                DateTimePicker::make('started_at')
                    ->label(trans('discount.started_at'))
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('discount.started_at_hint')),
                DateTimePicker::make('ended_at')
                    ->label(trans('discount.ended_at'))
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('discount.ended_at_hint')),
                TextInput::make('sold')
                    ->label(trans('discount.sold'))
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('discount.sold_hint')),
                TextInput::make('max_sell')
                    ->label(trans('discount.max_sell'))
                    ->numeric()
                    ->nullable()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('discount.max_sell_hint')),
                TextInput::make('max_sell_by_user')
                    ->label(trans('discount.max_sell_by_user'))
                    ->numeric()
                    ->nullable()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('discount.max_sell_by_user_hint')),
                Select::make('is_for')
                    ->label(trans('discount.is_for'))
                    ->required()
                    ->options(DiscountForEnum::options())
                    ->default(DiscountForEnum::EVERYONE->value)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('discount.is_for_hint')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('variety.attribute_value')
                    ->label(trans('discount.variety_value'))
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label(trans('discount.quantity'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('priority')
                    ->label(trans('discount.priority'))
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_percent')
                    ->label(trans('discount.is_percent'))
                    ->boolean(),
                TextColumn::make('amount')
                    ->label(trans('discount.amount'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('started_at')
                    ->label(trans('discount.started_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ended_at')
                    ->label(trans('discount.ended_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('sold')
                    ->label(trans('discount.sold'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_sell')
                    ->label(trans('discount.max_sell'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_sell_by_user')
                    ->label(trans('discount.max_sell_by_user'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('is_for')
                    ->label(trans('discount.is_for'))
                    ->getStateUsing(fn (Discount $record): string => $record->is_for->label())
                    ->color(fn (Discount $record): string => $record->is_for->color())
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(trans('discount.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(trans('discount.updated_at'))
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
