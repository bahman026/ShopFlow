<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\ProductStatusEnum;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Attribute;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Mohamedsabil83\FilamentFormsTinyeditor\Components\TinyEditor;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationGroup = 'Product';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('heading')
                    ->required()
                    ->live(onBlur: true)
                    ->maxLength(255)
                    ->afterStateUpdated(function (string $operation, ?string $state, Forms\Set $set): void {
                        if ($operation === 'create') {
                            $set('slug', Str::slug($state));
                        }
                    }),
                Forms\Components\TextInput::make('slug')
                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->maxLength(255)
                    ->unique(Product::class, 'slug', ignoreRecord: true),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('تومان'),
                TinyEditor::make('content')
                    ->columnSpanFull()
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('no_index')
                    ->required(),
                Forms\Components\TextInput::make('canonical')
                    ->maxLength(255),
                Forms\Components\Repeater::make('images')
                    ->relationship('images')
                    ->schema([
                        Forms\Components\FileUpload::make('path')
                            ->nullable()
                            ->columns(1)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_featured')
                            ->label('Featured Image')
                            ->reactive(),

                        Forms\Components\TextInput::make('alt_text')
                            ->label('Alt Text'),
                    ])
                    ->columnSpanFull(),

                Forms\Components\Select::make('attribute_group_id')
                    ->relationship('attributeGroup', 'name')
                    ->native(false)
                    ->preload(),
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'heading')
                    ->required()
                    ->native(false)
                    ->preload(),
                Forms\Components\Select::make('brand_id')
                    ->relationship('brand', 'heading')
                    ->required()
                    ->native(false)
                    ->preload(),
                Forms\Components\TextInput::make('minimum')
                    ->required()
                    ->numeric()
                    ->default(1),
                Forms\Components\TextInput::make('maximum')
                    ->numeric(),
                Forms\Components\TextInput::make('step')
                    ->required()
                    ->numeric()
                    ->default(1),
                Forms\Components\TextInput::make('profit_percent')
                    ->required()
                    ->numeric()
                    ->suffix('%')
                    ->default(0),

                Forms\Components\Repeater::make('attributes')
                    ->schema([
                        Forms\Components\Select::make('attribute_id')
                            ->label('Attribute')
                            ->options(
                                Attribute::with('attributeGroup')->get()->mapWithKeys(function ($attribute) {
                                    return [
                                        $attribute->id => $attribute->attributeGroup->name . ' - ' . $attribute->value,
                                    ];
                                })
                            )
                            ->searchable(),
                        Forms\Components\Checkbox::make('pivot.is_highlight')
                            ->label('Highlight'),
                    ])
                    ->dehydrated(false)
                    ->afterStateHydrated(function ($state, callable $set, $livewire) {
                        if ($livewire->record) {
                            $attributes = $livewire->record->attributes()->get();

                            $data = $attributes->map(function ($attribute) {
                                return [
                                    'attribute_id' => $attribute->id,
                                    'pivot' => ['is_highlight' => $attribute->pivot->is_highlight],
                                ];
                            })->toArray();

                            $set('attributes', $data);
                        }
                    })
                    ->saveRelationshipsUsing(function ($record, $state) {
                        $syncData = [];
                        foreach ($state as $item) {
                            $syncData[$item['attribute_id']] = ['is_highlight' => $item['pivot']['is_highlight'] ?? false];
                        }
                        $record->attributes()->sync($syncData);
                    }),
                Forms\Components\Toggle::make('has_stock')
                    ->default(true)
                    ->required(),
                Forms\Components\TextInput::make('variety_counts')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('weight')
                    ->numeric()
                    ->nullable(),
                Forms\Components\TextInput::make('length')
                    ->numeric()
                    ->nullable(),
                Forms\Components\TextInput::make('width')
                    ->numeric()
                    ->nullable(),
                Forms\Components\TextInput::make('height')
                    ->numeric()
                    ->nullable(),
                Forms\Components\Select::make('status')
                    ->required()
                    ->options(ProductStatusEnum::options())
                    ->default(ProductStatusEnum::DRAFT->value),
                Forms\Components\TextInput::make('seen')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('heading')
                    ->limit(30)
                    ->wrap(),
                Tables\Columns\TextColumn::make('slug')
                    ->limit(30)
                    ->wrap(),
                Tables\Columns\ImageColumn::make('featuredImage.path')
                    ->label('Featured')
                    ->square(),
                Tables\Columns\TextColumn::make('price')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\IconColumn::make('no_index')
                    ->boolean(),
                Tables\Columns\TextColumn::make('canonical')
                    ->searchable(),
                Tables\Columns\TextColumn::make('attributeGroup.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.title')
                    ->numeric()
                    ->limit(60),
                Tables\Columns\TextColumn::make('brand.title')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('minimum')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('maximum')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('step')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('profit_percent')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('has_stock')
                    ->boolean(),
                Tables\Columns\TextColumn::make('variety_counts')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('weight')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('length')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('width')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('height')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->getStateUsing(fn (Product $record) => $record->status->label())
                    ->color(fn (Product $record): string => $record->status->color())
                    ->sortable(),
                Tables\Columns\TextColumn::make('seen')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
