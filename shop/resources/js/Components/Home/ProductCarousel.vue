<script setup>
import { ref } from 'vue';
import Icon from '@/Components/Icon.vue';
import ProductCard from '@/Components/ProductCard.vue';
import SectionHeading from '@/Components/SectionHeading.vue';
import { uiIcons } from '@/fontawesome';

defineProps({
    title: {
        type: String,
        required: true,
    },
    viewAllUrl: {
        type: String,
        default: '',
    },
    products: {
        type: Array,
        default: () => [],
    },
});

const track = ref(null);

// Scroll by roughly one viewport; sign is flipped because the layout is RTL.
function scroll(direction) {
    if (track.value === null) {
        return;
    }

    track.value.scrollBy({ left: direction * track.value.clientWidth * 0.8, behavior: 'smooth' });
}
</script>

<template>
    <section v-if="products.length">
        <SectionHeading :title="title" :view-all-url="viewAllUrl" />

        <div class="relative">
            <div
                ref="track"
                class="flex snap-x snap-mandatory gap-4 overflow-x-auto scroll-smooth pb-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
            >
                <div
                    v-for="product in products"
                    :key="product.id"
                    class="w-40 shrink-0 snap-start sm:w-48"
                >
                    <ProductCard :product="product" />
                </div>
            </div>

            <button
                type="button"
                class="absolute top-1/2 -right-2 hidden -translate-y-1/2 rounded-full border border-gray-200 bg-white p-2 text-gray-700 shadow-sm transition hover:text-brand md:block"
                aria-label="قبلی"
                @click="scroll(1)"
            >
                <Icon :icon="uiIcons.chevronRight" />
            </button>
            <button
                type="button"
                class="absolute top-1/2 -left-2 hidden -translate-y-1/2 rounded-full border border-gray-200 bg-white p-2 text-gray-700 shadow-sm transition hover:text-brand md:block"
                aria-label="بعدی"
                @click="scroll(-1)"
            >
                <Icon :icon="uiIcons.chevronLeft" />
            </button>
        </div>
    </section>
</template>
