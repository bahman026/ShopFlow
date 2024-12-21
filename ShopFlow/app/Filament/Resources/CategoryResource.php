<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\CategoryStatusEnum;
use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use FilamentTiptapEditor\Enums\TiptapOutput;
use FilamentTiptapEditor\TiptapEditor;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationGroup = 'Product';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('heading')
                    ->required(),
                Forms\Components\TextInput::make('slug')
                    ->required(),
                Forms\Components\TextInput::make('title'),
                TiptapEditor::make('content')
                    ->output(TiptapOutput::Html) // optional, change the format for saved data, default is html
                    ->columnSpanFull()
                    ->extraInputAttributes(['style' => 'min-height: 12rem;'])
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('canonical'),
                Forms\Components\Select::make('parent_id')
                    ->options(function () {
                        return Category::active()
                            ->get()
                            ->pluck('heading', 'id');
                    }),
                Forms\Components\Toggle::make('no_index'),

                Forms\Components\Repeater::make('images')
                    ->relationship()
                    ->maxItems(1)
                    ->schema([
                        Forms\Components\FileUpload::make('path')
                            ->previewable(),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->options(CategoryStatusEnum::options()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('heading')
                    ->limit(30)
                    ->wrap(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(30)
                    ->wrap()
                    ->searchable(),
                Tables\Columns\TextColumn::make('content')
                    ->limit(60)
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->getStateUsing(fn (Category $record) => $record->status->label())
                    ->color(fn (Category $record): string => $record->status->color())
                    ->sortable(),
                Tables\Columns\IconColumn::make('no_index')
                    ->boolean(),
                Tables\Columns\TextColumn::make('parent_id')
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
                Tables\Columns\ImageColumn::make('images')
                    ->getStateUsing(function (Category $record) {
                        /** @var \Illuminate\Database\Eloquent\Collection|\App\Models\Image[] $images */
                        $images = $record->images;

                        return $images->first()?->path;
                    }),

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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
