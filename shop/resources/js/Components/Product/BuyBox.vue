<script setup>
import { computed, ref, watch } from 'vue';
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
});

const { formatPrice, toPersianDigits } = useFormat();

const quantity = ref(1);
const hasDiscount = computed(() => props.salePrice !== null && props.salePrice < props.price);
const finalPrice = computed(() => (hasDiscount.value ? props.salePrice : props.price));

// Highest quantity the customer may pick; null inventory means no known cap.
const maxQuantity = computed(() =>
    props.inventory === null ? Infinity : Math.max(0, props.inventory),
);
const atMax = computed(() => quantity.value >= maxQuantity.value);

const trust = [
    { icon: uiIcons.shield, label: 'تضمین اصالت کالا' },
    { icon: uiIcons.returns, label: '۷ روز ضمانت بازگشت' },
    { icon: uiIcons.truck, label: 'ارسال سریع' },
];

function changeQuantity(delta) {
    const next = quantity.value + delta;

    quantity.value = Math.min(maxQuantity.value, Math.max(1, next));
}

// Reset to 1 and clamp whenever the selected variety (its stock) changes.
watch(
    () => [props.inventory, props.selected, props.inStock],
    () => {
        quantity.value = 1;
    },
);
</script>

<template>
    <div class="flex flex-col gap-4 rounded-2xl border border-gray-100 bg-white p-4">
        <ul class="flex flex-col gap-3">
            <li
                v-for="item in trust"
                :key="item.label"
                class="flex items-center gap-2 text-sm text-gray-600"
            >
                <Icon
                    :icon="item.icon"
                    class="text-brand"
                />
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

            <div
                v-else-if="inStock"
                class="flex flex-col gap-3"
            >
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">قیمت</span>
                    <span
                        v-if="hasDiscount && discountPercent"
                        class="rounded-full bg-brand px-2 py-0.5 text-xs font-bold text-white"
                    >
                        {{ toPersianDigits(discountPercent) }}٪ تخفیف
                    </span>
                </div>

                <div class="flex items-baseline justify-between">
                    <span
                        v-if="hasDiscount"
                        class="text-sm text-gray-400 line-through"
                    >
                        {{ formatPrice(price) }}
                    </span>
                    <span class="text-lg font-bold text-gray-900">
                        {{ formatPrice(finalPrice) }}
                    </span>
                </div>

                <div class="flex items-center justify-between gap-3">
                    <span class="text-sm text-gray-500">تعداد</span>
                    <div class="flex h-10 items-center rounded-lg border border-gray-200">
                        <button
                            type="button"
                            class="px-3 text-gray-600 transition hover:text-brand disabled:text-gray-300"
                            aria-label="افزایش"
                            :disabled="atMax"
                            @click="changeQuantity(1)"
                        >
                            <Icon
                                :icon="uiIcons.plus"
                                class="shrink-0 text-sm"
                            />
                        </button>
                        <span class="min-w-[2rem] text-center text-sm font-medium">
                            {{ toPersianDigits(quantity) }}
                        </span>
                        <button
                            type="button"
                            class="px-3 text-gray-600 transition hover:text-brand disabled:text-gray-300"
                            aria-label="کاهش"
                            :disabled="quantity <= 1"
                            @click="changeQuantity(-1)"
                        >
                            <Icon
                                :icon="uiIcons.minus"
                                class="shrink-0 text-sm"
                            />
                        </button>
                    </div>
                </div>

                <p
                    v-if="atMax && inventory !== null"
                    class="text-xs text-gray-400"
                >
                    بیشترین تعداد موجود: {{ toPersianDigits(inventory) }}
                </p>

                <button
                    type="button"
                    class="h-12 w-full rounded-xl bg-brand text-sm font-bold text-white transition hover:bg-brand/90"
                >
                    افزودن به سبد خرید
                </button>
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
