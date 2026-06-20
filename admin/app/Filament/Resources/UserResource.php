<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\RolesEnum;
use App\Enums\UserStatusEnum;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\Address;
use App\Models\City;
use App\Models\Province;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user';

    public static function getModelLabel(): string
    {
        return trans('user.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('user.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('first_name')
                    ->label(trans('user.first_name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('last_name')
                    ->label(trans('user.last_name'))
                    ->required()
                    ->maxLength(255),
                Select::make('status')
                    ->label(trans('user.status'))
                    ->required()
                    ->options(UserStatusEnum::options())
                    ->default(UserStatusEnum::ACTIVE->value)
                    ->dehydrateStateUsing(fn (mixed $state): UserStatusEnum => $state instanceof UserStatusEnum ? $state : UserStatusEnum::from($state)),

                TextInput::make('email')
                    ->label(trans('user.email'))
                    ->email()
                    ->required()
                    ->maxLength(255),
                DateTimePicker::make('email_verified_at')
                    ->label(trans('user.email_verified_at')),
                TextInput::make('mobile')
                    ->label(trans('user.mobile'))
                    ->maxLength(20),
                DateTimePicker::make('mobile_verified_at')
                    ->label(trans('user.mobile_verified_at')),
                TextInput::make('national_id')
                    ->label(trans('user.national_id'))
                    ->maxLength(10),
                TextInput::make('login_token')
                    ->label(trans('user.login_token'))
                    ->hint(function () {
                        return new HtmlString('
                <span wire:click="$set(\'data.login_token\', \'' . Str::random(100) . '\')" class="font-medium h- px-2 py-0.5 rounded-xl bg-primary-500 text-white text-xs tracking-tight mt-[10px] cursor-pointer">
                    Generate Token
                </span>
        ');
                    })
                    ->visible(function (?User $record) {
                        return $record?->hasRole(RolesEnum::USER->value) ?? false;
                    }),
                Select::make('roles')
                    ->label(trans('user.roles'))
                    ->multiple()
                    ->preload()
                    ->relationship('roles', 'name')
                    ->visible(function () {
                        return auth()->user()->hasRole(RolesEnum::SUPER_ADMIN->value);
                    }),
                Repeater::make('addresses')
                    ->label(trans('user.addresses'))
                    ->relationship('addresses')
                    ->mutateRelationshipDataBeforeSaveUsing(function (Get $get, array $data, Address $record) {
                        unset($data['province']);
                        if (! Address::query()->where($data)->first()?->id) {
                            $record->delete();
                            $data['user_id'] = $record->user_id;
                            Address::query()->create($data);

                            return null;
                        }
                    })
                    ->schema([
                        TextInput::make('name')
                            ->label(trans('user.address_name'))
                            ->maxLength(255)
                            ->required(),
                        TextInput::make('phone')
                            ->label(trans('user.phone'))
                            ->required()
                            ->maxLength(20),
                        TextInput::make('postal_code')
                            ->label(trans('user.postal_code'))
                            ->maxLength(50),
                        TextInput::make('address')
                            ->label(trans('user.address'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('description')
                            ->label(trans('user.description'))
                            ->maxLength(255),
                        Select::make('province')
                            ->label(trans('user.province'))
                            ->options(function () {
                                return Province::all()->pluck('name', 'id');
                            })
                            ->default(1)
                            ->searchable()
                            ->live()
                            ->preload()
                            ->afterStateUpdated(function (Set $set) {
                                $set('city_id', null);
                            }),
                        Select::make('city_id')
                            ->label(trans('user.city_id'))
                            ->relationship('city', 'name')
                            ->live()
                            ->preload()
                            ->required()
                            ->options(function (?Get $get, ?Address $record) {
                                if (! empty($record) && ! $get('province')) {
                                    return [$record->city->id => $record->city->name];
                                }

                                return City::query()
                                    ->where('province_id', $get('province'))
                                    ->pluck('name', 'id');
                            }),

                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('first_name')
                    ->label(trans('user.first_name'))
                    ->searchable(),
                TextColumn::make('last_name')
                    ->label(trans('user.last_name'))
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->label(trans('user.roles'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(trans('user.email'))
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->label(trans('user.email_verified_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('mobile')
                    ->label(trans('user.mobile'))
                    ->searchable(),
                TextColumn::make('mobile_verified_at')
                    ->label(trans('user.mobile_verified_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('national_id')
                    ->label(trans('user.national_id')),
                TextColumn::make('created_at')
                    ->label(trans('user.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(trans('user.updated_at'))
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
                    //                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $query->whereNot('id', auth()->id());
        if (! auth()->user()->hasRole(RolesEnum::SUPER_ADMIN->value) && auth()->user()->hasRole(RolesEnum::ADMIN->value)) {
            $query->withoutRole(RolesEnum::SUPER_ADMIN->value); // @phpstan-ignore-line
        }

        return $query;
    }

    public static function canCreate(): bool
    {
        return Auth::user()->hasRole('super-admin');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
