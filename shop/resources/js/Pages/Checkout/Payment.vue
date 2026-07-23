<script setup>
import { computed, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppHead from '@/Components/AppHead.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import CheckoutSteps from '@/Components/Checkout/CheckoutSteps.vue';
import CartSummary from '@/Components/Cart/CartSummary.vue';
import { uiIcons } from '@/fontawesome';

const props = defineProps({
    summary: {
        type: Object,
        default: () => ({ count: 0, itemsTotal: 0, discount: 0, payable: 0 }),
    },
    address: { type: Object, default: null },
    shippingMethod: { type: Object, default: null },
});

const page = usePage();
const status = computed(() => page.props.flash?.status ?? null);
const processing = ref(false);

const fullAddress = computed(() => {
    if (!props.address) {
        return '';
    }
    const parts = [
        props.address.provinceName,
        props.address.cityName,
        props.address.address,
    ].filter(Boolean);
    return parts.join('، ');
});

function pay() {
    router.post(
        '/checkout/payment',
        {},
        { onStart: () => (processing.value = true), onFinish: () => (processing.value = false) },
    );
}
</script>

<template>
    <AppHead title="اطلاعات پرداخت" :noindex="true" />

    <AppLayout>
        <div class="flex flex-col gap-8">
            <CheckoutSteps :current="3" />

            <p v-if="status" class="rounded-lg bg-red-50 p-3 text-center text-sm text-red-700">
                {{ status }}
            </p>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <div class="flex flex-col gap-6 lg:col-span-8">
                    <div
                        v-if="address"
                        class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm"
                    >
                        <div class="mb-2 flex items-center gap-2 text-sm font-bold text-gray-900">
                            <Icon :icon="uiIcons.truck" class="text-brand" />
                            ارسال به نشانی
                        </div>
                        <p class="text-sm text-gray-700">{{ address.name }}</p>
                        <p class="text-sm leading-6 text-gray-600">{{ fullAddress }}</p>
                        <p class="mt-1 text-xs text-gray-400" dir="ltr">{{ address.phone }}</p>

                        <div v-if="shippingMethod" class="mt-3 border-t border-gray-100 pt-3">
                            <p class="text-sm font-medium text-gray-800">
                                روش ارسال: {{ shippingMethod.name }}
                            </p>
                            <p v-if="shippingMethod.sendingDays" class="mt-1 text-xs text-gray-400">
                                زمان ارسال: {{ shippingMethod.sendingDays }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="flex flex-col items-center gap-3 rounded-2xl border border-dashed border-gray-200 bg-white px-6 py-16 text-center"
                    >
                        <Icon :icon="uiIcons.creditCard" class="text-4xl text-gray-300" />
                        <p class="text-base font-bold text-gray-700">پرداخت آنلاین با زرین‌پال</p>
                        <p class="text-sm text-gray-500">
                            با کلیک روی «پرداخت» به درگاه پرداخت امن زرین‌پال منتقل می‌شوید.
                        </p>
                    </div>
                </div>

                <div class="lg:col-span-4">
                    <CartSummary
                        :summary="summary"
                        cta-label="پرداخت"
                        :processing="processing"
                        :show-shipping="true"
                        :shipping="shippingMethod"
                        @submit="pay"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
