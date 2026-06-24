<?php

declare(strict_types=1);

use App\Filament\Resources\SettingResource;
use App\Models\Setting;
use Filament\Actions\DeleteAction;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

it('can render index page of the setting resource.', function () {
    get(SettingResource::getUrl())->assertOk();
});

it('can list settings in the table.', function () {
    $settings = Setting::factory()->count(5)->create();

    livewire(SettingResource\Pages\ListSettings::class)
        ->assertCanSeeTableRecords($settings);
});

it('can render edit setting page.', function () {
    $setting = Setting::factory()->create();

    get(SettingResource::getUrl('edit', [
        'record' => $setting,
    ]))->assertOk();
});

it('can create setting model.', function () {
    livewire(SettingResource\Pages\CreateSetting::class)
        ->fillForm([
            'key' => 'site_phone',
            'label' => 'شماره تماس',
            'content' => '021-00000000',
            'autoload' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Setting::class, [
        'key' => 'site_phone',
        'content' => '021-00000000',
    ]);
});

it('can delete setting model.', function () {
    $setting = Setting::factory()->create();

    livewire(SettingResource\Pages\EditSetting::class, [
        'record' => $setting->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($setting);
});
