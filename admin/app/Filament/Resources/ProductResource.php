<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use App\Enums\ProductStatusEnum;
use App\Enums\VarietyStatusEnum;
use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Models\Attribute;
use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Livewire\Component;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Catalog';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('heading')
                    ->required()
                    ->live(onBlur: true)
                    ->maxLength(255)
                    ->afterStateUpdated(function (string $operation, ?string $state, Set $set): void {
                        if ($operation === 'create') {
                            $set('slug', Str::slug($state));
                        }
                    }),
                TextInput::make('slug')
                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->maxLength(255)
                    ->unique(Product::class, 'slug', ignoreRecord: true),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('تومان'),
                TinyEditor::make('content')
                    ->columnSpanFull()
                    ->required(),
                TextInput::make('title')
                    ->maxLength(255),
                Textarea::make('description')
                    ->maxLength(255)
                    ->columnSpanFull(),
                Toggle::make('no_index')
                    ->required(),
                TextInput::make('canonical')
                    ->maxLength(255),
                Repeater::make('images')
                    ->relationship('images')
                    ->schema([
                        FileUpload::make('path')
                            ->nullable()
                            ->columns(1)
                            ->columnSpanFull(),

                        Toggle::make('is_featured')
                            ->label('Featured Image')
                            ->reactive(),

                        TextInput::make('alt_text')
                            ->label('Alt Text'),
                    ])
                    ->columnSpanFull(),

                Select::make('attribute_group_id')
                    ->relationship('attributeGroup', 'name')
                    ->native(false)
                    ->preload(),
                Select::make('category_id')
                    ->relationship('category', 'heading')
                    ->required()
                    ->native(false)
                    ->preload(),
                Select::make('brand_id')
                    ->relationship('brand', 'heading')
                    ->required()
                    ->native(false)
                    ->preload(),
                TextInput::make('minimum')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('maximum')
                    ->numeric(),
                TextInput::make('step')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('profit_percent')
                    ->required()
                    ->numeric()
                    ->suffix('%')
                    ->default(0),

                Repeater::make('attributes')
                    ->schema([
                        Select::make('attribute_id')
                            ->label('Attribute')
                            ->options(
                                Attribute::with('attributeGroup')->get()->mapWithKeys(function (Attribute $attribute): array {
                                    return [
                                        $attribute->id => $attribute->attributeGroup->name . ' - ' . $attribute->value,
                                    ];
                                })
                            )
                            ->searchable(),
                        Checkbox::make('pivot.is_highlight')
                            ->label('Highlight'),
                    ])
                    ->dehydrated(false)
                    ->afterStateHydrated(function (mixed $state, callable $set, Component $livewire): void {
                        if (isset($livewire->record) && $livewire->record) {
                            $attributes = $livewire->record->attributes()->get();

                            $data = $attributes->map(function (Attribute $attribute): array {
                                return [
                                    'attribute_id' => $attribute->id,
                                    // @phpstan-ignore-next-line property.notFound
                                    'pivot' => ['is_highlight' => $attribute->pivot->is_highlight],
                                ];
                            })->toArray();

                            $set('attributes', $data);
                        }
                    })
                    ->saveRelationshipsUsing(function (Product $record, array $state): void {
                        $syncData = [];
                        foreach ($state as $item) {
                            if (empty($item['attribute_id'])) {
                                continue;
                            }
                            $syncData[$item['attribute_id']] = ['is_highlight' => $item['pivot']['is_highlight'] ?? false];
                        }
                        $record->attributes()->sync($syncData);
                    }),
                Toggle::make('has_stock')
                    ->default(true)
                    ->required(),
                TextInput::make('variety_counts')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Calculated automatically from the varieties below.'),
                TextInput::make('weight')
                    ->numeric()
                    ->nullable(),
                TextInput::make('length')
                    ->numeric()
                    ->nullable(),
                TextInput::make('width')
                    ->numeric()
                    ->nullable(),
                TextInput::make('height')
                    ->numeric()
                    ->nullable(),
                Select::make('status')
                    ->required()
                    ->options(ProductStatusEnum::options())
                    ->default(ProductStatusEnum::PUBLISHED->value),
                TextInput::make('seen')
                    ->required()
                    ->numeric()
                    ->default(0),
                Fieldset::make('Variety Details')
                    ->schema([
                        Repeater::make('varieties')
                            ->relationship('varieties')
                            ->schema([
                                TextInput::make('attribute_value')
                                    ->maxLength(255),
                                TextInput::make('color')
                                    ->maxLength(255),
                                TextInput::make('price')
                                    ->required()
                                    ->numeric(),
                                TextInput::make('sale_price')
                                    ->numeric()
                                    ->nullable(),
                                TextInput::make('inventory')
                                    ->required()
                                    ->numeric()
                                    ->default(0),
                                Toggle::make('has_stock')
                                    ->default(true)
                                    ->required(),
                                Select::make('status')
                                    ->required()
                                    ->options(VarietyStatusEnum::options())
                                    ->default(VarietyStatusEnum::PUBLISHED->value),
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('heading')
                    ->limit(30)
                    ->wrap(),
                TextColumn::make('slug')
                    ->limit(30)
                    ->wrap(),
                ImageColumn::make('featuredImage.path')
                    ->label('Featured')
                    ->square(),
                TextColumn::make('price')
                    ->money()
                    ->sortable(),
                TextColumn::make('title')
                    ->searchable(),
                IconColumn::make('no_index')
                    ->boolean(),
                TextColumn::make('canonical')
                    ->searchable(),
                TextColumn::make('attributeGroup.name')
                    ->sortable(),
                TextColumn::make('category.heading')
                    ->limit(60),
                TextColumn::make('brand.heading')
                    ->sortable(),
                TextColumn::make('minimum')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('maximum')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('step')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('profit_percent')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('has_stock')
                    ->boolean(),
                TextColumn::make('variety_counts')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('weight')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('length')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('width')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('height')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->getStateUsing(fn (Product $record) => $record->status->label())
                    ->color(fn (Product $record): string => $record->status->color())
                    ->sortable(),
                TextColumn::make('seen')
                    ->numeric()
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
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
