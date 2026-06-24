<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\TransactionPortEnum;
use App\Enums\TransactionStatusEnum;
use App\Filament\Resources\TransactionResource\Pages\CreateTransaction;
use App\Filament\Resources\TransactionResource\Pages\EditTransaction;
use App\Filament\Resources\TransactionResource\Pages\ListTransactions;
use App\Models\Transaction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-credit-card';

    public static function getNavigationGroup(): ?string
    {
        return trans('transaction.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return trans('transaction.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('transaction.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label(trans('transaction.user_id'))
                    ->relationship('user', 'email')
                    ->searchable()
                    ->preload()
                    ->native(false),
                Select::make('order_id')
                    ->label(trans('transaction.order_id'))
                    ->relationship('order', 'id')
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
                TextInput::make('transaction_id')
                    ->label(trans('transaction.transaction_id'))
                    ->maxLength(255),
                DateTimePicker::make('paid_at')
                    ->label(trans('transaction.paid_at')),
                TextInput::make('ip')
                    ->label(trans('transaction.ip'))
                    ->maxLength(255),
                TextInput::make('result_code')
                    ->label(trans('transaction.result_code'))
                    ->maxLength(255),
                TextInput::make('result_message')
                    ->label(trans('transaction.result_message'))
                    ->maxLength(255),
                Textarea::make('description')
                    ->label(trans('transaction.description'))
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
                TextColumn::make('user.email')
                    ->label(trans('transaction.user_id'))
                    ->searchable(),
                TextColumn::make('order_id')
                    ->label(trans('transaction.order_id'))
                    ->sortable(),
                TextColumn::make('port')
                    ->label(trans('transaction.port'))
                    ->badge()
                    ->getStateUsing(fn (Transaction $record): ?string => $record->port?->label())
                    ->color(fn (Transaction $record): string => $record->port?->color() ?? 'gray'),
                TextColumn::make('amount')
                    ->label(trans('transaction.amount'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(trans('transaction.status'))
                    ->badge()
                    ->getStateUsing(fn (Transaction $record): string => $record->status->label())
                    ->color(fn (Transaction $record): string => $record->status->color())
                    ->sortable(),
                TextColumn::make('paid_at')
                    ->label(trans('transaction.paid_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(trans('transaction.created_at'))
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTransactions::route('/'),
            'create' => CreateTransaction::route('/create'),
            'edit' => EditTransaction::route('/{record}/edit'),
        ];
    }
}
