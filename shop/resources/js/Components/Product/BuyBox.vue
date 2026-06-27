<script setup>
import { computed } from 'vue';
import AppLink from '@/Components/AppLink.vue';
import Icon from '@/Components/Icon.vue';
import { uiIcons } from '@/fontawesome';
import { useFormat } from '@/composables/useFormat';

const props = defineProps({
    price: {
        type: Number,
        default: null,
    },
    salePrice: {
        type: Number,
        default: null,
    },
    discountPercent: {
        type: Number,
        default: null,
    },
    inStock: {
        type: Boolean,
        default: false,
    },
    inventory: {
        type: Number,
        default: null,
    },
    selected: {
        type: Boolean,
        default: true,
    },
    processing: {
        type: Boolean,
        default: false,
    },
    cartCount: {
        type: Number,
        default: 0,
    },
});

const emit = defineEmits(['add', 'increase', 'decrease', 'remove']);

const { formatPrice, toPersianDigits } = useFormat();

const hasDiscount = computed(() => props.salePrice !== null && props.salePrice < props.price);
const finalPrice = computed(() => (hasDiscount.value ? props.salePrice : props.price));

// Highest quantity the customer may pick; null inventory means no known cap.
const maxQuantity = computed(() =>
    props.inventory === null ? Infinity : Math.max(0, props.inventory),
);
const atMax = computed(() => props.cartCount >= maxQuantity.value);

const trust = [
    { icon: uiIcons.shield, label: 'تضمین اصالت کالا' },
    { icon: uiIcons.returns, label: '۷ روز ضمانت بازگشت' },
    { icon: uiIcons.truck, label: 'ارسال سریع' },
];
</script>

<template>
    <div class="flex flex-col gap-4 rounded-2xl border border-gray-100 bg-white p-4">
        <ul class="flex flex-col gap-3">
            <li
                v-for="item in trust"
                :key="item.label"
                class="flex items-center gap-2 text-sm text-gray-600"
            >
                <Icon :icon="item.icon" class="text-brand" />
                <span>{{ item.label }}</span>
            </li>
        </ul>

        <div class="border-t border-gray-100 pt-4">
            <p
                v-if="!selected"
                class="rounded-lg bg-gray-50 px-4 py-3 text-center text-sm font-medium text-gray-500"
            >
                لطفاً ویژگی‌های محصول را انتخاب کنید
            </p>

            <div v-else-if="inStock" class="flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">قیمت</span>
                    <span
                        v-if="hasDiscount && discountPercent"
                        class="bg-brand rounded-full px-2 py-0.5 text-xs font-bold text-white"
                    >
                        {{ toPersianDigits(discountPercent) }}٪ تخفیف
                    </span>
                </div>

                <div class="flex items-baseline justify-between">
                    <span v-if="hasDiscount" class="text-sm text-gray-400 line-through">
                        {{ formatPrice(price) }}
                    </span>
                    <span class="text-lg font-bold text-gray-900">
                        {{ formatPrice(finalPrice) }}
                    </span>
                </div>

                <button
                    v-if="cartCount === 0"
                    type="button"
                    class="bg-brand hover:bg-brand/90 h-12 w-full rounded-xl text-sm font-bold text-white transition disabled:opacity-50"
                    :disabled="processing"
                    @click="emit('add')"
                >
                    افزودن به سبد خرید
                </button>

                <template v-else>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-sm text-gray-500">تعداد در سبد خرید</span>
                        <div class="flex h-10 items-center rounded-lg border border-gray-200">
                            <button
                                type="button"
                                class="hover:text-brand px-3 text-gray-600 transition disabled:text-gray-300"
                                aria-label="افزایش"
                                :disabled="processing || atMax"
                                @click="emit('increase')"
                            >
                                <Icon :icon="uiIcons.plus" class="shrink-0 text-sm" />
                            </button>
                            <span class="min-w-[2rem] text-center text-sm font-medium">
                                {{ toPersianDigits(cartCount) }}
                            </span>
                            <button
                                v-if="cartCount > 1"
                                type="button"
                                class="hover:text-brand px-3 text-gray-600 transition disabled:text-gray-300"
                                aria-label="کاهش"
                                :disabled="processing"
                                @click="emit('decrease')"
                            >
                                <Icon :icon="uiIcons.minus" class="shrink-0 text-sm" />
                            </button>
                            <button
                                v-else
                                type="button"
                                class="px-3 text-gray-500 transition hover:text-red-600 disabled:text-gray-300"
                                aria-label="حذف"
                                :disabled="processing"
                                @click="emit('remove')"
                            >
                                <Icon :icon="uiIcons.trash" class="shrink-0 text-sm" />
                            </button>
                        </div>
                    </div>

                    <p v-if="atMax && inventory !== null" class="text-xs text-gray-400">
                        بیشترین تعداد موجود: {{ toPersianDigits(inventory) }}
                    </p>

                    <AppLink
                        href="/cart"
                        class="border-brand text-brand hover:bg-brand flex h-12 w-full items-center justify-center rounded-xl border text-sm font-bold transition hover:text-white"
                    >
                        رفتن به سبد خرید
                    </AppLink>
                </template>
            </div>

            <p
                v-else
                class="rounded-lg bg-gray-50 px-4 py-3 text-center text-sm font-medium text-gray-400"
            >
                ناموجود
            </p>
        </div>
    </div>
</template>
