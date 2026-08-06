<script setup>
import { ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { useFormat } from '@/composables/useFormat';

const props = defineProps({
    // The coupon currently previewed on the cart, or null.
    coupon: { type: Object, default: null },
    // Why a previously applied code stopped being valid (cart changed, expired…).
    error: { type: String, default: null },
});

const { formatPrice } = useFormat();

const form = useForm({ code: '' });
const notice = ref(props.error);

watch(
    () => props.error,
    (value) => {
        notice.value = value;
    },
);

function apply() {
    form.post('/cart/coupon', {
        preserveScroll: true,
        onSuccess: () => form.reset('code'),
    });
}

function remove() {
    router.delete('/cart/coupon', { preserveScroll: true });
}
</script>

<template>
    <div class="flex flex-col gap-3 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <h2 class="text-base font-bold text-gray-900">کد تخفیف</h2>

        <template v-if="coupon">
            <div class="flex items-center justify-between rounded-xl bg-green-50 px-4 py-3">
                <div class="flex flex-col gap-1">
                    <span class="text-sm font-bold text-green-700">{{ coupon.name }}</span>
                    <span class="text-xs text-green-600" dir="ltr">{{ coupon.code }}</span>
                </div>

                <button
                    type="button"
                    class="text-xs text-gray-500 transition hover:text-red-600"
                    @click="remove"
                >
                    حذف
                </button>
            </div>

            <p v-if="coupon.discount > 0" class="text-xs text-green-700">
                {{ formatPrice(coupon.discount) }} از مبلغ سبد خرید کم می‌شود.
            </p>

            <p v-if="coupon.freeShipping" class="text-xs text-green-700">
                این کد شامل ارسال رایگان است.
            </p>
        </template>

        <template v-else>
            <form class="flex items-center gap-2" @submit.prevent="apply">
                <input
                    v-model="form.code"
                    type="text"
                    dir="ltr"
                    placeholder="کد تخفیف را وارد کنید"
                    class="focus:border-brand min-w-0 flex-1 rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none"
                />
                <button
                    type="submit"
                    class="rounded-xl bg-gray-900 px-4 py-2.5 text-sm font-bold text-white transition hover:opacity-90 disabled:opacity-50"
                    :disabled="form.processing || !form.code"
                >
                    اعمال
                </button>
            </form>

            <p v-if="form.errors.code" class="text-xs text-red-600">{{ form.errors.code }}</p>
            <p v-else-if="notice" class="text-xs text-amber-600">{{ notice }}</p>
        </template>
    </div>
</template>
