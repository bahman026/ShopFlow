<script setup>
import AppHead from '@/Components/AppHead.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import AppLink from '@/Components/AppLink.vue';
import Icon from '@/Components/Icon.vue';
import OrderDetail from '@/Components/Order/OrderDetail.vue';
import { uiIcons } from '@/fontawesome';
import { useFormat } from '@/composables/useFormat';

defineProps({
    order: {
        type: Object,
        required: true,
    },
});

const { toPersianDigits } = useFormat();
</script>

<template>
    <AppHead title="سفارش ثبت شد" :noindex="true" />

    <AppLayout>
        <div class="mx-auto flex max-w-3xl flex-col gap-6">
            <div class="flex flex-col items-center gap-3 py-6 text-center">
                <div
                    class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100 text-2xl text-green-600"
                >
                    <Icon :icon="uiIcons.check" />
                </div>
                <h1 class="text-lg font-bold text-gray-900">پرداخت با موفقیت انجام شد</h1>
                <p class="text-sm text-gray-500">
                    شماره سفارش شما
                    <span class="font-bold text-gray-800" dir="ltr"
                        >#{{ toPersianDigits(order.trackingCode) }}</span
                    >
                    ثبت شد و {{ order.statusLabel }} است.
                </p>
                <p v-if="order.refId" class="text-xs text-gray-400" dir="ltr">
                    کد پیگیری: {{ toPersianDigits(order.refId) }}
                </p>
            </div>

            <OrderDetail :order="order" />

            <div class="flex justify-center">
                <AppLink
                    href="/"
                    class="bg-brand hover:bg-brand/90 inline-flex h-12 items-center justify-center rounded-xl px-8 text-sm font-bold text-white transition"
                >
                    بازگشت به فروشگاه
                </AppLink>
            </div>
        </div>
    </AppLayout>
</template>
