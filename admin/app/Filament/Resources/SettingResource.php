<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages\CreateSetting;
use App\Filament\Resources\SettingResource\Pages\EditSetting;
use App\Filament\Resources\SettingResource\Pages\ListSettings;
use App\Models\Setting;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';

    public static function getNavigationGroup(): ?string
    {
        return trans('setting.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return trans('setting.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('setting.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->label(trans('setting.key'))
                    ->required()
                    ->maxLength(255)
                    ->unique(Setting::class, 'key', ignoreRecord: true),
                TextInput::make('label')
                    ->label(trans('setting.label_field'))
                    ->maxLength(255),
                Toggle::make('autoload')
                    ->label(trans('setting.autoload'))
                    ->default(true),
                Textarea::make('content')
                    ->label(trans('setting.content'))
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
                TextColumn::make('key')
                    ->label(trans('setting.key'))
                    ->searchable(),
                TextColumn::make('label')
                    ->label(trans('setting.label_field'))
                    ->searchable()
                    ->limit(30)
                    ->wrap(),
                IconColumn::make('autoload')
                    ->label(trans('setting.autoload'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(trans('setting.created_at'))
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
            'index' => ListSettings::route('/'),
            'create' => CreateSetting::route('/create'),
            'edit' => EditSetting::route('/{record}/edit'),
        ];
    }
}
