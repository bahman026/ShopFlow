<?php

declare(strict_types=1);

use App\Enums\ReviewStatusEnum;
use App\Filament\Resources\ReviewResource;
use App\Models\Review;
use Filament\Actions\DeleteAction;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

it('can render index page of the review resource.', function () {
    get(ReviewResource::getUrl())->assertOk();
});

it('can list reviews in the table.', function () {
    $reviews = Review::factory()->count(5)->create();

    livewire(ReviewResource\Pages\ListReviews::class)
        ->assertCanSeeTableRecords($reviews);
});

it('can render edit page.', function () {
    $review = Review::factory()->create();

    get(ReviewResource::getUrl('edit', [
        'record' => $review,
    ]))->assertOk();
});

it('can update review status.', function () {
    $review = Review::factory()->create(['status' => ReviewStatusEnum::PENDING]);

    livewire(ReviewResource\Pages\EditReview::class, [
        'record' => $review->getRouteKey(),
    ])
        ->fillForm([
            'heading' => $review->heading,
            'content' => $review->content,
            'product_id' => $review->product_id,
            'status' => ReviewStatusEnum::APPROVED->value,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($review->refresh())
        ->status->toBe(ReviewStatusEnum::APPROVED);
});

it('can create review.', function () {
    $newReview = Review::factory()->make(['status' => ReviewStatusEnum::APPROVED]);

    livewire(ReviewResource\Pages\CreateReview::class)
        ->fillForm([
            'heading' => $newReview->heading,
            'content' => $newReview->content,
            'product_id' => $newReview->product_id,
            'status' => ReviewStatusEnum::APPROVED->value,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Review::class, [
        'heading' => $newReview->heading,
        'status' => ReviewStatusEnum::APPROVED->value,
    ]);
});

it('can delete review.', function () {
    $review = Review::factory()->create();

    livewire(ReviewResource\Pages\EditReview::class, [
        'record' => $review->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($review);
});

it('deletes replies when parent review is deleted.', function () {
    $parent = Review::factory()->create();
    $reply = Review::factory()->withParent($parent)->create();

    $parent->delete();

    $this->assertModelMissing($parent);
    $this->assertDatabaseMissing(Review::class, ['parent_id' => $parent->id]);
    expect($reply->refresh()->parent_id)->toBeNull();
});
