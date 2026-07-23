<script setup>
import { computed } from 'vue';
import AppLink from '@/Components/AppLink.vue';
import Icon from '@/Components/Icon.vue';
import { uiIcons } from '@/fontawesome';
import { useFormat } from '@/composables/useFormat';

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
});

const { formatPrice, formatDate, toPersianDigits } = useFormat();

const fullAddress = computed(() => {
    if (!props.order.address) {
        return '';
    }
    const parts = [
        props.order.address.provinceName,
        props.order.address.cityName,
        props.order.address.address,
    ].filter(Boolean);
    return parts.join('، ');
});
</script>

<template>
    <div class="flex flex-col gap-6">
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-sm font-bold text-gray-900">اقلام سفارش</h2>
            <div class="flex flex-col gap-4">
                <div
                    v-for="(line, index) in order.lines"
                    :key="index"
                    class="flex gap-4 border-b border-gray-100 pb-4 last:border-b-0 last:pb-0"
                >
                    <AppLink v-if="line.url" :href="line.url" class="shrink-0">
                        <img
                            v-if="line.image"
                            :src="line.image.url"
                            :alt="line.image.alt || line.heading"
                            class="h-16 w-16 rounded-xl border border-gray-100 object-cover"
                        />
                        <div
                            v-else
                            class="flex h-16 w-16 items-center justify-center rounded-xl border border-gray-100 bg-gray-50 text-gray-300"
                        >
                            <Icon :icon="uiIcons.image" />
                        </div>
                    </AppLink>

                    <div class="flex min-w-0 flex-1 flex-col gap-1">
                        <p class="text-sm font-medium text-gray-800">{{ line.heading }}</p>
                        <p v-if="line.color" class="text-xs text-gray-500">رنگ: {{ line.color }}</p>
                        <p class="text-xs text-gray-400">
                            {{ toPersianDigits(line.quantity) }} عدد ×
                            {{ formatPrice(line.unitPrice) }}
                        </p>
                    </div>

                    <div class="shrink-0 self-center text-sm font-bold text-gray-900">
                        {{ formatPrice(line.finalPrice) }}
                    </div>
                </div>
            </div>
        </div>

        <div v-if="order.address" class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="mb-2 flex items-center gap-2 text-sm font-bold text-gray-900">
                <Icon :icon="uiIcons.truck" class="text-brand" />
                ارسال به نشانی
            </div>
            <p class="text-sm text-gray-700">{{ order.address.name }}</p>
            <p class="text-sm leading-6 text-gray-600">{{ fullAddress }}</p>
            <p v-if="order.shippingMethodName" class="mt-2 text-xs text-gray-500">
                روش ارسال: {{ order.shippingMethodName }}
                <span v-if="order.shippingLineName">({{ order.shippingLineName }})</span>
            </p>
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-sm font-bold text-gray-900">خلاصه پرداخت</h2>
            <dl class="flex flex-col gap-3 text-sm">
                <div class="flex items-center justify-between text-gray-600">
                    <dt>قیمت کالاها</dt>
                    <dd>{{ formatPrice(order.totalProductsPrice) }}</dd>
                </div>
                <div
                    v-if="order.discount > 0"
                    class="flex items-center justify-between font-medium text-green-600"
                >
                    <dt>سود شما از خرید</dt>
                    <dd>{{ formatPrice(order.discount) }}</dd>
                </div>
                <div class="flex items-center justify-between text-gray-600">
                    <dt>هزینه ارسال</dt>
                    <dd>
                        {{ order.shippingCost === 0 ? 'رایگان' : formatPrice(order.shippingCost) }}
                    </dd>
                </div>
                <div
                    class="flex items-center justify-between border-t border-gray-100 pt-3 font-bold text-gray-900"
                >
                    <dt>مبلغ پرداخت‌شده</dt>
                    <dd>{{ formatPrice(order.totalPrice) }}</dd>
                </div>
            </dl>
            <p v-if="order.paidAt" class="mt-3 text-xs text-gray-400">
                زمان پرداخت: {{ formatDate(order.paidAt) }}
            </p>
        </div>
    </div>
</template>
