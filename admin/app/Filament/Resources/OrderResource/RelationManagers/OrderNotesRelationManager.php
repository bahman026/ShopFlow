<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderNotesRelationManager extends RelationManager
{
    protected static string $relationship = 'orderNotes';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('content')
            ->columns([
                TextColumn::make('user.email')
                    ->label(trans('order_note.user_id')),
                TextColumn::make('content')
                    ->label(trans('order_note.content'))
                    ->limit(50)
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label(trans('order_note.created_at'))
                    ->dateTime(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
