const STATUS_COLORS = {
    PENDING: 'bg-amber-50 text-amber-700',
    PAID: 'bg-green-50 text-green-700',
    PROCESSING: 'bg-blue-50 text-blue-700',
    SHIPPED: 'bg-blue-50 text-blue-700',
    DELIVERED: 'bg-green-50 text-green-700',
    CANCELED: 'bg-red-50 text-red-700',
    RETURNED: 'bg-red-50 text-red-700',
};

/**
 * Tailwind badge classes for an order's status (OrderStatusEnum name, e.g.
 * "PAID"). Kept client-side since shop enums never define color() (that's
 * admin/Filament-only) — this is a display concern, not a shared value.
 */
export function orderStatusColor(status) {
    return STATUS_COLORS[status] ?? 'bg-gray-50 text-gray-700';
}

export function useOrderStatus() {
    return { orderStatusColor };
}
