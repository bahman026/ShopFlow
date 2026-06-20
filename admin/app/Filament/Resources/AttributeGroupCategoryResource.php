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

    protected static ?int $navigationSort = 3;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationGroup(): ?string
    {
        return trans('attribute_group_category.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return trans('attribute_group_category.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('attribute_group_category.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('attribute_group_id')
                    ->label(trans('attribute_group_category.attribute_group_id'))
                    ->relationship('attributeGroup', 'name')
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('attribute_group_category.attribute_group_id_hint'))
                    ->required(),
                Select::make('category_id')
                    ->label(trans('attribute_group_category.category_id'))
                    ->relationship('category', 'title')
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('attribute_group_category.category_id_hint'))
                    ->required(),
                Toggle::make('as_filter')
                    ->label(trans('attribute_group_category.as_filter'))
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('attribute_group_category.as_filter_hint'))
                    ->required(),
                Toggle::make('required')
                    ->label(trans('attribute_group_category.required'))
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('attribute_group_category.required_hint'))
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('attributeGroup.name')
                    ->label(trans('attribute_group_category.attribute_group'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('category.title')
                    ->label(trans('attribute_group_category.category'))
                    ->numeric()
                    ->sortable(),
                IconColumn::make('as_filter')
                    ->label(trans('attribute_group_category.as_filter'))
                    ->boolean(),
                IconColumn::make('required')
                    ->label(trans('attribute_group_category.required'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(trans('attribute_group_category.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(trans('attribute_group_category.updated_at'))
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
