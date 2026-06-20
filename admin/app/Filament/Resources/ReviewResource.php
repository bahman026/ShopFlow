<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\ReviewStatusEnum;
use App\Filament\Resources\ReviewResource\Pages\CreateReview;
use App\Filament\Resources\ReviewResource\Pages\EditReview;
use App\Filament\Resources\ReviewResource\Pages\ListReviews;
use App\Models\Review;
use App\Models\Variety;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Content';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?int $navigationSort = 8;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('heading')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('The review title written by the user (e.g. "Great product!").'),
                Textarea::make('content')
                    ->required()
                    ->rows(5)
                    ->columnSpanFull()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('The full review text.'),
                Select::make('product_id')
                    ->relationship('product', 'heading')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->native(false)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('The product this review is about.'),
                Select::make('variety_id')
                    ->label('Variety (optional)')
                    ->options(function (Get $get): array {
                        $productId = $get('product_id');
                        if (! $productId) {
                            return [];
                        }

                        return Variety::query()
                            ->where('product_id', $productId)
                            ->get()
                            ->mapWithKeys(fn (Variety $v): array => [$v->id => $v->attribute_value ?? "Variety #{$v->id}"])
                            ->toArray();
                    })
                    ->nullable()
                    ->native(false)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('The specific variety (e.g. size/color) the user purchased, if known.'),
                Select::make('user_id')
                    ->relationship('user', 'email')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->native(false)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('The user who wrote this review. Leave empty for anonymous/admin-entered reviews.'),
                Select::make('parent_id')
                    ->label('Reply to')
                    ->options(function (?Review $record): array {
                        return Review::query()
                            ->whereNull('parent_id')
                            ->when($record?->id, fn (Builder $q): Builder => $q->where('id', '!=', $record->id))
                            ->get()
                            ->mapWithKeys(fn (Review $r): array => [$r->id => "#{$r->id} — {$r->heading}"])
                            ->toArray();
                    })
                    ->nullable()
                    ->searchable()
                    ->native(false)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Set this to make the review a reply to another review.'),
                Select::make('status')
                    ->required()
                    ->options(ReviewStatusEnum::options())
                    ->default(ReviewStatusEnum::PENDING->value)
                    ->native(false)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Pending reviews are hidden from the storefront until approved.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.heading')
                    ->label('Product')
                    ->limit(30)
                    ->searchable(),
                TextColumn::make('heading')
                    ->limit(40)
                    ->searchable(),
                TextColumn::make('user.email')
                    ->label('User')
                    ->placeholder('Anonymous')
                    ->searchable(),
                TextColumn::make('parent_id')
                    ->label('Reply to')
                    ->placeholder('—')
                    ->formatStateUsing(fn (?int $state): string => $state ? "#{$state}" : '—'),
                TextColumn::make('status')
                    ->getStateUsing(fn (Review $record): string => $record->status->label())
                    ->color(fn (Review $record): string => $record->status->color())
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
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
            'index' => ListReviews::route('/'),
            'create' => CreateReview::route('/create'),
            'edit' => EditReview::route('/{record}/edit'),
        ];
    }
}
