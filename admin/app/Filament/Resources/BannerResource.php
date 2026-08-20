<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\BannerPositionEnum;
use App\Enums\BannerStatusEnum;
use App\Enums\PermissionGroupEnum;
use App\Filament\Resources\BannerResource\Pages\CreateBanner;
use App\Filament\Resources\BannerResource\Pages\EditBanner;
use App\Filament\Resources\BannerResource\Pages\ListBanners;
use App\Models\Banner;
use App\Traits\AuthorizesWithPermissions;
use Closure;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BannerResource extends Resource
{
    use AuthorizesWithPermissions;

    public static function permissionGroup(): PermissionGroupEnum
    {
        return PermissionGroupEnum::CONTENT;
    }

    protected static ?string $model = Banner::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-photo';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return trans('banner.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return trans('banner.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('banner.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Radio rather than a dropdown: each placement needs a line of
                // explanation, and there are only three of them.
                Radio::make('position')
                    ->label(trans('banner.position'))
                    ->required()
                    ->options(BannerPositionEnum::options())
                    ->descriptions(BannerPositionEnum::descriptions())
                    ->live()
                    // Filament wraps each component in a wire:partial, and the
                    // guide's own state never changes — so without this the
                    // browser keeps the stale wireframe even though the server
                    // renders the right one.
                    ->partiallyRenderComponentsAfterStateUpdated(['position_guide'])
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('banner.position_hint')),
                // UI only — never written to the model.
                ViewField::make('position_guide')
                    ->label(trans('position_guide.label'))
                    ->helperText(trans('position_guide.hint'))
                    ->view('filament.forms.position-guide')
                    ->viewData(['kind' => 'banner'])
                    ->dehydrated(false)
                    ->columnSpanFull(),
                TextInput::make('heading')
                    ->label(trans('banner.heading'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('url')
                    ->label(trans('banner.url'))
                    ->maxLength(255)
                    // Allow an absolute URL (https://…) or an internal path
                    // (/tags/…, /categories/…) so banners can link to tag
                    // pages. Wrapped in an outer closure so Filament returns
                    // the rule to Laravel instead of evaluating it itself.
                    ->rule(static fn (): Closure => static function (string $attribute, mixed $value, Closure $fail): void {
                        if ($value !== null && $value !== '' && (! is_string($value) || preg_match('#^(https?://|/)#', $value) !== 1)) {
                            $fail(trans('banner.url_invalid'));
                        }
                    })
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip(trans('banner.url_hint')),
                TextInput::make('sort')
                    ->label(trans('banner.sort'))
                    ->numeric()
                    ->nullable(),
                Select::make('status')
                    ->label(trans('banner.status'))
                    ->required()
                    ->options(BannerStatusEnum::options())
                    ->default(BannerStatusEnum::PUBLISHED->value),
                Repeater::make('images')
                    ->label(trans('banner.images'))
                    ->relationship('images')
                    ->schema([
                        FileUpload::make('path')
                            ->label(trans('banner.path'))
                            ->image()
                            ->imageEditor()
                            // Crop to the ratio this position actually renders
                            // at, so a tall or square upload cannot stretch the
                            // storefront layout. Two levels up: repeater item,
                            // then the form.
                            ->imageCropAspectRatio(fn (Get $get): ?string => BannerPositionEnum::tryFrom((string) $get('../../position'))?->aspectRatio())
                            ->helperText(fn (Get $get): string => BannerResource::imageHint(BannerPositionEnum::tryFrom((string) $get('../../position'))))
                            ->nullable()
                            ->columns(1)
                            ->columnSpanFull(),
                        Toggle::make('is_featured')
                            ->label(trans('banner.is_featured'))
                            ->reactive(),
                        TextInput::make('alt_text')
                            ->label(trans('banner.alt_text')),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Tells the admin what to upload: the ratio the slot renders at and the
     * pixel size that stays sharp on a retina screen. Falls back to a nudge to
     * pick a position first, since the ratio depends on it.
     */
    public static function imageHint(?BannerPositionEnum $position): string
    {
        if (! $position instanceof BannerPositionEnum) {
            return trans('banner.path_hint_no_position');
        }

        return trans('banner.path_hint', [
            'ratio' => $position->aspectRatio(),
            'size' => $position->recommendedSize(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('position')
                    ->label(trans('banner.position'))
                    ->formatStateUsing(fn (string $state): string => BannerPositionEnum::tryFrom($state)?->label() ?? $state)
                    ->searchable(),
                TextColumn::make('heading')
                    ->label(trans('banner.heading'))
                    ->limit(30)
                    ->wrap()
                    ->searchable(),
                ImageColumn::make('featuredImage.path')
                    ->label(trans('banner.featured'))
                    ->square(),
                TextColumn::make('url')
                    ->label(trans('banner.url'))
                    ->limit(30),
                TextColumn::make('sort')
                    ->label(trans('banner.sort'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(trans('banner.status'))
                    ->getStateUsing(fn (Banner $record): string => $record->status->label())
                    ->color(fn (Banner $record): string => $record->status->color())
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(trans('banner.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(trans('banner.updated_at'))
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
            'index' => ListBanners::route('/'),
            'create' => CreateBanner::route('/create'),
            'edit' => EditBanner::route('/{record}/edit'),
        ];
    }
}
