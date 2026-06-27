<script setup>
import { computed } from 'vue';
import { useFormat } from '@/composables/useFormat';

const props = defineProps({
    summary: {
        type: Object,
        required: true,
    },
    ctaLabel: {
        type: String,
        default: 'تایید و تکمیل سفارش',
    },
    processing: {
        type: Boolean,
        default: false,
    },
    showShipping: {
        type: Boolean,
        default: false,
    },
    shipping: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['submit']);

const { formatPrice } = useFormat();

// Shipping adds to the payable only when a method with a real cost is chosen
// (pay-on-delivery and free shipping do not change the online total).
const shippingCost = computed(() =>
    props.shipping && !props.shipping.payOnDelivery ? (props.shipping.cost ?? 0) : 0,
);
const total = computed(() => props.summary.payable + shippingCost.value);
</script>

<template>
    <div class="flex flex-col gap-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <h2 class="text-base font-bold text-gray-900">خلاصه سفارش</h2>

        <dl class="flex flex-col gap-3 text-sm">
            <div class="flex items-center justify-between text-gray-600">
                <dt>قیمت کالاها</dt>
                <dd>{{ formatPrice(summary.itemsTotal) }}</dd>
            </div>

            <div
                v-if="summary.discount > 0"
                class="flex items-center justify-between font-medium text-green-600"
            >
                <dt>سود شما از خرید</dt>
                <dd>{{ formatPrice(summary.discount) }}</dd>
            </div>

            <div v-if="showShipping" class="flex items-center justify-between text-gray-600">
                <dt>هزینه ارسال</dt>
                <dd v-if="!shipping" class="text-xs text-gray-400">روش حمل و نقل انتخاب نشده</dd>
                <dd v-else-if="shipping.payOnDelivery" class="text-gray-500">پس‌کرایه</dd>
                <dd v-else-if="shippingCost === 0" class="text-green-600">رایگان</dd>
                <dd v-else>{{ formatPrice(shippingCost) }}</dd>
            </div>

            <div
                class="flex items-center justify-between border-t border-gray-100 pt-3 font-bold text-gray-900"
            >
                <dt>قابل پرداخت</dt>
                <dd>{{ formatPrice(total) }}</dd>
            </div>
        </dl>

        <button
            type="button"
            class="bg-brand hover:bg-brand/90 h-12 w-full rounded-xl text-sm font-bold text-white transition disabled:opacity-50"
            :disabled="processing || summary.count === 0"
            @click="emit('submit')"
        >
            {{ ctaLabel }}
        </button>
    </div>
</template>
