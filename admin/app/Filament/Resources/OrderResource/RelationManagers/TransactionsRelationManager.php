<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Enums\TransactionPortEnum;
use App\Enums\TransactionStatusEnum;
use App\Models\Transaction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label(trans('transaction.user_id'))
                    ->relationship('user', 'email')
                    ->searchable()
                    ->preload()
                    ->native(false),
                Select::make('port')
                    ->label(trans('transaction.port'))
                    ->options(TransactionPortEnum::options())
                    ->native(false),
                Select::make('status')
                    ->label(trans('transaction.status'))
                    ->required()
                    ->options(TransactionStatusEnum::options())
                    ->default(TransactionStatusEnum::PENDING->value),
                TextInput::make('amount')
                    ->label(trans('transaction.amount'))
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('ref_id')
                    ->label(trans('transaction.ref_id'))
                    ->maxLength(255),
                DateTimePicker::make('paid_at')
                    ->label(trans('transaction.paid_at')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('port')
                    ->label(trans('transaction.port'))
                    ->badge()
                    ->getStateUsing(fn (Transaction $record): ?string => $record->port?->label())
                    ->color(fn (Transaction $record): string => $record->port?->color() ?? 'gray'),
                TextColumn::make('status')
                    ->label(trans('transaction.status'))
                    ->badge()
                    ->getStateUsing(fn (Transaction $record): string => $record->status->label())
                    ->color(fn (Transaction $record): string => $record->status->color()),
                TextColumn::make('amount')
                    ->label(trans('transaction.amount'))
                    ->numeric(),
                TextColumn::make('paid_at')
                    ->label(trans('transaction.paid_at'))
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
