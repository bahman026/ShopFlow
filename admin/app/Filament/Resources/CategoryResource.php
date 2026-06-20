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

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationGroup(): ?string
    {
        return trans('category.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return trans('category.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('category.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('heading')
                    ->label(trans('category.heading'))
                    ->required()
                    ->live(onBlur: true)
                    ->maxLength(255)
                    ->afterStateUpdated(function (string $operation, ?string $state, Set $set): void {
                        if ($operation === 'create') {
                            $set('slug', Str::slug($state));
                        }
                    }),
                TextInput::make('slug')
                    ->label(trans('category.slug'))
                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->maxLength(255)
                    ->unique(Category::class, 'slug', ignoreRecord: true),
                TextInput::make('title')
                    ->label(trans('category.title'))
                    ->maxLength(255),
                TinyEditor::make('content')
                    ->label(trans('category.content'))
                    ->columnSpanFull()
                    ->required(),
                Textarea::make('description')
                    ->label(trans('category.description'))
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('canonical')
                    ->label(trans('category.canonical'))
                    ->maxLength(255),
                Select::make('parent_id')
                    ->label(trans('category.parent_id'))
                    ->options(function () {
                        return Category::active()
                            ->get()
                            ->pluck('heading', 'id');
                    }),
                Toggle::make('no_index')
                    ->label(trans('category.no_index'))
                    ->required(),
                Fieldset::make('image')
                    ->label(trans('category.image'))
                    ->relationship('image')
                    ->schema([
                        FileUpload::make('path')
                            ->label(trans('category.path'))
                            ->image()
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
                    ->label(trans('category.status'))
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
                    ->label(trans('category.heading'))
                    ->limit(30)
                    ->wrap(),
                TextColumn::make('description')
                    ->label(trans('category.description'))
                    ->limit(30)
                    ->wrap(),
                TextColumn::make('content')
                    ->label(trans('category.content'))
                    ->limit(60),
                TextColumn::make('status')
                    ->label(trans('category.status'))
                    ->getStateUsing(fn (Category $record) => $record->status->label())
                    ->color(fn (Category $record): string => $record->status->color())
                    ->sortable(),
                IconColumn::make('no_index')
                    ->label(trans('category.no_index'))
                    ->boolean(),
                TextColumn::make('parent_id')
                    ->label(trans('category.parent_id'))
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
                    ->label(trans('category.image'))
                    ->getStateUsing(function (Category $record) {
                        /** @var Image|null $image */
                        $image = $record->image;

                        return $image?->path;
                    }),
                TextColumn::make('created_at')
                    ->label(trans('category.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(trans('category.updated_at'))
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
