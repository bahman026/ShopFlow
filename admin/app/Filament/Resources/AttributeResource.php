<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\AttributeResource\Pages\CreateAttribute;
use App\Filament\Resources\AttributeResource\Pages\EditAttribute;
use App\Filament\Resources\AttributeResource\Pages\ListAttributes;
use App\Models\Attribute;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AttributeResource extends Resource
{
    protected static ?string $model = Attribute::class;

    protected static ?int $navigationSort = 4;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationGroup(): ?string
    {
        return trans('attribute.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return trans('attribute.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('attribute.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('attribute_group_id')
                    ->label(trans('attribute.attribute_group_id'))
                    ->relationship('attributeGroup', 'name')
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('attribute.attribute_group_id_hint'))
                    ->required(),
                TextInput::make('value')
                    ->label(trans('attribute.value'))
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('attribute.value_hint'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('color')
                    ->label(trans('attribute.color'))
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('attribute.color_hint'))
                    ->nullable()
                    ->maxLength(31),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('attributeGroup.name')
                    ->label(trans('attribute.attribute_group'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('value')
                    ->label(trans('attribute.value')),
                TextColumn::make('color')
                    ->label(trans('attribute.color')),
                TextColumn::make('created_at')
                    ->label(trans('attribute.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(trans('attribute.updated_at'))
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
            'index' => ListAttributes::route('/'),
            'create' => CreateAttribute::route('/create'),
            'edit' => EditAttribute::route('/{record}/edit'),
        ];
    }
}
