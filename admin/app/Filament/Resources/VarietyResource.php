<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\VarietyStatusEnum;
use App\Filament\Resources\VarietyResource\Pages\CreateVariety;
use App\Filament\Resources\VarietyResource\Pages\EditVariety;
use App\Filament\Resources\VarietyResource\Pages\ListVarieties;
use App\Models\Attribute;
use App\Models\Product;
use App\Models\Variety;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VarietyResource extends Resource
{
    protected static ?string $model = Variety::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shopping-bag';

    public static function getNavigationGroup(): ?string
    {
        return trans('variety.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return trans('variety.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('variety.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->label(trans('variety.product_id'))
                    ->relationship('product', 'heading')
                    ->searchable()
                    ->live()
                    ->required()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('variety.product_id_hint')),
                Select::make('attribute_id')
                    ->label(trans('variety.attribute_id'))
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('variety.attribute_id_hint'))
                    ->options(function (Get $get, ?Variety $record): array {
                        $productId = $get('product_id') ?? $record?->product_id;
                        if (! $productId) {
                            return [];
                        }
                        $product = Product::find($productId);
                        if (! $product?->attribute_group_id) {
                            return [];
                        }

                        return Attribute::query()
                            ->where('attribute_group_id', $product->attribute_group_id)
                            ->pluck('value', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->nullable()
                    ->helperText(trans('variety.attribute_id_helper')),
                ColorPicker::make('color')
                    ->label(trans('variety.color'))
                    ->nullable()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('variety.color_hint')),
                TextInput::make('price')
                    ->label(trans('variety.price'))
                    ->required()
                    ->numeric()
                    ->prefix('تومان')
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('variety.price_hint')),
                TextInput::make('sale_price')
                    ->label(trans('variety.sale_price'))
                    ->numeric()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('variety.sale_price_hint')),
                TextInput::make('inventory')
                    ->label(trans('variety.inventory'))
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('variety.inventory_hint')),
                Toggle::make('has_stock')
                    ->label(trans('variety.has_stock'))
                    ->required()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('variety.has_stock_hint')),
                Select::make('status')
                    ->label(trans('variety.status'))
                    ->required()
                    ->options(VarietyStatusEnum::options())
                    ->default(VarietyStatusEnum::PUBLISHED->value),
                Select::make('attributes')
                    ->label(trans('variety.additional_attributes'))
                    ->multiple()
                    ->relationship(
                        name: 'attributes',
                        titleAttribute: 'value',
                        modifyQueryUsing: fn (Builder $query): Builder => $query->with('attributeGroup'),
                    )
                    ->getOptionLabelFromRecordUsing(fn (Attribute $record): string => $record->attributeGroup->name . ' / ' . $record->value)
                    ->searchable()
                    ->preload()
                    ->columnSpanFull()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('variety.additional_attributes_hint')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.heading')
                    ->label(trans('variety.product'))
                    ->limit(30)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('attribute.value')
                    ->label(trans('variety.attribute_id'))
                    ->searchable(),
                TextColumn::make('attribute_value')
                    ->label(trans('variety.attribute_value')),
                ColorColumn::make('color')
                    ->label(trans('variety.color'))
                    ->copyable(),
                TextColumn::make('price')
                    ->label(trans('variety.price'))
                    ->money(),
                TextColumn::make('sale_price')
                    ->label(trans('variety.sale_price'))
                    ->numeric(),
                TextColumn::make('inventory')
                    ->label(trans('variety.inventory'))
                    ->numeric(),
                IconColumn::make('has_stock')
                    ->label(trans('variety.has_stock'))
                    ->boolean(),
                TextColumn::make('status')
                    ->label(trans('variety.status'))
                    ->getStateUsing(fn (Variety $record): string => $record->status->label())
                    ->color(fn (Variety $record): string => $record->status->color())
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(trans('variety.created_at'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(trans('variety.updated_at'))
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
