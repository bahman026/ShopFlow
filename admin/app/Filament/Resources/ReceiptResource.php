<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\ReceiptTypeEnum;
use App\Filament\Resources\ReceiptResource\Pages\CreateReceipt;
use App\Filament\Resources\ReceiptResource\Pages\EditReceipt;
use App\Filament\Resources\ReceiptResource\Pages\ListReceipts;
use App\Models\Receipt;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReceiptResource extends Resource
{
    protected static ?string $model = Receipt::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';

    public static function getNavigationGroup(): ?string
    {
        return trans('receipt.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return trans('receipt.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('receipt.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label(trans('receipt.user_id'))
                    ->relationship('user', 'email')
                    ->searchable()
                    ->preload()
                    ->native(false),
                Select::make('order_id')
                    ->label(trans('receipt.order_id'))
                    ->relationship('order', 'id')
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
                TextInput::make('destination_name')
                    ->label(trans('receipt.destination_name'))
                    ->maxLength(255),
                TextInput::make('destination_bank')
                    ->label(trans('receipt.destination_bank'))
                    ->maxLength(255),
                TextInput::make('end_of_card_number')
                    ->label(trans('receipt.end_of_card_number'))
                    ->maxLength(4),
                TextInput::make('card_id')
                    ->label(trans('receipt.card_id'))
                    ->numeric(),
                Toggle::make('is_paya')
                    ->label(trans('receipt.is_paya')),
                Textarea::make('description')
                    ->label(trans('receipt.description'))
                    ->columnSpanFull(),
                Fieldset::make(trans('receipt.image'))
                    ->relationship('image')
                    ->schema([
                        FileUpload::make('path')
                            ->label(trans('receipt.path'))
                            ->image()
                            ->nullable()
                            ->columnSpanFull(),
                        TextInput::make('alt_text')
                            ->label(trans('receipt.alt_text'))
                            ->nullable()
                            ->maxLength(255),
                    ])
                    ->mutateRelationshipDataBeforeSaveUsing(function (array $data, Receipt $record): array {
                        if (empty($data['path'])) {
                            $record->image?->delete();
                        }

                        return $data;
                    })
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
                    ->label(trans('receipt.user_id'))
                    ->searchable(),
                TextColumn::make('order_id')
                    ->label(trans('receipt.order_id'))
                    ->sortable(),
                TextColumn::make('type')
                    ->label(trans('receipt.type'))
                    ->badge()
                    ->getStateUsing(fn (Receipt $record): string => $record->type->label())
                    ->color(fn (Receipt $record): string => $record->type->color())
                    ->sortable(),
                TextColumn::make('amount')
                    ->label(trans('receipt.amount'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('paid_at')
                    ->label(trans('receipt.paid_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(trans('receipt.created_at'))
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
            'index' => ListReceipts::route('/'),
            'create' => CreateReceipt::route('/create'),
            'edit' => EditReceipt::route('/{record}/edit'),
        ];
    }
}
