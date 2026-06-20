<?php

declare(strict_types=1);

use App\Filament\Resources\SliderResource;
use App\Models\Slide;
use App\Models\Slider;
use Filament\Actions\DeleteAction;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

it('can render index page of the slider resource.', function () {
    get(SliderResource::getUrl())->assertOk();
});

it('can list sliders in the table.', function () {
    $sliders = Slider::factory()->count(5)->create();

    livewire(SliderResource\Pages\ListSliders::class)
        ->assertCanSeeTableRecords($sliders);
});

it('can render edit slider page.', function () {
    $slider = Slider::factory()->create();

    get(SliderResource::getUrl('edit', [
        'record' => $slider,
    ]))->assertOk();
});

it('can update slider model.', function () {
    $slider = Slider::factory()->create();
    $newSlider = Slider::factory()->make();

    livewire(SliderResource\Pages\EditSlider::class, [
        'record' => $slider->getRouteKey(),
    ])
        ->fillForm([
            'name' => $newSlider->name,
            'position' => $newSlider->position,
            'status' => $newSlider->status->value,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($slider->refresh())
        ->name->toBe($newSlider->name)
        ->position->toBe($newSlider->position)
        ->status->toBe($newSlider->status);
});

it('can create slider model.', function () {
    $newSlider = Slider::factory()->make();

    livewire(SliderResource\Pages\CreateSlider::class)
        ->fillForm([
            'name' => $newSlider->name,
            'position' => $newSlider->position,
            'status' => $newSlider->status->value,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Slider::class, [
        'name' => $newSlider->name,
        'position' => $newSlider->position,
        'status' => $newSlider->status->value,
    ]);
});

it('can delete slider model.', function () {
    $slider = Slider::factory()->create();

    livewire(SliderResource\Pages\EditSlider::class, [
        'record' => $slider->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($slider);
});

it('cascades delete to slides when slider is deleted.', function () {
    $slider = Slider::factory()->create();
    $slide = Slide::factory()->create(['slider_id' => $slider->id]);

    $slider->delete();

    $this->assertModelMissing($slide);
});
