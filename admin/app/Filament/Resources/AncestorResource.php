<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\AncestorResource\Pages\CreateAncestor;
use App\Filament\Resources\AncestorResource\Pages\EditAncestor;
use App\Filament\Resources\AncestorResource\Pages\ListAncestors;
use App\Models\Ancestor;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AncestorResource extends Resource
{
    protected static ?string $model = Ancestor::class;

    protected static ?int $navigationSort = 1;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationGroup(): ?string
    {
        return trans('ancestor.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return trans('ancestor.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('ancestor.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(trans('ancestor.name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('order')
                    ->label(trans('ancestor.order'))
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(trans('ancestor.name'))
                    ->searchable(),
                TextColumn::make('order')
                    ->label(trans('ancestor.order'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(trans('ancestor.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(trans('ancestor.updated_at'))
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
            'index' => ListAncestors::route('/'),
            'create' => CreateAncestor::route('/create'),
            'edit' => EditAncestor::route('/{record}/edit'),
        ];
    }
}
