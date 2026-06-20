<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\ShippingMethodForEnum;
use App\Filament\Resources\ShippingMethodResource\Pages\CreateShippingMethod;
use App\Filament\Resources\ShippingMethodResource\Pages\EditShippingMethod;
use App\Filament\Resources\ShippingMethodResource\Pages\ListShippingMethods;
use App\Models\ShippingMethod;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShippingMethodResource extends Resource
{
    protected static ?string $model = ShippingMethod::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Logistics';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('shipping_line_id')
                    ->relationship('shippingLine', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('The carrier this method belongs to (e.g. Post Office, Tap30).'),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('The service name shown to admins (e.g. "Standard Post", "Overnight Express").'),
                TextInput::make('type')
                    ->nullable()
                    ->maxLength(100)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Service category, e.g. Express, Economy, Special.'),
                TextInput::make('min_count')
                    ->numeric()
                    ->nullable()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Minimum item quantity required to use this method. Leave empty for no minimum.'),
                TextInput::make('min_amount')
                    ->numeric()
                    ->nullable()
                    ->prefix('تومان')
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Minimum order amount required to use this method. Leave empty for no minimum.'),
                Select::make('for')
                    ->required()
                    ->options(ShippingMethodForEnum::options())
                    ->default(ShippingMethodForEnum::CUSTOMER->value)
                    ->native(false)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Who can use this method — Customer, Partner, or Employee.'),
                DateTimePicker::make('disable_from')
                    ->nullable()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Start of the period when this method is unavailable (e.g. holiday blackout).'),
                DateTimePicker::make('disable_to')
                    ->nullable()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('End of the unavailability period.'),
                Toggle::make('status')
                    ->default(true)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('When off, this method is hidden from all users.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('shippingLine.name')
                    ->label('Carrier')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->placeholder('—'),
                TextColumn::make('for')
                    ->getStateUsing(fn (ShippingMethod $record): string => $record->for->label())
                    ->color(fn (ShippingMethod $record): string => $record->for->color()),
                TextColumn::make('min_amount')
                    ->money()
                    ->placeholder('No minimum'),
                IconColumn::make('status')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
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
            'index' => ListShippingMethods::route('/'),
            'create' => CreateShippingMethod::route('/create'),
            'edit' => EditShippingMethod::route('/{record}/edit'),
        ];
    }
}
