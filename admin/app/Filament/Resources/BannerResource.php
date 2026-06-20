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

    protected static string | \UnitEnum | null $navigationGroup = 'Content';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-photo';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('position')
                    ->required()
                    ->maxLength(255),
                TextInput::make('heading')
                    ->required()
                    ->maxLength(255),
                TextInput::make('url')
                    ->url()
                    ->maxLength(255),
                TextInput::make('sort')
                    ->numeric()
                    ->nullable(),
                Select::make('status')
                    ->required()
                    ->options(BannerStatusEnum::options())
                    ->default(BannerStatusEnum::PUBLISHED->value),
                Repeater::make('images')
                    ->relationship('images')
                    ->schema([
                        FileUpload::make('path')
                            ->nullable()
                            ->columns(1)
                            ->columnSpanFull(),
                        Toggle::make('is_featured')
                            ->label('Featured Image')
                            ->reactive(),
                        TextInput::make('alt_text')
                            ->label('Alt Text'),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('position')
                    ->searchable(),
                TextColumn::make('heading')
                    ->limit(30)
                    ->wrap()
                    ->searchable(),
                ImageColumn::make('featuredImage.path')
                    ->label('Featured')
                    ->square(),
                TextColumn::make('url')
                    ->limit(30),
                TextColumn::make('sort')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->getStateUsing(fn (Banner $record): string => $record->status->label())
                    ->color(fn (Banner $record): string => $record->status->color())
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
