<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\GatewayForEnum;
use App\Filament\Resources\GatewayResource\Pages\CreateGateway;
use App\Filament\Resources\GatewayResource\Pages\EditGateway;
use App\Filament\Resources\GatewayResource\Pages\ListGateways;
use App\Models\Gateway;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GatewayResource extends Resource
{
    protected static ?string $model = Gateway::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-library';

    public static function getNavigationGroup(): ?string
    {
        return trans('gateway.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return trans('gateway.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('gateway.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(trans('gateway.name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('key')
                    ->label(trans('gateway.key'))
                    ->required()
                    ->maxLength(255),
                Select::make('for')
                    ->label(trans('gateway.for'))
                    ->required()
                    ->options(GatewayForEnum::options())
                    ->default(GatewayForEnum::EVERYONE->value),
                TextInput::make('gate_id')
                    ->label(trans('gateway.gate_id'))
                    ->maxLength(255),
                TextInput::make('username')
                    ->label(trans('gateway.username'))
                    ->maxLength(255),
                TextInput::make('password')
                    ->label(trans('gateway.password'))
                    ->password()
                    ->revealable()
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->maxLength(255),
                TextInput::make('priority')
                    ->label(trans('gateway.priority'))
                    ->numeric()
                    ->default(0),
                TextInput::make('coding')
                    ->label(trans('gateway.coding'))
                    ->maxLength(255),
                Toggle::make('active')
                    ->label(trans('gateway.active'))
                    ->default(true),
                Fieldset::make(trans('gateway.image'))
                    ->relationship('image')
                    ->schema([
                        FileUpload::make('path')
                            ->label(trans('gateway.path'))
                            ->image()
                            ->nullable()
                            ->columnSpanFull(),
                        TextInput::make('alt_text')
                            ->label(trans('gateway.alt_text'))
                            ->nullable()
                            ->maxLength(255),
                    ])
                    ->mutateRelationshipDataBeforeSaveUsing(function (array $data, Gateway $record): array {
                        if (empty($data['path'])) {
                            $record->image?->delete();
                        }

                        return $data;
                    })
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('priority', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('name')
                    ->label(trans('gateway.name'))
                    ->searchable(),
                TextColumn::make('key')
                    ->label(trans('gateway.key'))
                    ->searchable(),
                TextColumn::make('for')
                    ->label(trans('gateway.for'))
                    ->badge()
                    ->getStateUsing(fn (Gateway $record): string => $record->for->label())
                    ->color(fn (Gateway $record): string => $record->for->color()),
                TextColumn::make('priority')
                    ->label(trans('gateway.priority'))
                    ->sortable(),
                IconColumn::make('active')
                    ->label(trans('gateway.active'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(trans('gateway.created_at'))
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
            'index' => ListGateways::route('/'),
            'create' => CreateGateway::route('/create'),
            'edit' => EditGateway::route('/{record}/edit'),
        ];
    }
}
