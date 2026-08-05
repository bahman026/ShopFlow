<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\BannerPositionEnum;
use App\Enums\HomeSectionTypeEnum;
use App\Enums\SliderPositionEnum;
use App\Filament\Resources\HomeSectionResource\Pages\CreateHomeSection;
use App\Filament\Resources\HomeSectionResource\Pages\EditHomeSection;
use App\Filament\Resources\HomeSectionResource\Pages\ListHomeSections;
use App\Models\HomeSection;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HomeSectionResource extends Resource
{
    protected static ?string $model = HomeSection::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-squares-2x2';

    public static function getNavigationGroup(): ?string
    {
        return trans('home_section.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return trans('home_section.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('home_section.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->label(trans('home_section.type'))
                    ->required()
                    ->live()
                    ->options(HomeSectionTypeEnum::options())
                    ->default(HomeSectionTypeEnum::PRODUCTS->value)
                    ->native(false)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('home_section.type_hint')),

                // Slider/banner sections point at a position (which slider or
                // banner group to show); the options depend on the type.
                Select::make('config.position')
                    ->label(trans('home_section.position'))
                    ->options(fn (Get $get): array => match ($get('type')) {
                        HomeSectionTypeEnum::SLIDER->value => SliderPositionEnum::options(),
                        HomeSectionTypeEnum::BANNERS->value => BannerPositionEnum::options(),
                        default => [],
                    })
                    ->visible(fn (Get $get): bool => in_array($get('type'), [
                        HomeSectionTypeEnum::SLIDER->value,
                        HomeSectionTypeEnum::BANNERS->value,
                    ], true))
                    ->required(fn (Get $get): bool => in_array($get('type'), [
                        HomeSectionTypeEnum::SLIDER->value,
                        HomeSectionTypeEnum::BANNERS->value,
                    ], true))
                    ->native(false),

                // Product rows carry a sort and a heading.
                Select::make('config.sort')
                    ->label(trans('home_section.sort_by'))
                    ->options([
                        'newest' => trans('home_section.sort_newest'),
                        'popular' => trans('home_section.sort_popular'),
                    ])
                    ->visible(fn (Get $get): bool => $get('type') === HomeSectionTypeEnum::PRODUCTS->value)
                    ->required(fn (Get $get): bool => $get('type') === HomeSectionTypeEnum::PRODUCTS->value)
                    ->native(false),
                TextInput::make('title')
                    ->label(trans('home_section.title'))
                    ->maxLength(255)
                    ->visible(fn (Get $get): bool => $get('type') === HomeSectionTypeEnum::PRODUCTS->value)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('home_section.title_hint')),

                Toggle::make('status')
                    ->label(trans('home_section.status'))
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('order')
            ->defaultSort('order')
            ->columns([
                TextColumn::make('order')
                    ->label(trans('home_section.order'))
                    ->sortable(),
                TextColumn::make('type')
                    ->label(trans('home_section.type'))
                    ->getStateUsing(fn (HomeSection $record): string => $record->type->label()),
                TextColumn::make('title')
                    ->label(trans('home_section.title'))
                    ->placeholder('—'),
                IconColumn::make('status')
                    ->label(trans('home_section.status'))
                    ->boolean(),
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

    public static function getPages(): array
    {
        return [
            'index' => ListHomeSections::route('/'),
            'create' => CreateHomeSection::route('/create'),
            'edit' => EditHomeSection::route('/{record}/edit'),
        ];
    }
}
