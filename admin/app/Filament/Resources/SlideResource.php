<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SlideResource\Pages\CreateSlide;
use App\Filament\Resources\SlideResource\Pages\EditSlide;
use App\Filament\Resources\SlideResource\Pages\ListSlides;
use App\Models\Slide;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SlideResource extends Resource
{
    protected static ?string $model = Slide::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Content';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('slider_id')
                    ->relationship('slider', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('The slider this slide belongs to.'),
                TextInput::make('heading')
                    ->nullable()
                    ->maxLength(255)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Title or alt text of the slide image shown to customers.'),
                TextInput::make('label')
                    ->nullable()
                    ->maxLength(255)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Optional secondary text shown on the slide, e.g. a subtitle or call-to-action label.'),
                TextInput::make('url')
                    ->url()
                    ->nullable()
                    ->maxLength(255)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('The link the slide points to when clicked.'),
                TextInput::make('order')
                    ->numeric()
                    ->default(0)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Display order within the slider. Lower numbers appear first.'),
                Fieldset::make('Image')
                    ->relationship('image')
                    ->schema([
                        FileUpload::make('path')
                            ->nullable()
                            ->columnSpanFull(),
                        TextInput::make('alt_text')
                            ->nullable()
                            ->maxLength(255),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('slider.name')
                    ->label('Slider')
                    ->limit(30)
                    ->wrap()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('heading')
                    ->limit(30)
                    ->wrap()
                    ->searchable(),
                ImageColumn::make('image.path')
                    ->label('Image')
                    ->square(),
                TextColumn::make('url')
                    ->limit(30),
                TextColumn::make('order')
                    ->numeric()
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSlides::route('/'),
            'create' => CreateSlide::route('/create'),
            'edit' => EditSlide::route('/{record}/edit'),
        ];
    }
}
