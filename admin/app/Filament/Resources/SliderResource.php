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

    protected static string | \UnitEnum | null $navigationGroup = 'Content';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-film';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('A human-readable label for this slider, e.g. "Home Page Main Slider". Not shown on the frontend.'),
                TextInput::make('position')
                    ->required()
                    ->maxLength(255)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('The position key used by the frontend to fetch this slider, e.g. "home-main". Must be unique per placement.'),
                Select::make('status')
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
                    ->limit(30)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('position')
                    ->searchable(),
                TextColumn::make('slides_count')
                    ->label('Slides')
                    ->counts('slides')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->getStateUsing(fn (Slider $record): string => $record->status->label())
                    ->color(fn (Slider $record): string => $record->status->color())
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
            'index' => ListSliders::route('/'),
            'create' => CreateSlider::route('/create'),
            'edit' => EditSlider::route('/{record}/edit'),
        ];
    }
}
