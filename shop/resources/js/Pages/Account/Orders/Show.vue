<script setup>
import { computed, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppHead from '@/Components/AppHead.vue';
import AccountLayout from '@/Layouts/AccountLayout.vue';
import AppLink from '@/Components/AppLink.vue';
import Icon from '@/Components/Icon.vue';
import OrderDetail from '@/Components/Order/OrderDetail.vue';
import { uiIcons } from '@/fontawesome';
import { useFormat } from '@/composables/useFormat';
import { useOrderStatus } from '@/composables/useOrderStatus';

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
});

const { formatDate, toPersianDigits } = useFormat();
const { orderStatusColor } = useOrderStatus();

const page = usePage();
const status = computed(() => page.props.flash?.status ?? null);
const retrying = ref(false);

function retryPayment() {
    router.post(
        `/account/orders/${props.order.id}/retry`,
        {},
        { onStart: () => (retrying.value = true), onFinish: () => (retrying.value = false) },
    );
}
</script>

<template>
    <AppHead :title="'سفارش #' + toPersianDigits(order.trackingCode)" :noindex="true" />

    <AccountLayout>
        <div class="mb-4 flex items-center justify-between">
            <AppLink
                href="/account/orders"
                class="hover:text-brand flex items-center gap-2 text-sm text-gray-500 transition"
            >
                <Icon :icon="uiIcons.chevronRight" class="text-xs" />
                بازگشت به سفارش‌ها
            </AppLink>
        </div>

        <p v-if="status" class="mb-4 rounded-lg bg-green-50 p-3 text-center text-sm text-green-700">
            {{ status }}
        </p>

        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-lg font-bold text-gray-900" dir="ltr">
                    #{{ toPersianDigits(order.trackingCode) }}
                </h1>
                <p class="mt-1 text-xs text-gray-400">{{ formatDate(order.createdAt) }}</p>
            </div>
            <div class="flex items-center gap-3">
                <span
                    class="rounded-full px-3 py-1 text-sm font-medium"
                    :class="orderStatusColor(order.status)"
                >
                    {{ order.statusLabel }}
                </span>
                <button
                    v-if="order.canRetryPayment"
                    type="button"
                    class="bg-brand hover:bg-brand/90 flex h-9 items-center rounded-xl px-4 text-sm font-bold text-white transition disabled:opacity-60"
                    :disabled="retrying"
                    @click="retryPayment"
                >
                    پرداخت مجدد
                </button>
            </div>
        </div>

        <OrderDetail :order="order" />
    </AccountLayout>
</template>
