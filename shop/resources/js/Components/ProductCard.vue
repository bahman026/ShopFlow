<script setup>
import AppLink from '@/Components/AppLink.vue';
import Icon from '@/Components/Icon.vue';
import PriceTag from '@/Components/PriceTag.vue';
import { uiIcons } from '@/fontawesome';

defineProps({
    product: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <AppLink
        :href="product.url"
        new-tab
        class="group hover:border-brand flex h-full flex-col overflow-hidden rounded-xl border border-gray-100 bg-white transition hover:shadow-md"
    >
        <div class="flex aspect-square items-center justify-center overflow-hidden bg-gray-50">
            <img
                v-if="product.image"
                :src="product.image.url"
                :alt="product.image.alt || product.heading"
                loading="lazy"
                class="h-full w-full object-contain transition duration-300 group-hover:scale-105"
            />
            <Icon v-else :icon="uiIcons.image" class="text-4xl text-gray-300" />
        </div>

        <div class="flex flex-1 flex-col gap-3 p-3">
            <h3 class="line-clamp-2 min-h-[2.5rem] text-sm leading-relaxed text-gray-700">
                {{ product.heading }}
            </h3>

            <div class="mt-auto">
                <PriceTag
                    :price="product.price"
                    :sale-price="product.salePrice"
                    :discount-percent="product.discountPercent"
                />
            </div>
        </div>
    </AppLink>
</template>
