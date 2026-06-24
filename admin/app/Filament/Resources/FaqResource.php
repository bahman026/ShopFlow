<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\FaqResource\Pages\CreateFaq;
use App\Filament\Resources\FaqResource\Pages\EditFaq;
use App\Filament\Resources\FaqResource\Pages\ListFaqs;
use App\Models\Faq;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?int $navigationSort = 7;

    public static function getNavigationGroup(): ?string
    {
        return trans('faq.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return trans('faq.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('faq.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('heading')
                    ->label(trans('faq.heading'))
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('faq.heading_hint')),
                Textarea::make('content')
                    ->label(trans('faq.content'))
                    ->required()
                    ->rows(5)
                    ->columnSpanFull()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('faq.content_hint')),
                TextInput::make('order')
                    ->label(trans('faq.order'))
                    ->numeric()
                    ->default(0)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('faq.order_hint')),
                TextInput::make('position')
                    ->label(trans('faq.position'))
                    ->nullable()
                    ->maxLength(100)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('faq.position_hint')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('heading')
                    ->label(trans('faq.heading'))
                    ->limit(50)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('order')
                    ->label(trans('faq.order'))
                    ->sortable(),
                TextColumn::make('position')
                    ->label(trans('faq.position'))
                    ->placeholder(trans('faq.position_placeholder'))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(trans('faq.created_at'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(trans('faq.updated_at'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('order')
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
            'index' => ListFaqs::route('/'),
            'create' => CreateFaq::route('/create'),
            'edit' => EditFaq::route('/{record}/edit'),
        ];
    }
}
