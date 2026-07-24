<script setup>
import { router } from '@inertiajs/vue3';
import AppHead from '@/Components/AppHead.vue';
import AccountLayout from '@/Layouts/AccountLayout.vue';
import AppLink from '@/Components/AppLink.vue';
import EmptyState from '@/Components/EmptyState.vue';
import Icon from '@/Components/Icon.vue';
import Pagination from '@/Components/Pagination.vue';
import { uiIcons } from '@/fontawesome';
import { useFormat } from '@/composables/useFormat';
import { useOrderStatus } from '@/composables/useOrderStatus';

const props = defineProps({
    orders: {
        type: Object,
        default: () => ({ data: [], meta: { currentPage: 1, lastPage: 1 } }),
    },
    title: {
        type: String,
        default: 'سفارش‌های من',
    },
    emptyTitle: {
        type: String,
        default: 'هنوز سفارشی ثبت نکرده‌اید',
    },
    emptyDescription: {
        type: String,
        default: 'سفارش‌های شما پس از خرید، اینجا نمایش داده می‌شوند.',
    },
    baseUrl: {
        type: String,
        default: '/account/orders',
    },
});

const { formatPrice, formatDate, toPersianDigits } = useFormat();
const { orderStatusColor } = useOrderStatus();

function onPage(page) {
    router.get(props.baseUrl, page > 1 ? { page } : {}, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
}
</script>

<template>
    <AppHead :title="title" :noindex="true" />

    <AccountLayout>
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h1 class="mb-6 text-lg font-bold text-gray-900">{{ title }}</h1>

            <EmptyState
                v-if="orders.data.length === 0"
                :icon="uiIcons.orders"
                :title="emptyTitle"
                :description="emptyDescription"
            />

            <div v-else class="flex flex-col gap-3">
                <AppLink
                    v-for="order in orders.data"
                    :key="order.id"
                    :href="order.url"
                    class="flex items-center gap-4 rounded-xl border border-gray-100 p-4 transition hover:border-gray-200"
                >
                    <img
                        v-if="order.image"
                        :src="order.image.url"
                        :alt="order.image.alt || order.firstItemHeading"
                        class="h-16 w-16 shrink-0 rounded-xl border border-gray-100 object-cover"
                    />
                    <div
                        v-else
                        class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl border border-gray-100 bg-gray-50 text-gray-300"
                    >
                        <Icon :icon="uiIcons.image" />
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="mb-1 flex items-center gap-2">
                            <span class="text-sm font-bold text-gray-900" dir="ltr"
                                >#{{ toPersianDigits(order.trackingCode) }}</span
                            >
                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="orderStatusColor(order.status)"
                            >
                                {{ order.statusLabel }}
                            </span>
                        </div>
                        <p class="truncate text-sm text-gray-600">{{ order.firstItemHeading }}</p>
                        <p class="mt-1 text-xs text-gray-400">
                            {{ formatDate(order.createdAt) }} ·
                            {{ toPersianDigits(order.itemCount) }} کالا
                        </p>
                    </div>

                    <div class="shrink-0 text-left">
                        <p class="text-sm font-bold text-gray-900">
                            {{ formatPrice(order.totalPrice) }}
                        </p>
                    </div>
                </AppLink>

                <Pagination
                    :current-page="orders.meta.currentPage"
                    :last-page="orders.meta.lastPage"
                    @change="onPage"
                />
            </div>
        </div>
    </AccountLayout>
</template>
