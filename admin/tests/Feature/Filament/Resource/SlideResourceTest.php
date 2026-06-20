<?php

declare(strict_types=1);

use App\Filament\Resources\SlideResource;
use App\Models\Slide;
use App\Models\Slider;
use Filament\Actions\DeleteAction;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

it('can render index page of the slide resource.', function () {
    get(SlideResource::getUrl())->assertOk();
});

it('can list slides in the table.', function () {
    $slides = Slide::factory()->count(5)->create();

    livewire(SlideResource\Pages\ListSlides::class)
        ->assertCanSeeTableRecords($slides);
});

it('can render edit slide page.', function () {
    $slide = Slide::factory()->create();

    get(SlideResource::getUrl('edit', [
        'record' => $slide,
    ]))->assertOk();
});

it('can update slide model.', function () {
    $slide = Slide::factory()->create();
    $newSlide = Slide::factory()->make();

    livewire(SlideResource\Pages\EditSlide::class, [
        'record' => $slide->getRouteKey(),
    ])
        ->fillForm([
            'slider_id' => $slide->slider_id,
            'heading' => $newSlide->heading,
            'label' => $newSlide->label,
            'url' => $newSlide->url,
            'order' => $newSlide->order,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($slide->refresh())
        ->heading->toBe($newSlide->heading)
        ->order->toBe($newSlide->order);
});

it('can create slide model.', function () {
    $slider = Slider::factory()->create();
    $newSlide = Slide::factory()->make(['slider_id' => $slider->id]);

    livewire(SlideResource\Pages\CreateSlide::class)
        ->fillForm([
            'slider_id' => $slider->id,
            'heading' => $newSlide->heading,
            'label' => $newSlide->label,
            'url' => $newSlide->url,
            'order' => $newSlide->order,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Slide::class, [
        'slider_id' => $slider->id,
        'heading' => $newSlide->heading,
        'order' => $newSlide->order,
    ]);
});

it('can delete slide model.', function () {
    $slide = Slide::factory()->create();

    livewire(SlideResource\Pages\EditSlide::class, [
        'record' => $slide->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($slide);
});

it('cascades delete image when slide is deleted.', function () {
    $slide = Slide::factory()->withImage()->create();
    $image = $slide->image;

    $slide->delete();

    $this->assertModelMissing($image);
});
