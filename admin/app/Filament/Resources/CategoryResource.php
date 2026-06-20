<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use App\Enums\CategoryStatusEnum;
use App\Filament\Resources\CategoryResource\Pages\CreateCategory;
use App\Filament\Resources\CategoryResource\Pages\EditCategory;
use App\Filament\Resources\CategoryResource\Pages\ListCategories;
use App\Models\Category;
use App\Models\Image;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Catalog';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('heading')
                    ->required()
                    ->live(onBlur: true)
                    ->maxLength(255)
                    ->afterStateUpdated(function (string $operation, ?string $state, Set $set): void {
                        if ($operation === 'create') {
                            $set('slug', Str::slug($state));
                        }
                    }),
                TextInput::make('slug')
                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->maxLength(255)
                    ->unique(Category::class, 'slug', ignoreRecord: true),
                TextInput::make('title')
                    ->maxLength(255),
                TinyEditor::make('content')
                    ->columnSpanFull()
                    ->required(),
                Textarea::make('description')
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('canonical')
                    ->maxLength(255),
                Select::make('parent_id')
                    ->options(function () {
                        return Category::active()
                            ->get()
                            ->pluck('heading', 'id');
                    }),
                Toggle::make('no_index')
                    ->required(),
                Fieldset::make('image')
                    ->relationship('image')
                    ->schema([
                        FileUpload::make('path')
                            ->nullable()
                            ->columns(1)
                            ->columnSpanFull(),
                    ])
                    ->mutateRelationshipDataBeforeSaveUsing(function (array $data, Category $record) {
                        if ($data['path'] === null) {
                            $record->image->delete();
                        }

                        return $data;
                    })
                    ->columnSpanFull(),
                Select::make('status')
                    ->required()
                    ->options(CategoryStatusEnum::options())
                    ->default(CategoryStatusEnum::ACTIVE->value),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('heading')
                    ->limit(30)
                    ->wrap(),
                TextColumn::make('description')
                    ->limit(30)
                    ->wrap(),
                TextColumn::make('content')
                    ->limit(60),
                TextColumn::make('status')
                    ->getStateUsing(fn (Category $record) => $record->status->label())
                    ->color(fn (Category $record): string => $record->status->color())
                    ->sortable(),
                IconColumn::make('no_index')
                    ->boolean(),
                TextColumn::make('parent_id')
                    ->getStateUsing(function (Category $record) {
                        if ($record->parent_id) {
                            return "($record->parent_id) $record->title";
                        }

                        return null;
                    })
                    ->limit(20)
                    ->wrap()
                    ->numeric()
                    ->sortable(),
                ImageColumn::make('images')
                    ->getStateUsing(function (Category $record) {
                        /** @var Image|null $image */
                        $image = $record->image;

                        return $image?->path;
                    }),

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
            'index' => ListCategories::route('/'),
            'create' => CreateCategory::route('/create'),
            'edit' => EditCategory::route('/{record}/edit'),
        ];
    }
}
