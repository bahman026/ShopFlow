<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use App\Enums\BrandStatusEnum;
use App\Filament\Resources\BrandResource\Pages\CreateBrand;
use App\Filament\Resources\BrandResource\Pages\EditBrand;
use App\Filament\Resources\BrandResource\Pages\ListBrands;
use App\Models\Brand;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationGroup(): ?string
    {
        return trans('brand.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return trans('brand.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('brand.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('heading')
                    ->label(trans('brand.heading'))
                    ->required()
                    ->live(onBlur: true)
                    ->maxLength(255)
                    ->afterStateUpdated(function (string $operation, ?string $state, Set $set): void {
                        if ($operation === 'create') {
                            $set('slug', Str::slug($state));
                        }
                    }),
                TextInput::make('slug')
                    ->label(trans('brand.slug'))
                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->maxLength(255)
                    ->unique(Brand::class, 'slug', ignoreRecord: true),

                TinyEditor::make('content')
                    ->label(trans('brand.content'))
                    ->columnSpanFull(),
                TextInput::make('title')
                    ->label(trans('brand.title'))
                    ->maxLength(255),
                TextInput::make('description')
                    ->label(trans('brand.description'))
                    ->maxLength(255),
                Toggle::make('no_index')
                    ->label(trans('brand.no_index'))
                    ->required(),
                TextInput::make('canonical')
                    ->label(trans('brand.canonical'))
                    ->maxLength(255),
                Fieldset::make('image')
                    ->label(trans('brand.image'))
                    ->relationship('image')
                    ->schema([
                        FileUpload::make('path')
                            ->label(trans('brand.path'))
                            ->image()
                            ->nullable()
                            ->columns(1)
                            ->columnSpanFull(),
                    ])
                    ->mutateRelationshipDataBeforeSaveUsing(function (array $data, Brand $record) {
                        if ($data['path'] === null) {
                            $record->image->delete();
                        }

                        return $data;
                    })
                    ->columnSpanFull(),
                Select::make('status')
                    ->label(trans('brand.status'))
                    ->required()
                    ->options(BrandStatusEnum::options())
                    ->default(BrandStatusEnum::ACTIVE->value),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('heading')
                    ->label(trans('brand.heading'))
                    ->limit(30)
                    ->wrap(),
                TextColumn::make('slug')
                    ->label(trans('brand.slug'))
                    ->limit(30)
                    ->wrap(),
                TextColumn::make('title')
                    ->label(trans('brand.title'))
                    ->limit(30)
                    ->wrap(),
                TextColumn::make('description')
                    ->label(trans('brand.description'))
                    ->limit(30)
                    ->wrap(),
                IconColumn::make('no_index')
                    ->label(trans('brand.no_index'))
                    ->boolean(),
                ImageColumn::make('image.path')
                    ->label(trans('brand.image')),
                TextColumn::make('status')
                    ->label(trans('brand.status'))
                    ->getStateUsing(fn (Brand $record) => $record->status->label())
                    ->color(fn (Brand $record): string => $record->status->color())
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(trans('brand.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(trans('brand.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
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
            'index' => ListBrands::route('/'),
            'create' => CreateBrand::route('/create'),
            'edit' => EditBrand::route('/{record}/edit'),
        ];
    }
}
