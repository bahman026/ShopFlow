<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\OrderNoteResource\Pages\CreateOrderNote;
use App\Filament\Resources\OrderNoteResource\Pages\EditOrderNote;
use App\Filament\Resources\OrderNoteResource\Pages\ListOrderNotes;
use App\Models\OrderNote;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderNoteResource extends Resource
{
    protected static ?string $model = OrderNote::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-pencil-square';

    public static function getNavigationGroup(): ?string
    {
        return trans('order_note.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return trans('order_note.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('order_note.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('order_id')
                    ->label(trans('order_note.order_id'))
                    ->relationship('order', 'id')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->native(false),
                Select::make('user_id')
                    ->label(trans('order_note.user_id'))
                    ->relationship('user', 'email')
                    ->searchable()
                    ->preload()
                    ->native(false),
                Textarea::make('content')
                    ->label(trans('order_note.content'))
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('order_id')
                    ->label(trans('order_note.order_id'))
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label(trans('order_note.user_id'))
                    ->searchable(),
                TextColumn::make('content')
                    ->label(trans('order_note.content'))
                    ->limit(50)
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label(trans('order_note.created_at'))
                    ->dateTime()
                    ->sortable(),
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrderNotes::route('/'),
            'create' => CreateOrderNote::route('/create'),
            'edit' => EditOrderNote::route('/{record}/edit'),
        ];
    }
}
