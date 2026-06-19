<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\VarietyStatusEnum;
use App\Filament\Resources\VarietyResource\Pages\CreateVariety;
use App\Filament\Resources\VarietyResource\Pages\EditVariety;
use App\Filament\Resources\VarietyResource\Pages\ListVarieties;
use App\Models\Variety;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VarietyResource extends Resource
{
    protected static ?string $model = Variety::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Product';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->relationship('product', 'heading')
                    ->searchable()
                    ->required(),
                TextInput::make('attribute_value')
                    ->maxLength(255),
                TextInput::make('color')
                    ->maxLength(255),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('sale_price')
                    ->numeric(),
                TextInput::make('inventory')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('has_stock')
                    ->required(),
                Select::make('status')
                    ->required()
                    ->options(VarietyStatusEnum::options())
                    ->default(VarietyStatusEnum::PUBLISHED->value),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.title')
                    ->numeric(),
                TextColumn::make('attribute_value'),
                TextColumn::make('color'),
                TextColumn::make('price')
                    ->money(),
                TextColumn::make('sale_price')
                    ->numeric(),
                TextColumn::make('inventory')
                    ->numeric(),
                IconColumn::make('has_stock')
                    ->boolean(),
                TextColumn::make('status')
                    ->getStateUsing(fn (Variety $record) => $record->status->label())
                    ->color(fn (Variety $record): string => $record->status->color())
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
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
            'index' => ListVarieties::route('/'),
            'create' => CreateVariety::route('/create'),
            'edit' => EditVariety::route('/{record}/edit'),
        ];
    }
}
