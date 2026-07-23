<?php

declare(strict_types=1);

namespace App\Actions\Checkout;

use App\Actions\Account\BuildAddressDTO;
use App\Actions\Catalog\TransformImage;
use App\DTOs\OrderDTO;
use App\DTOs\OrderLineDTO;
use App\Enums\TransactionStatusEnum;
use App\Models\Order;
use App\Models\OrderVariety;
use App\Models\Transaction;

class BuildOrderDTO
{
    public function __construct(
        private TransformImage $transformImage,
        private BuildAddressDTO $buildAddress,
    ) {}

    public function __invoke(Order $order): OrderDTO
    {
        $order->loadMissing([
            'orderVarieties.product.featuredImage',
            'orderVarieties.variety.image',
            'address.city.province',
            'shippingMethod.shippingLine',
            'transactions',
        ]);

        /** @var Transaction|null $transaction */
        $transaction = $order->transactions->firstWhere('status', TransactionStatusEnum::SUCCESS)
            ?? $order->transactions->last();

        return new OrderDTO(
            id: $order->id,
            status: $order->status->name,
            statusLabel: $order->status->label(),
            createdAt: (string) $order->created_at?->toIso8601String(),
            totalProductsPrice: $order->total_products_price,
            discount: $order->discount,
            shippingCost: $order->shipping_cost,
            taxPrice: $order->tax,
            totalPrice: $order->total_price,
            lines: $order->orderVarieties->map(fn (OrderVariety $line): OrderLineDTO => $this->line($line))->all(),
            address: $order->address === null ? null : ($this->buildAddress)($order->address),
            shippingMethodName: $order->shippingMethod?->name,
            shippingLineName: $order->shippingMethod?->shippingLine?->name,
            refId: $transaction?->ref_id,
            paidAt: $transaction?->paid_at?->toIso8601String(),
        );
    }

    private function line(OrderVariety $line): OrderLineDTO
    {
        // product_id/variety_id are nullable snapshot FKs (nullOnDelete): the
        // line survives deletion of the product/variety it once referenced.
        // Larastan resolves these BelongsTo magic properties as non-nullable
        // regardless of the column's actual nullability, so it flags the
        // nullsafe operator below as redundant — it is not; keep it.
        $product = $line->product;
        $variety = $line->variety;

        $image = $this->transformImage->__invoke(
            // @phpstan-ignore nullsafe.neverNull (see comment above)
            $variety?->image ?? $product?->featuredImage,
        );

        return new OrderLineDTO(
            // @phpstan-ignore nullsafe.neverNull (see comment above)
            heading: $product?->heading ?? 'محصول حذف‌شده',
            url: $product === null ? null : '/products/'.$product->slug,
            image: $image,
            color: $variety?->color,
            quantity: $line->quantity,
            unitPrice: $line->price,
            finalPrice: $line->final_price,
        );
    }
}
