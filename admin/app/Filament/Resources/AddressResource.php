<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\AddressResource\Pages\CreateAddress;
use App\Filament\Resources\AddressResource\Pages\EditAddress;
use App\Filament\Resources\AddressResource\Pages\ListAddresses;
use App\Models\Address;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AddressResource extends Resource
{
    protected static ?string $model = Address::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-map-pin';

    public static function getNavigationGroup(): ?string
    {
        return trans('address.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return trans('address.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('address.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label(trans('address.user_id'))
                    ->relationship('user', 'email')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->native(false),
                Select::make('city_id')
                    ->label(trans('address.city_id'))
                    ->relationship('city', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->native(false),
                TextInput::make('name')
                    ->label(trans('address.name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label(trans('address.phone'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('postal_code')
                    ->label(trans('address.postal_code'))
                    ->maxLength(255),
                Toggle::make('prime')
                    ->label(trans('address.prime')),
                Textarea::make('address')
                    ->label(trans('address.address'))
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->label(trans('address.description'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label(trans('address.user_id'))
                    ->searchable(),
                TextColumn::make('name')
                    ->label(trans('address.name'))
                    ->searchable(),
                TextColumn::make('city.name')
                    ->label(trans('address.city_id'))
                    ->searchable(),
                TextColumn::make('phone')
                    ->label(trans('address.phone'))
                    ->searchable(),
                IconColumn::make('prime')
                    ->label(trans('address.prime'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(trans('address.created_at'))
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
                //
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
            'index' => ListAddresses::route('/'),
            'create' => CreateAddress::route('/create'),
            'edit' => EditAddress::route('/{record}/edit'),
        ];
    }
}
