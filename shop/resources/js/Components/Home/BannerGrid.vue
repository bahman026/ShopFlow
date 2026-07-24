<script setup>
import { computed } from 'vue';
import AppLink from '@/Components/AppLink.vue';

const props = defineProps({
    banners: {
        type: Array,
        default: () => [],
    },
});

// Only banners that actually have an image are worth rendering.
const visibleBanners = computed(() => props.banners.filter((banner) => banner.image));
</script>

<template>
    <section v-if="visibleBanners.length">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <component
                :is="banner.url ? AppLink : 'div'"
                v-for="banner in visibleBanners"
                :key="banner.id"
                :href="banner.url || undefined"
                class="overflow-hidden rounded-xl"
            >
                <img
                    :src="banner.image.url"
                    :alt="banner.image.alt || banner.heading"
                    loading="lazy"
                    class="h-full w-full object-cover transition duration-300 hover:scale-105"
                />
            </component>
        </div>
    </section>
</template>
