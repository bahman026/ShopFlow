<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\WishlistResource\Pages\CreateWishlist;
use App\Filament\Resources\WishlistResource\Pages\ListWishlists;
use App\Models\Wishlist;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WishlistResource extends Resource
{
    protected static ?string $model = Wishlist::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-heart';

    protected static ?int $navigationSort = 9;

    public static function getNavigationGroup(): ?string
    {
        return trans('wishlist.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return trans('wishlist.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('wishlist.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label(trans('wishlist.user_id'))
                    ->relationship('user', 'email')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('wishlist.user_id_hint')),
                Select::make('product_id')
                    ->label(trans('wishlist.product_id'))
                    ->relationship('product', 'heading')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('wishlist.product_id_hint')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.email')
                    ->label(trans('wishlist.user'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.heading')
                    ->label(trans('wishlist.product'))
                    ->limit(40)
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label(trans('wishlist.saved_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                DeleteAction::make(),
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
            'index' => ListWishlists::route('/'),
            'create' => CreateWishlist::route('/create'),
        ];
    }
}
