<?php

declare(strict_types=1);

use App\Filament\Resources\UserResource;
use App\Models\User;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

it('can render index page of the user resource.', function () {
    // Act & Assert
    get(UserResource::getUrl())->assertOk();
});

it('can list users in the table.', function () {
    // Arrange
    $users = User::factory()->count(5)->create();

    // Act & Assert
    livewire(UserResource\Pages\ListUsers::class)
        ->assertCanSeeTableRecords($users);
});

it('can render edit user page.', function () {
    // Act & Assert
    get(UserResource::getUrl('edit', [
        'record' => User::factory()->create(),
    ]))
        ->assertSuccessful();
});

it('can update user model.', function () {
    // Arrange
    $user = User::factory()->create();
    $newUser = User::factory()->make();

    $role = Role::query()->firstOrCreate(['name' => \App\Enums\RolesEnum::USER->value]);

    // Act & Assert
    livewire(UserResource\Pages\EditUser::class, [
        'record' => $user->getRouteKey(),
    ])
        ->fillForm([
            'first_name' => $newUser->first_name,
            'last_name' => $newUser->last_name,
            'email' => $newUser->email,
            'mobile' => $newUser->mobile,
            'mobile_verified_at' => $newUser->mobile_verified_at,
            'national_id' => $newUser->national_id,
            'login_token' => $newUser->login_token,
            'status' => $newUser->status,
            'email_verified_at' => $newUser->email_verified_at,
            'roles' => $role->id,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($user->refresh())
        ->first_name->toBe($newUser->first_name)
        ->last_name->toBe($newUser->last_name)
        ->email->toBe($newUser->email)
        ->email_verified_at->toDateString()->toBe($newUser->email_verified_at->toDateString())
        ->mobile->toBe($newUser->mobile)
        ->mobile_verified_at->toBe((string) $newUser->mobile_verified_at)
        ->national_id->toBe($newUser->national_id)
        ->login_token->toBe($newUser->login_token)
        ->status->toBe($newUser->status)
        ->roles->first()->id->toBe($role->id);
});
