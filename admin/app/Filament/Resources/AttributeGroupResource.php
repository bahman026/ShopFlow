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

    protected static string | \UnitEnum | null $navigationGroup = 'Attribute';

    protected static ?int $navigationSort = 2;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('ancestor_id')
                    ->relationship('ancestor', 'name')
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Groups attribute groups under a common ancestor (e.g. "Physical", "Technical"). Create ancestors first.')
                    ->required(),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('label')
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('The label shown to the customer on the frontend (e.g. "Choose a color"). If empty, the name is used.')
                    ->maxLength(255),
                TextInput::make('order')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ancestor.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('label')
                    ->searchable(),
                TextColumn::make('order')
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
