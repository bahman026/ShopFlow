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

    protected static string | \UnitEnum | null $navigationGroup = 'Catalog';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->relationship('product', 'heading')
                    ->searchable()
                    ->live()
                    ->required()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('The product this variety belongs to. Changing it reloads the available attributes below.'),
                Select::make('attribute_id')
                    ->label('Attribute')
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('The attribute that defines this variety (e.g. "Red" from the "Color" group). Auto-fills value and color on save. Options are filtered by the product\'s attribute group.')
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
                    ->helperText('Selecting an attribute auto-fills the value and color.'),
                ColorPicker::make('color')
                    ->nullable()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Hex color for this variety (e.g. #ff8516). Auto-filled from the selected attribute — override here if needed.'),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('تومان')
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('The selling price of this variety.'),
                TextInput::make('sale_price')
                    ->numeric()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Discounted price shown instead of the regular price when set. Leave empty for no sale price.'),
                TextInput::make('inventory')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Number of units available in stock.'),
                Toggle::make('has_stock')
                    ->required()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('When off, this variety is shown as out of stock regardless of inventory count.'),
                Select::make('status')
                    ->required()
                    ->options(VarietyStatusEnum::options())
                    ->default(VarietyStatusEnum::PUBLISHED->value),
                Select::make('attributes')
                    ->label('Additional Attributes')
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
                    ->hintIconTooltip('Secondary attributes for this variety from other groups, e.g. Color when the primary group is Size. Stored in the attribute_variety pivot table.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.heading')
                    ->limit(30)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('attribute.value')
                    ->label('Attribute')
                    ->searchable(),
                TextColumn::make('attribute_value')
                    ->label('Value'),
                ColorColumn::make('color')
                    ->copyable(),
                TextColumn::make('price')
                    ->money(),
                TextColumn::make('sale_price')
                    ->numeric(),
                TextColumn::make('inventory')
                    ->numeric(),
                IconColumn::make('has_stock')
                    ->boolean(),
                TextColumn::make('status')
                    ->getStateUsing(fn (Variety $record): string => $record->status->label())
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
