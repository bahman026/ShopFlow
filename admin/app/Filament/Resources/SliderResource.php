<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\SliderStatusEnum;
use App\Filament\Resources\SliderResource\Pages\CreateSlider;
use App\Filament\Resources\SliderResource\Pages\EditSlider;
use App\Filament\Resources\SliderResource\Pages\ListSliders;
use App\Models\Slider;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SliderResource extends Resource
{
    protected static ?string $model = Slider::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-film';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return trans('slider.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return trans('slider.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('slider.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(trans('slider.name'))
                    ->required()
                    ->maxLength(255)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('slider.name_hint')),
                TextInput::make('position')
                    ->label(trans('slider.position'))
                    ->required()
                    ->maxLength(255)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('slider.position_hint')),
                Select::make('status')
                    ->label(trans('slider.status'))
                    ->required()
                    ->options(SliderStatusEnum::options())
                    ->default(SliderStatusEnum::PUBLISHED->value),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(trans('slider.name'))
                    ->limit(30)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('position')
                    ->label(trans('slider.position'))
                    ->searchable(),
                TextColumn::make('slides_count')
                    ->label(trans('slider.slides_count'))
                    ->counts('slides')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(trans('slider.status'))
                    ->getStateUsing(fn (Slider $record): string => $record->status->label())
                    ->color(fn (Slider $record): string => $record->status->color())
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(trans('slider.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(trans('slider.updated_at'))
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
            'index' => ListSliders::route('/'),
            'create' => CreateSlider::route('/create'),
            'edit' => EditSlider::route('/{record}/edit'),
        ];
    }
}
