<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppHead from '@/Components/AppHead.vue';
import Icon from '@/Components/Icon.vue';
import { uiIcons } from '@/fontawesome';
import { useFormat } from '@/composables/useFormat';

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const { formatPrice, formatDate, toPersianDigits } = useFormat();

const siteName = computed(() => page.props.seo?.siteName ?? '');
const contact = computed(() => page.props.footer?.contact ?? {});

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

function lineDiscount(line) {
    return line.unitPrice * line.quantity - line.finalPrice;
}

function print() {
    window.print();
}
</script>

<template>
    <AppHead :title="'رسید سفارش #' + toPersianDigits(order.trackingCode)" :noindex="true" />

    <div class="mx-auto max-w-3xl px-4 py-8 print:max-w-none print:p-0">
        <div class="mb-6 flex items-center justify-between print:hidden">
            <button
                type="button"
                class="bg-brand hover:bg-brand/90 flex h-11 items-center gap-2 rounded-xl px-6 text-sm font-bold text-white transition"
                @click="print"
            >
                <Icon :icon="uiIcons.print" />
                پرینت / دانلود
            </button>
            <span class="text-lg font-bold text-gray-900">{{ siteName }}</span>
        </div>

        <div
            class="rounded-2xl border border-gray-200 bg-white p-6 print:rounded-none print:border-0 print:p-0"
        >
            <h1 class="mb-6 text-center text-base font-bold text-gray-900">رسید خرید</h1>

            <div
                class="mb-6 grid grid-cols-1 gap-4 border-b border-gray-100 pb-6 text-sm sm:grid-cols-2"
            >
                <div>
                    <p class="mb-1 font-bold text-gray-800">فروشنده</p>
                    <p class="text-gray-600">{{ siteName }}</p>
                    <p v-if="contact.phone" class="text-gray-500" dir="ltr">{{ contact.phone }}</p>
                    <p v-if="contact.address" class="text-gray-500">{{ contact.address }}</p>
                </div>
                <div class="sm:text-left">
                    <p class="mb-1 font-bold text-gray-800">اطلاعات سفارش</p>
                    <p class="text-gray-600">
                        کد رهگیری:
                        <span dir="ltr">{{ toPersianDigits(order.trackingCode) }}</span>
                    </p>
                    <p class="text-gray-600">تاریخ: {{ formatDate(order.createdAt) }}</p>
                </div>
            </div>

            <div v-if="order.address" class="mb-6 border-b border-gray-100 pb-6 text-sm">
                <p class="mb-1 font-bold text-gray-800">خریدار</p>
                <p class="text-gray-600">{{ order.address.name }}</p>
                <p class="text-gray-600" dir="ltr">{{ order.address.phone }}</p>
                <p class="text-gray-600">{{ fullAddress }}</p>
            </div>

            <div class="mb-6 overflow-x-auto">
                <table class="w-full border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-gray-500">
                            <th class="py-2 pr-2 text-right font-medium">ردیف</th>
                            <th class="py-2 text-right font-medium">شرح کالا</th>
                            <th class="py-2 text-center font-medium">تعداد</th>
                            <th class="py-2 text-left font-medium">مبلغ واحد</th>
                            <th class="py-2 text-left font-medium">تخفیف</th>
                            <th class="py-2 pl-2 text-left font-medium">مبلغ کل</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(line, index) in order.lines"
                            :key="index"
                            class="border-b border-gray-100 text-gray-700"
                        >
                            <td class="py-3 pr-2">{{ toPersianDigits(index + 1) }}</td>
                            <td class="py-3">{{ line.heading }}</td>
                            <td class="py-3 text-center">{{ toPersianDigits(line.quantity) }}</td>
                            <td class="py-3 text-left">{{ formatPrice(line.unitPrice) }}</td>
                            <td class="py-3 text-left">{{ formatPrice(lineDiscount(line)) }}</td>
                            <td class="py-3 pl-2 text-left font-bold">
                                {{ formatPrice(line.finalPrice) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <dl class="mr-auto flex w-full max-w-xs flex-col gap-2 text-sm">
                <div class="flex items-center justify-between text-gray-600">
                    <dt>جمع کالاها</dt>
                    <dd>{{ formatPrice(order.totalProductsPrice) }}</dd>
                </div>
                <div
                    v-if="order.discount > 0"
                    class="flex items-center justify-between text-green-600"
                >
                    <dt>تخفیف</dt>
                    <dd>{{ formatPrice(order.discount) }}</dd>
                </div>
                <div class="flex items-center justify-between text-gray-600">
                    <dt>هزینه ارسال</dt>
                    <dd>
                        {{ order.shippingCost === 0 ? 'رایگان' : formatPrice(order.shippingCost) }}
                    </dd>
                </div>
                <div
                    class="flex items-center justify-between border-t border-gray-200 pt-2 text-base font-bold text-gray-900"
                >
                    <dt>مبلغ نهایی</dt>
                    <dd>{{ formatPrice(order.totalPrice) }}</dd>
                </div>
            </dl>
        </div>
    </div>
</template>
