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

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?int $navigationSort = 8;

    public static function getNavigationGroup(): ?string
    {
        return trans('review.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return trans('review.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('review.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('heading')
                    ->label(trans('review.heading'))
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('review.heading_hint')),
                Textarea::make('content')
                    ->label(trans('review.content'))
                    ->required()
                    ->rows(5)
                    ->columnSpanFull()
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('review.content_hint')),
                Select::make('product_id')
                    ->label(trans('review.product_id'))
                    ->relationship('product', 'heading')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->native(false)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('review.product_id_hint')),
                Select::make('variety_id')
                    ->label(trans('review.variety_id'))
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
                    ->hintIconTooltip(trans('review.variety_id_hint')),
                Select::make('user_id')
                    ->label(trans('review.user_id'))
                    ->relationship('user', 'email')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->native(false)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('review.user_id_hint')),
                Select::make('parent_id')
                    ->label(trans('review.parent_id'))
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
                    ->hintIconTooltip(trans('review.parent_id_hint')),
                Select::make('status')
                    ->label(trans('review.status'))
                    ->required()
                    ->options(ReviewStatusEnum::options())
                    ->default(ReviewStatusEnum::PENDING->value)
                    ->native(false)
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('review.status_hint')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.heading')
                    ->label(trans('review.product'))
                    ->limit(30)
                    ->searchable(),
                TextColumn::make('heading')
                    ->label(trans('review.heading'))
                    ->limit(40)
                    ->searchable(),
                TextColumn::make('user.email')
                    ->label(trans('review.user'))
                    ->placeholder(trans('review.anonymous'))
                    ->searchable(),
                TextColumn::make('parent_id')
                    ->label(trans('review.parent_id'))
                    ->placeholder('—')
                    ->formatStateUsing(fn (?int $state): string => $state ? "#{$state}" : '—'),
                TextColumn::make('status')
                    ->label(trans('review.status'))
                    ->getStateUsing(fn (Review $record): string => $record->status->label())
                    ->color(fn (Review $record): string => $record->status->color())
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(trans('review.created_at'))
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
