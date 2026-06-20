<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\BannerStatusEnum;
use App\Filament\Resources\BannerResource\Pages\CreateBanner;
use App\Filament\Resources\BannerResource\Pages\EditBanner;
use App\Filament\Resources\BannerResource\Pages\ListBanners;
use App\Models\Banner;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-photo';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return trans('banner.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return trans('banner.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('banner.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('position')
                    ->label(trans('banner.position'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('heading')
                    ->label(trans('banner.heading'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('url')
                    ->label(trans('banner.url'))
                    ->url()
                    ->maxLength(255),
                TextInput::make('sort')
                    ->label(trans('banner.sort'))
                    ->numeric()
                    ->nullable(),
                Select::make('status')
                    ->label(trans('banner.status'))
                    ->required()
                    ->options(BannerStatusEnum::options())
                    ->default(BannerStatusEnum::PUBLISHED->value),
                Repeater::make('images')
                    ->label(trans('banner.images'))
                    ->relationship('images')
                    ->schema([
                        FileUpload::make('path')
                            ->label(trans('banner.path'))
                            ->image()
                            ->nullable()
                            ->columns(1)
                            ->columnSpanFull(),
                        Toggle::make('is_featured')
                            ->label(trans('banner.is_featured'))
                            ->reactive(),
                        TextInput::make('alt_text')
                            ->label(trans('banner.alt_text')),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('position')
                    ->label(trans('banner.position'))
                    ->searchable(),
                TextColumn::make('heading')
                    ->label(trans('banner.heading'))
                    ->limit(30)
                    ->wrap()
                    ->searchable(),
                ImageColumn::make('featuredImage.path')
                    ->label(trans('banner.featured'))
                    ->square(),
                TextColumn::make('url')
                    ->label(trans('banner.url'))
                    ->limit(30),
                TextColumn::make('sort')
                    ->label(trans('banner.sort'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(trans('banner.status'))
                    ->getStateUsing(fn (Banner $record): string => $record->status->label())
                    ->color(fn (Banner $record): string => $record->status->color())
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(trans('banner.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(trans('banner.updated_at'))
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
            'index' => ListBanners::route('/'),
            'create' => CreateBanner::route('/create'),
            'edit' => EditBanner::route('/{record}/edit'),
        ];
    }
}
