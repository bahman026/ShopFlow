<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\VarietyStatusEnum;
use App\Filament\Resources\VarietyResource\Pages;
use App\Models\Variety;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VarietyResource extends Resource
{
    protected static ?string $model = Variety::class;

    protected static ?string $navigationGroup = 'Product';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'heading')
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('attribute_value')
                    ->maxLength(255),
                Forms\Components\TextInput::make('color')
                    ->maxLength(255),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\TextInput::make('sale_price')
                    ->numeric(),
                Forms\Components\TextInput::make('inventory')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('has_stock')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->required()
                    ->options(VarietyStatusEnum::options())
                    ->default(VarietyStatusEnum::PUBLISHED->value),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.title')
                    ->numeric(),
                Tables\Columns\TextColumn::make('attribute_value'),
                Tables\Columns\TextColumn::make('color'),
                Tables\Columns\TextColumn::make('price')
                    ->money(),
                Tables\Columns\TextColumn::make('sale_price')
                    ->numeric(),
                Tables\Columns\TextColumn::make('inventory')
                    ->numeric(),
                Tables\Columns\IconColumn::make('has_stock')
                    ->boolean(),
                Tables\Columns\TextColumn::make('status')
                    ->getStateUsing(fn (Variety $record) => $record->status->label())
                    ->color(fn (Variety $record): string => $record->status->color())
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListVarieties::route('/'),
            'create' => Pages\CreateVariety::route('/create'),
            'edit' => Pages\EditVariety::route('/{record}/edit'),
        ];
    }
}
