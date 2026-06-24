<script setup>
import { computed } from 'vue';
import { useFormat } from '@/composables/useFormat';

const props = defineProps({
    price: {
        type: Number,
        required: true,
    },
    salePrice: {
        type: Number,
        default: null,
    },
    discountPercent: {
        type: Number,
        default: null,
    },
});

const { formatPrice, toPersianDigits } = useFormat();

const hasDiscount = computed(() => props.salePrice !== null && props.salePrice < props.price);
const finalPrice = computed(() => (hasDiscount.value ? props.salePrice : props.price));
</script>

<template>
    <div class="flex items-center justify-between gap-2">
        <div class="flex flex-col">
            <span
                v-if="hasDiscount"
                class="text-xs text-gray-400 line-through"
            >
                {{ formatPrice(price) }}
            </span>
            <span class="text-sm font-bold text-gray-900">
                {{ formatPrice(finalPrice) }}
            </span>
        </div>

        <span
            v-if="hasDiscount && discountPercent"
            class="rounded-full bg-brand px-2 py-0.5 text-xs font-bold text-white"
        >
            {{ toPersianDigits(discountPercent) }}٪
        </span>
    </div>
</template>
