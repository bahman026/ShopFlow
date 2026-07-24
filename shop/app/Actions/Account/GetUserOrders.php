<?php

declare(strict_types=1);

namespace App\Actions\Account;

use App\Actions\Catalog\TransformImage;
use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\OrderVariety;
use App\Models\User;

class GetUserOrders
{
    /**
     * Orders shown per page in the account order history.
     */
    private const PER_PAGE = 10;

    public function __construct(private TransformImage $transformImage) {}

    /**
     * Paginated, newest-first order cards for one user's order history.
     * Pass $status to scope the list (e.g. the account "returns" page only
     * wants RETURNED orders) — omit it for the full order history.
     *
     * @return array{data: array<int, array<string, mixed>>, meta: array{currentPage: int, lastPage: int, perPage: int, total: int, from: int|null, to: int|null}}
     */
    public function __invoke(User $user, ?OrderStatusEnum $status = null): array
    {
        $query = Order::query()->where('user_id', $user->id);

        if ($status !== null) {
            $query->where('status', $status);
        }

        $paginator = $query
            ->with(['orderVarieties.product.featuredImage', 'orderVarieties.variety.image'])
            // created_at alone isn't a reliable sort: orders created within the
            // same second would tie with no deterministic order, so id breaks it.
            ->latest()
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        /** @var array<int, Order> $items */
        $items = $paginator->items();

        return [
            'data' => array_map(fn (Order $order): array => $this->card($order), $items),
            'meta' => [
                'currentPage' => $paginator->currentPage(),
                'lastPage' => $paginator->lastPage(),
                'perPage' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function card(Order $order): array
    {
        /** @var OrderVariety|null $firstLine */
        $firstLine = $order->orderVarieties->first();
        $product = $firstLine?->product;
        $variety = $firstLine?->variety;

        $image = $this->transformImage->__invoke(
            // @phpstan-ignore nullsafe.neverNull (nullable snapshot FKs; see BuildOrderDTO::line())
            $variety?->image ?? $product?->featuredImage,
        );

        return [
            'id' => $order->id,
            'trackingCode' => $order->tracking_code,
            'status' => $order->status->name,
            'statusLabel' => $order->status->label(),
            'createdAt' => (string) $order->created_at?->toIso8601String(),
            'totalPrice' => $order->total_price,
            'itemCount' => (int) $order->orderVarieties->sum('quantity'),
            // @phpstan-ignore nullsafe.neverNull (nullable snapshot FK; see BuildOrderDTO::line())
            'firstItemHeading' => $product?->heading ?? trans('messages.deleted_product'),
            'image' => $image?->toArray(),
            'url' => '/account/orders/'.$order->id,
        ];
    }
}
