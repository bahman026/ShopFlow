<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\BrandStatusEnum;
use App\Filament\Resources\BrandResource\Pages;
use App\Models\Brand;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use FilamentTiptapEditor\Enums\TiptapOutput;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Support\Str;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static ?string $navigationGroup = 'Product';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('heading')
                    ->required()
                    ->live(onBlur: true)
                    ->maxLength(255)
                    ->afterStateUpdated(function (string $operation, ?string $state, Forms\Set $set): void {
                        if ($operation === 'create') {
                            $set('slug', Str::slug($state));
                        }
                    }),
                Forms\Components\TextInput::make('slug')
                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->maxLength(255)
                    ->unique(Brand::class, 'slug', ignoreRecord: true),

                TiptapEditor::make('content')
                    ->output(TiptapOutput::Html) // optional, change the format for saved data, default is html
                    ->columnSpanFull()
                    ->extraInputAttributes(['style' => 'min-height: 12rem;']),
                Forms\Components\TextInput::make('title')
                    ->maxLength(255),
                Forms\Components\TextInput::make('description')
                    ->maxLength(255),
                Forms\Components\Toggle::make('no_index')
                    ->required(),
                Forms\Components\TextInput::make('canonical')
                    ->maxLength(255),
                Forms\Components\Fieldset::make('image')
                    ->relationship('image')
                    ->schema([
                        Forms\Components\FileUpload::make('path')
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
                Forms\Components\Select::make('status')
                    ->required()
                    ->options(BrandStatusEnum::options())
                    ->default(BrandStatusEnum::ACTIVE->value),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('heading')
                    ->limit(30)
                    ->wrap(),
                Tables\Columns\TextColumn::make('slug')
                    ->limit(30)
                    ->wrap(),
                Tables\Columns\TextColumn::make('title')
                    ->limit(30)
                    ->wrap(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(30)
                    ->wrap(),
                Tables\Columns\IconColumn::make('no_index')
                    ->boolean(),
                Tables\Columns\ImageColumn::make('image.path'),
                Tables\Columns\TextColumn::make('status')
                    ->getStateUsing(fn (Brand $record) => $record->status->label())
                    ->color(fn (Brand $record): string => $record->status->color())
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'edit' => Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}
