<?php

declare(strict_types=1);

use App\Filament\Resources\FaqResource;
use App\Models\Faq;
use Filament\Actions\DeleteAction;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

it('can render index page of the faq resource.', function () {
    get(FaqResource::getUrl())->assertOk();
});

it('can list faqs in the table.', function () {
    $faqs = Faq::factory()->count(5)->create();

    livewire(FaqResource\Pages\ListFaqs::class)
        ->assertCanSeeTableRecords($faqs);
});

it('can render edit page.', function () {
    $faq = Faq::factory()->create();

    get(FaqResource::getUrl('edit', [
        'record' => $faq,
    ]))->assertOk();
});

it('can update faq model.', function () {
    $faq = Faq::factory()->create();
    $newFaq = Faq::factory()->make();

    livewire(FaqResource\Pages\EditFaq::class, [
        'record' => $faq->getRouteKey(),
    ])
        ->fillForm([
            'heading' => $newFaq->heading,
            'content' => $newFaq->content,
            'order' => $newFaq->order,
            'position' => $newFaq->position,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($faq->refresh())
        ->heading->toBe($newFaq->heading)
        ->content->toBe($newFaq->content)
        ->order->toBe($newFaq->order);
});

it('can create faq model.', function () {
    $newFaq = Faq::factory()->make();

    livewire(FaqResource\Pages\CreateFaq::class)
        ->fillForm([
            'heading' => $newFaq->heading,
            'content' => $newFaq->content,
            'order' => $newFaq->order,
            'position' => $newFaq->position,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Faq::class, [
        'heading' => $newFaq->heading,
        'order' => $newFaq->order,
    ]);
});

it('can delete faq model.', function () {
    $faq = Faq::factory()->create();

    livewire(FaqResource\Pages\EditFaq::class, [
        'record' => $faq->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($faq);
});
