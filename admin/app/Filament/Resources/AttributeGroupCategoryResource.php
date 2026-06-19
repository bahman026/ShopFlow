<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\AttributeGroupCategoryResource\Pages\CreateAttributeGroupCategory;
use App\Filament\Resources\AttributeGroupCategoryResource\Pages\EditAttributeGroupCategory;
use App\Filament\Resources\AttributeGroupCategoryResource\Pages\ListAttributeGroupCategories;
use App\Models\AttributeGroupCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AttributeGroupCategoryResource extends Resource
{
    protected static ?string $model = AttributeGroupCategory::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Attribute';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('attribute_group_id')
                    ->relationship('attributeGroup', 'name')
                    ->required(),
                Select::make('category_id')
                    ->relationship('category', 'title')
                    ->required(),
                Toggle::make('as_filter')
                    ->required(),
                Toggle::make('required')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('attributeGroup.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('category.title')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('as_filter')
                    ->boolean(),
                IconColumn::make('required')
                    ->boolean(),
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
                    //                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => ListAttributeGroupCategories::route('/'),
            'create' => CreateAttributeGroupCategory::route('/create'),
            'edit' => EditAttributeGroupCategory::route('/{record}/edit'),
        ];
    }
}
