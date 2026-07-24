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
use App\Models\Attribute as AttributeModel;
use App\Models\AttributeGroup;
use App\Models\AttributeGroupCategory;
use App\Models\Product;
use App\Models\Variety;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Component;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shopping-bag';

    public static function getNavigationGroup(): ?string
    {
        return trans('product.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return trans('product.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('product.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('heading')
                    ->label(trans('product.heading'))
                    ->required()
                    ->live(onBlur: true)
                    ->maxLength(255)
                    ->afterStateUpdated(function (string $operation, ?string $state, Set $set): void {
                        if ($operation === 'create') {
                            $set('slug', Str::slug($state));
                        }
                    }),
                TextInput::make('slug')
                    ->label(trans('product.slug'))
                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->maxLength(255)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('product.slug_hint'))
                    ->unique(Product::class, 'slug', ignoreRecord: true),
                TextInput::make('price')
                    ->label(trans('product.price'))
                    ->required()
                    ->numeric()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('product.price_hint'))
                    ->prefix('تومان'),
                TinyEditor::make('content')
                    ->label(trans('product.content'))
                    ->columnSpanFull()
                    ->required(),
                TextInput::make('title')
                    ->label(trans('product.title'))
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('product.title_hint'))
                    ->maxLength(255),
                Textarea::make('description')
                    ->label(trans('product.description'))
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('product.description_hint'))
                    ->maxLength(255)
                    ->columnSpanFull(),
                Toggle::make('no_index')
                    ->label(trans('product.no_index'))
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('product.no_index_hint'))
                    ->required(),
                TextInput::make('canonical')
                    ->label(trans('product.canonical'))
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('product.canonical_hint'))
                    ->maxLength(255),
                Repeater::make('images')
                    ->label(trans('product.images'))
                    ->relationship('images')
                    ->schema([
                        FileUpload::make('path')
                            ->label(trans('product.path'))
                            ->image()
                            ->nullable()
                            ->columns(1)
                            ->columnSpanFull(),

                        Toggle::make('is_featured')
                            ->label(trans('product.is_featured'))
                            ->reactive(),

                        TextInput::make('alt_text')
                            ->label(trans('product.alt_text')),
                    ])
                    ->columnSpanFull(),

                Select::make('category_id')
                    ->label(trans('product.category_id'))
                    ->relationship('category', 'heading')
                    ->required()
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('attribute_group_id', null))
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('product.category_id_hint')),
                Select::make('attribute_group_id')
                    ->label(trans('product.attribute_group_id'))
                    ->options(function (Get $get, ?Product $record): array {
                        $categoryId = $get('category_id');
                        if (! $categoryId) {
                            return AttributeGroup::query()->pluck('name', 'id')->toArray();
                        }

                        $groupIds = AttributeGroupCategory::query()
                            ->where('category_id', $categoryId)
                            ->pluck('attribute_group_id');

                        if ($record?->attribute_group_id) {
                            $groupIds = $groupIds->push($record->attribute_group_id)->unique();
                        }

                        return AttributeGroup::query()
                            ->whereIn('id', $groupIds)
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->native(false)
                    ->live()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('product.attribute_group_id_hint')),
                Select::make('brand_id')
                    ->label(trans('product.brand_id'))
                    ->relationship('brand', 'heading')
                    ->required()
                    ->native(false)
                    ->preload(),
                TextInput::make('minimum')
                    ->label(trans('product.minimum'))
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('product.minimum_hint')),
                TextInput::make('maximum')
                    ->label(trans('product.maximum'))
                    ->numeric()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('product.maximum_hint')),
                TextInput::make('step')
                    ->label(trans('product.step'))
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('product.step_hint')),
                TextInput::make('profit_percent')
                    ->label(trans('product.profit_percent'))
                    ->required()
                    ->numeric()
                    ->suffix('%')
                    ->default(0)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('product.profit_percent_hint')),

                Repeater::make('attributes')
                    ->label(trans('product.attributes'))
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('product.attributes_hint'))
                    ->schema([
                        Select::make('attribute_id')
                            ->label(trans('product.attribute_id'))
                            ->options(
                                Attribute::with('attributeGroup')->get()->mapWithKeys(function (Attribute $attribute): array {
                                    return [
                                        $attribute->id => $attribute->attributeGroup->name . ' - ' . $attribute->value,
                                    ];
                                })
                            )
                            ->searchable(),
                        Checkbox::make('pivot.is_highlight')
                            ->label(trans('product.is_highlight')),
                    ])
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
                    ->label(trans('product.has_stock'))
                    ->default(true)
                    ->required(),
                TextInput::make('variety_counts')
                    ->label(trans('product.variety_counts'))
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText(trans('product.variety_counts_helper')),
                TextInput::make('weight')
                    ->label(trans('product.weight'))
                    ->numeric()
                    ->nullable(),
                TextInput::make('length')
                    ->label(trans('product.length'))
                    ->numeric()
                    ->nullable(),
                TextInput::make('width')
                    ->label(trans('product.width'))
                    ->numeric()
                    ->nullable(),
                TextInput::make('height')
                    ->label(trans('product.height'))
                    ->numeric()
                    ->nullable(),
                Select::make('status')
                    ->label(trans('product.status'))
                    ->required()
                    ->options(ProductStatusEnum::options())
                    ->default(ProductStatusEnum::PUBLISHED->value),
                TextInput::make('seen')
                    ->label(trans('product.seen'))
                    ->required()
                    ->numeric()
                    ->default(0),
                Repeater::make('varieties')
                    ->label(trans('product.varieties'))
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('product.varieties_hint'))
                    ->relationship('varieties')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('attribute_id')
                            ->label(trans('product.variety_attribute_id'))
                            ->options(function (Get $get): array {
                                $groupId = $get('../../attribute_group_id');
                                if (! $groupId) {
                                    return [];
                                }

                                return AttributeModel::query()
                                    ->where('attribute_group_id', $groupId)
                                    ->pluck('value', 'id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->nullable()
                            ->helperText(trans('product.variety_attribute_helper')),
                        ColorPicker::make('color')
                            ->label(trans('product.variety_color'))
                            ->nullable()
                            ->hintIcon('heroicon-o-information-circle')
                            ->hintIconTooltip(trans('product.variety_color_hint')),
                        Select::make('attributes')
                            ->label(trans('product.variety_additional_attributes'))
                            ->multiple()
                            ->relationship(
                                name: 'attributes',
                                titleAttribute: 'value',
                                modifyQueryUsing: function (Builder $query, Get $get): Builder {
                                    $groupId = $get('../../attribute_group_id');
                                    $query->with('attributeGroup');
                                    if ($groupId) {
                                        $query->where('attribute_group_id', '!=', (int) $groupId);
                                    }

                                    return $query;
                                },
                            )
                            ->getOptionLabelFromRecordUsing(fn (AttributeModel $record): string => $record->attributeGroup->name . ' / ' . $record->value)
                            ->searchable()
                            ->preload()
                            ->hintIcon('heroicon-o-information-circle')
                            ->hintIconTooltip(trans('product.variety_additional_attributes_hint')),
                        TextInput::make('price')
                            ->label(trans('product.variety_price'))
                            ->required()
                            ->numeric(),
                        TextInput::make('sale_price')
                            ->label(trans('product.variety_sale_price'))
                            ->numeric()
                            ->nullable(),
                        TextInput::make('inventory')
                            ->label(trans('product.variety_inventory'))
                            ->required()
                            ->numeric()
                            ->default(0),
                        Toggle::make('has_stock')
                            ->label(trans('product.variety_has_stock'))
                            ->default(true)
                            ->required(),
                        Select::make('status')
                            ->label(trans('product.variety_status'))
                            ->required()
                            ->options(VarietyStatusEnum::options())
                            ->default(VarietyStatusEnum::PUBLISHED->value),
                        Fieldset::make(trans('product.variety_image'))
                            ->relationship('image')
                            ->schema([
                                FileUpload::make('path')
                                    ->label(trans('product.path'))
                                    ->image()
                                    ->nullable()
                                    ->columnSpanFull(),
                                TextInput::make('alt_text')
                                    ->label(trans('product.alt_text'))
                                    ->nullable()
                                    ->maxLength(255),
                            ])
                            ->mutateRelationshipDataBeforeSaveUsing(function (array $data, Variety $record): array {
                                if (empty($data['path'])) {
                                    $record->image?->delete();
                                }

                                return $data;
                            })
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('heading')
                    ->label(trans('product.heading'))
                    ->limit(30)
                    ->wrap(),
                TextColumn::make('slug')
                    ->label(trans('product.slug'))
                    ->limit(30)
                    ->wrap(),
                ImageColumn::make('featuredImage.path')
                    ->label(trans('product.featured'))
                    ->square(),
                TextColumn::make('price')
                    ->label(trans('product.price'))
                    ->money()
                    ->sortable(),
                TextColumn::make('title')
                    ->label(trans('product.title'))
                    ->searchable(),
                IconColumn::make('no_index')
                    ->label(trans('product.no_index'))
                    ->boolean(),
                TextColumn::make('canonical')
                    ->label(trans('product.canonical'))
                    ->searchable(),
                TextColumn::make('attributeGroup.name')
                    ->label(trans('product.attribute_group'))
                    ->sortable(),
                TextColumn::make('category.heading')
                    ->label(trans('product.category'))
                    ->limit(60),
                TextColumn::make('brand.heading')
                    ->label(trans('product.brand'))
                    ->sortable(),
                TextColumn::make('minimum')
                    ->label(trans('product.minimum'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('maximum')
                    ->label(trans('product.maximum'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('step')
                    ->label(trans('product.step'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('profit_percent')
                    ->label(trans('product.profit_percent'))
                    ->numeric()
                    ->sortable(),
                IconColumn::make('has_stock')
                    ->label(trans('product.has_stock'))
                    ->boolean(),
                TextColumn::make('variety_counts')
                    ->label(trans('product.variety_counts'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('weight')
                    ->label(trans('product.weight'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('length')
                    ->label(trans('product.length'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('width')
                    ->label(trans('product.width'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('height')
                    ->label(trans('product.height'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(trans('product.status'))
                    ->getStateUsing(fn (Product $record) => $record->status->label())
                    ->color(fn (Product $record): string => $record->status->color())
                    ->sortable(),
                TextColumn::make('seen')
                    ->label(trans('product.seen'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(trans('product.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(trans('product.updated_at'))
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

    /**
     * Returns names of required attribute groups that have no selected attribute.
     *
     * @param  array<mixed>  $attributeState
     * @return Collection<int, string>
     */
    public static function missingRequiredGroups(array $attributeState, int $categoryId): Collection
    {
        $selectedAttributeIds = collect($attributeState)
            ->filter(fn (array $item): bool => ! empty($item['attribute_id']))
            ->pluck('attribute_id')
            ->map(fn (mixed $id): int => (int) $id)
            ->all();

        $requiredGroupIds = AttributeGroupCategory::query()
            ->where('category_id', $categoryId)
            ->where('required', true)
            ->pluck('attribute_group_id');

        if ($requiredGroupIds->isEmpty()) {
            return collect();
        }

        $selectedGroupIds = Attribute::query()
            ->whereIn('id', $selectedAttributeIds)
            ->pluck('attribute_group_id');

        return AttributeGroup::query()
            ->whereIn('id', $requiredGroupIds->diff($selectedGroupIds))
            ->pluck('name');
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
