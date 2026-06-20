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

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return trans('slide.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return trans('slide.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('slide.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('slider_id')
                    ->label(trans('slide.slider_id'))
                    ->relationship('slider', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('slide.slider_id_hint')),
                TextInput::make('heading')
                    ->label(trans('slide.heading'))
                    ->nullable()
                    ->maxLength(255)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('slide.heading_hint')),
                TextInput::make('label')
                    ->label(trans('slide.label_field'))
                    ->nullable()
                    ->maxLength(255)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('slide.label_field_hint')),
                TextInput::make('url')
                    ->label(trans('slide.url'))
                    ->url()
                    ->nullable()
                    ->maxLength(255)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('slide.url_hint')),
                TextInput::make('order')
                    ->label(trans('slide.order'))
                    ->numeric()
                    ->default(0)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('slide.order_hint')),
                Fieldset::make(trans('slide.image'))
                    ->relationship('image')
                    ->schema([
                        FileUpload::make('path')
                            ->label(trans('slide.path'))
                            ->image()
                            ->nullable()
                            ->columnSpanFull(),
                        TextInput::make('alt_text')
                            ->label(trans('slide.alt_text'))
                            ->nullable()
                            ->maxLength(255),
                    ])
                    ->mutateRelationshipDataBeforeSaveUsing(function (array $data, Slide $record): array {
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
            ->columns([
                TextColumn::make('slider.name')
                    ->label(trans('slide.slider'))
                    ->limit(30)
                    ->wrap()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('heading')
                    ->label(trans('slide.heading'))
                    ->limit(30)
                    ->wrap()
                    ->searchable(),
                ImageColumn::make('image.path')
                    ->label(trans('slide.image'))
                    ->square(),
                TextColumn::make('url')
                    ->label(trans('slide.url'))
                    ->limit(30),
                TextColumn::make('order')
                    ->label(trans('slide.order'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(trans('slide.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(trans('slide.updated_at'))
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
