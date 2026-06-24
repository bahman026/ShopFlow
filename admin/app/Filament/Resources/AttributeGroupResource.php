<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\AttributeGroupResource\Pages\CreateAttributeGroup;
use App\Filament\Resources\AttributeGroupResource\Pages\EditAttributeGroup;
use App\Filament\Resources\AttributeGroupResource\Pages\ListAttributeGroups;
use App\Models\AttributeGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AttributeGroupResource extends Resource
{
    protected static ?string $model = AttributeGroup::class;

    protected static ?int $navigationSort = 2;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationGroup(): ?string
    {
        return trans('attribute_group.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return trans('attribute_group.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('attribute_group.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('ancestor_id')
                    ->label(trans('attribute_group.ancestor_id'))
                    ->relationship('ancestor', 'name')
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('attribute_group.ancestor_id_hint'))
                    ->required(),
                TextInput::make('name')
                    ->label(trans('attribute_group.name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('label')
                    ->label(trans('attribute_group.label_field'))
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('attribute_group.label_field_hint'))
                    ->maxLength(255),
                TextInput::make('order')
                    ->label(trans('attribute_group.order'))
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ancestor.name')
                    ->label(trans('attribute_group.ancestor'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(trans('attribute_group.name'))
                    ->searchable(),
                TextColumn::make('label')
                    ->label(trans('attribute_group.label_field'))
                    ->searchable(),
                TextColumn::make('order')
                    ->label(trans('attribute_group.order'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(trans('attribute_group.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(trans('attribute_group.updated_at'))
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
            'index' => ListAttributeGroups::route('/'),
            'create' => CreateAttributeGroup::route('/create'),
            'edit' => EditAttributeGroup::route('/{record}/edit'),
        ];
    }
}
