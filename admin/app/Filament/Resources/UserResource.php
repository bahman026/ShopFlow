<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\RolesEnum;
use App\Enums\UserStatusEnum;
use App\Filament\Resources\UserResource\Pages;
use App\Models\Address;
use App\Models\City;
use App\Models\Province;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('first_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('last_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->required()
                    ->options(UserStatusEnum::options())
                    ->default(UserStatusEnum::ACTIVE->value)
                    ->dehydrateStateUsing(fn (string $state): int => (int) $state),

                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('email_verified_at'),
                Forms\Components\TextInput::make('mobile')
                    ->maxLength(20),
                Forms\Components\DateTimePicker::make('mobile_verified_at'),
                Forms\Components\TextInput::make('national_id')
                    ->maxLength(10),
                Forms\Components\TextInput::make('login_token')
                    ->hint(function () {
                        return new HtmlString('
                <span wire:click="$set(\'data.login_token\', \'' . Str::random(100) . '\')" class="font-medium h- px-2 py-0.5 rounded-xl bg-primary-500 text-white text-xs tracking-tight mt-[10px] cursor-pointer">
                    Generate Token
                </span>
        ');
                    })
                    ->visible(function (User $record) {
                        return $record->hasRole(RolesEnum::USER->value);
                    }),
                Forms\Components\Select::make('roles')
                    ->multiple()
                    ->preload()
                    ->relationship('roles', 'name')
                    ->visible(function () {
                        return auth()->user()->hasRole(RolesEnum::SUPER_ADMIN->value);
                    }),
                Forms\Components\Repeater::make('addresses')
                    ->relationship('addresses')
                    ->mutateRelationshipDataBeforeSaveUsing(function (Forms\Get $get, array $data, Address $record) {
                        unset($data['province']);
                        if (! Address::query()->where($data)->first()?->id) {
                            $record->delete();
                            $data['user_id'] = $record->user_id;
                            Address::query()->create($data);

                            return null;
                        }
                    })
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->maxLength(255)
                            ->required(),
                        Forms\Components\TextInput::make('phone')
                            ->required()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('postal_code')
                            ->maxLength(50),
                        Forms\Components\TextInput::make('address')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('description')
                            ->maxLength(255),
                        Forms\Components\Select::make('province')
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
                        Forms\Components\Select::make('city_id')
                            ->relationship('city', 'name')
                            ->live()
                            ->preload()
                            ->required()
                            ->options(function (Forms\Get $get, $record) {
                                if ($record && ! $get('province')) {
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
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('mobile')
                    ->searchable(),
                Tables\Columns\TextColumn::make('mobile_verified_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('national_id')
                    ->searchable(), Tables\Columns\TextColumn::make('status')
                    ->getStateUsing(fn (User $user) => UserStatusEnum::from($user->status)->label())
                    ->color(fn (User $user) => UserStatusEnum::from($user->status)->color())
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
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
            $query->withoutRole(RolesEnum::SUPER_ADMIN->value);
        }

        return $query;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
