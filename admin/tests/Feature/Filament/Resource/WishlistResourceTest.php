<?php

declare(strict_types=1);

use App\Filament\Resources\WishlistResource;
use App\Models\Wishlist;
use Filament\Actions\DeleteAction;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

it('can render index page of the wishlist resource.', function () {
    get(WishlistResource::getUrl())->assertOk();
});

it('can list wishlists in the table.', function () {
    $wishlists = Wishlist::factory()->count(5)->create();

    livewire(WishlistResource\Pages\ListWishlists::class)
        ->assertCanSeeTableRecords($wishlists);
});

it('can create wishlist entry.', function () {
    $wishlist = Wishlist::factory()->make();

    livewire(WishlistResource\Pages\CreateWishlist::class)
        ->fillForm([
            'user_id' => $wishlist->user_id,
            'product_id' => $wishlist->product_id,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Wishlist::class, [
        'user_id' => $wishlist->user_id,
        'product_id' => $wishlist->product_id,
    ]);
});

it('can delete wishlist entry.', function () {
    $wishlist = Wishlist::factory()->create();

    livewire(WishlistResource\Pages\ListWishlists::class)
        ->callTableAction(DeleteAction::class, $wishlist);

    $this->assertModelMissing($wishlist);
});

it('cascades delete when user is deleted.', function () {
    $wishlist = Wishlist::factory()->create();
    $user = $wishlist->user;

    $user->delete();

    $this->assertModelMissing($wishlist);
});
