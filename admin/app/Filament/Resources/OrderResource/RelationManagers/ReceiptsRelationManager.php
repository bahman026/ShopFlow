<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Enums\ReceiptTypeEnum;
use App\Models\Receipt;
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

class ReceiptsRelationManager extends RelationManager
{
    protected static string $relationship = 'receipts';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label(trans('receipt.user_id'))
                    ->relationship('user', 'email')
                    ->searchable()
                    ->preload()
                    ->native(false),
                Select::make('type')
                    ->label(trans('receipt.type'))
                    ->required()
                    ->options(ReceiptTypeEnum::options())
                    ->default(ReceiptTypeEnum::RECEIPT->value),
                TextInput::make('amount')
                    ->label(trans('receipt.amount'))
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('tracking_code')
                    ->label(trans('receipt.tracking_code'))
                    ->maxLength(255),
                DateTimePicker::make('paid_at')
                    ->label(trans('receipt.paid_at')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('type')
                    ->label(trans('receipt.type'))
                    ->badge()
                    ->getStateUsing(fn (Receipt $record): string => $record->type->label())
                    ->color(fn (Receipt $record): string => $record->type->color()),
                TextColumn::make('amount')
                    ->label(trans('receipt.amount'))
                    ->numeric(),
                TextColumn::make('paid_at')
                    ->label(trans('receipt.paid_at'))
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
