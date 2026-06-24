<?php

declare(strict_types=1);

use App\Filament\Resources\UserConfigResource;
use App\Models\User;
use App\Models\UserConfig;
use Filament\Actions\DeleteAction;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

it('can render index page of the user config resource.', function () {
    get(UserConfigResource::getUrl())->assertOk();
});

it('can list user configs in the table.', function () {
    $configs = UserConfig::factory()->count(5)->create();

    livewire(UserConfigResource\Pages\ListUserConfigs::class)
        ->assertCanSeeTableRecords($configs);
});

it('can render edit user config page.', function () {
    $config = UserConfig::factory()->create();

    get(UserConfigResource::getUrl('edit', [
        'record' => $config,
    ]))->assertOk();
});

it('can create user config model.', function () {
    $user = User::factory()->create();

    livewire(UserConfigResource\Pages\CreateUserConfig::class)
        ->fillForm([
            'user_id' => $user->id,
            'key' => 'newsletter',
            'label' => 'Newsletter',
            'content' => 'enabled',
            'autoload' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(UserConfig::class, [
        'user_id' => $user->id,
        'key' => 'newsletter',
        'content' => 'enabled',
    ]);
});

it('can delete user config model.', function () {
    $config = UserConfig::factory()->create();

    livewire(UserConfigResource\Pages\EditUserConfig::class, [
        'record' => $config->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($config);
});
