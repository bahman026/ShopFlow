<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\PermissionGroupEnum;
use App\Enums\SliderPositionEnum;
use App\Enums\SliderStatusEnum;
use App\Filament\Resources\SliderResource\Pages\CreateSlider;
use App\Filament\Resources\SliderResource\Pages\EditSlider;
use App\Filament\Resources\SliderResource\Pages\ListSliders;
use App\Models\Slider;
use App\Traits\AuthorizesWithPermissions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SliderResource extends Resource
{
    use AuthorizesWithPermissions;

    public static function permissionGroup(): PermissionGroupEnum
    {
        return PermissionGroupEnum::CONTENT;
    }

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
                // Radio rather than a dropdown: each placement needs a line of
                // explanation, and there are only four of them.
                Radio::make('position')
                    ->label(trans('slider.position'))
                    ->required()
                    ->options(SliderPositionEnum::options())
                    ->descriptions(SliderPositionEnum::descriptions())
                    ->live()
                    // Filament wraps each component in a wire:partial, and the
                    // guide's own state never changes — so without this the
                    // browser keeps the stale wireframe even though the server
                    // renders the right one.
                    ->partiallyRenderComponentsAfterStateUpdated(['position_guide'])
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('slider.position_hint')),
                // UI only — never written to the model.
                ViewField::make('position_guide')
                    ->label(trans('position_guide.label'))
                    ->helperText(trans('position_guide.hint'))
                    ->view('filament.forms.position-guide')
                    ->viewData(['kind' => 'slider'])
                    ->dehydrated(false)
                    ->columnSpanFull(),
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
                    ->formatStateUsing(fn (string $state): string => SliderPositionEnum::tryFrom($state)?->label() ?? $state)
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
