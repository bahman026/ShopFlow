<?php

declare(strict_types=1);

use App\Enums\OrderSrcEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\ReviewStatusEnum;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

/**
 * Give the user a purchased order containing the product, so their review
 * qualifies for the "خریدار" (verified buyer) badge.
 */
function buyProduct(User $user, Product $product, OrderStatusEnum $status = OrderStatusEnum::PAID): void
{
    $variety = $product->varieties()->firstOrFail();

    $order = Order::create([
        'user_id' => $user->id,
        'status' => $status,
        'coupon_discount' => 0,
        'discount' => 0,
        'shipping_cost' => 0,
        'total_products_price' => 100000,
        'tax' => 0,
        'total_price' => 100000,
        'src' => OrderSrcEnum::WEB,
    ]);

    $order->orderVarieties()->create([
        'product_id' => $product->id,
        'variety_id' => $variety->id,
        'quantity' => 1,
        'price' => 100000,
        'discount' => 0,
        'coupon_discount' => 0,
        'final_price' => 100000,
    ]);
}

it('lets a logged-in user submit a review, created pending', function (): void {
    $user = User::factory()->create();
    $product = makeProduct();

    $this->actingAs($user)
        ->post('/products/'.$product->id.'/reviews', [
            'rating' => 4,
            'heading' => 'خیلی خوب بود',
            'content' => 'کیفیت ساخت عالی بود و به‌موقع رسید.',
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    $this->assertDatabaseHas('reviews', [
        'user_id' => $user->id,
        'product_id' => $product->id,
        'rating' => 4,
        'heading' => 'خیلی خوب بود',
        'status' => ReviewStatusEnum::PENDING->value,
    ]);
});

it('redirects guests to login when submitting a review', function (): void {
    $product = makeProduct();

    $this->post('/products/'.$product->id.'/reviews', [
        'rating' => 5,
        'heading' => 'test',
        'content' => 'test content',
    ])->assertRedirect('/login');

    $this->assertDatabaseMissing('reviews', ['product_id' => $product->id]);
});

it('validates the rating, heading and content', function (): void {
    $user = User::factory()->create();
    $product = makeProduct();

    $this->actingAs($user)
        ->post('/products/'.$product->id.'/reviews', [
            'rating' => 6,
            'heading' => '',
            'content' => '',
        ])
        ->assertSessionHasErrors(['rating', 'heading', 'content']);

    $this->assertDatabaseMissing('reviews', ['product_id' => $product->id]);
});

it('never shows pending reviews on the product page', function (): void {
    $product = makeProduct();

    Review::create([
        'product_id' => $product->id,
        'heading' => 'در انتظار تایید',
        'content' => 'این نباید نمایش داده شود.',
        'rating' => 3,
        'status' => ReviewStatusEnum::PENDING,
    ]);

    $this->get('/products/'.$product->slug)
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->has('product.reviews', 0)
            ->where('product.reviewCount', 0)
            ->where('product.averageRating', null)
        );
});

it('shows approved reviews with rating and an average', function (): void {
    $product = makeProduct();

    Review::create([
        'product_id' => $product->id,
        'heading' => 'خوب',
        'content' => 'محتوا',
        'rating' => 4,
        'status' => ReviewStatusEnum::APPROVED,
    ]);
    Review::create([
        'product_id' => $product->id,
        'heading' => 'عالی',
        'content' => 'محتوا',
        'rating' => 5,
        'status' => ReviewStatusEnum::APPROVED,
    ]);

    $this->get('/products/'.$product->slug)
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->has('product.reviews', 2)
            ->where('product.reviewCount', 2)
            ->where('product.averageRating', 4.5)
        );
});

it('flags a review as a verified buyer only when the author purchased the product', function (): void {
    $buyer = User::factory()->create();
    $nonBuyer = User::factory()->create();
    $product = makeProduct();

    buyProduct($buyer, $product);

    Review::create([
        'user_id' => $buyer->id,
        'product_id' => $product->id,
        'heading' => 'از خریدار',
        'content' => 'محتوا',
        'rating' => 5,
        'status' => ReviewStatusEnum::APPROVED,
    ]);
    Review::create([
        'user_id' => $nonBuyer->id,
        'product_id' => $product->id,
        'heading' => 'بدون خرید',
        'content' => 'محتوا',
        'rating' => 3,
        'status' => ReviewStatusEnum::APPROVED,
    ]);

    $this->get('/products/'.$product->slug)
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('product.reviews.0.isBuyer', true)   // buyer's review is newest-first
            ->where('product.reviews.1.isBuyer', false)
        );
});

it('does not count a canceled order as a purchase for the buyer badge', function (): void {
    $user = User::factory()->create();
    $product = makeProduct();

    buyProduct($user, $product, OrderStatusEnum::CANCELED);

    Review::create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'heading' => 'سفارش لغو شده',
        'content' => 'محتوا',
        'rating' => 2,
        'status' => ReviewStatusEnum::APPROVED,
    ]);

    $this->get('/products/'.$product->slug)
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('product.reviews.0.isBuyer', false)
        );
});

it('exposes canReview true for a logged-in user and false for a guest', function (): void {
    $product = makeProduct();

    $this->get('/products/'.$product->slug)
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page->where('canReview', false));

    $this->actingAs(User::factory()->create())
        ->get('/products/'.$product->slug)
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page->where('canReview', true));
});
