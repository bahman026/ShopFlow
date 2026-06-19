<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\DiscountForEnum;
use App\Filament\Resources\DiscountResource\Pages\CreateDiscount;
use App\Filament\Resources\DiscountResource\Pages\EditDiscount;
use App\Filament\Resources\DiscountResource\Pages\ListDiscounts;
use App\Models\Discount;
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
                    ->relationship('variety', 'attribute_value')
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->required(),
                TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('priority')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_percent'),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                DateTimePicker::make('started_at'),
                DateTimePicker::make('ended_at'),
                TextInput::make('sold')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('max_sell')
                    ->numeric()
                    ->nullable(),
                TextInput::make('max_sell_by_user')
                    ->numeric()
                    ->nullable(),
                Select::make('is_for')
                    ->required()
                    ->options(DiscountForEnum::options())
                    ->default(DiscountForEnum::EVERYONE->value),
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
