<script setup>
import { computed } from 'vue';
import AppLink from '@/Components/AppLink.vue';

/**
 * Renders the banners assigned to one BannerPositionEnum slot.
 *
 * `layout` decides how the slot uses its space — a promo grid across the page,
 * one full-width strip, or a stacked column in a sidebar. The data is the same
 * shape in every case; only the arrangement differs.
 *
 * Renders nothing at all when the slot has no banners with images, so a page
 * can hand it an empty array without guarding.
 */
const props = defineProps({
    banners: {
        type: Array,
        default: () => [],
    },
    layout: {
        type: String,
        default: 'grid',
        validator: (value) => ['grid', 'wide', 'stack'].includes(value),
    },
});

// Only banners that actually have an image are worth rendering.
const visibleBanners = computed(() => props.banners.filter((banner) => banner.image));

// `wide` is a single strip: extra banners assigned to the slot are ignored
// rather than stacked, so an accidental second row can't break the layout.
const renderedBanners = computed(() =>
    props.layout === 'wide' ? visibleBanners.value.slice(0, 1) : visibleBanners.value,
);

const containerClass = computed(
    () =>
        ({
            grid: 'grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3',
            wide: 'grid grid-cols-1',
            stack: 'flex flex-col gap-4',
        })[props.layout],
);

// Each slot pins its own aspect ratio, so one oddly-shaped upload cannot
// stretch a grid row or push the page around. These match the ratios the
// admin crops to (BannerPositionEnum::aspectRatio) — keep them in step.
// A wide strip would be a sliver on a phone, so it stays taller there.
const frameClass = computed(
    () =>
        ({
            grid: 'aspect-[16/9]',
            wide: 'aspect-[3/1] sm:aspect-[5/1]',
            stack: 'aspect-[4/5]',
        })[props.layout],
);
</script>

<template>
    <section v-if="renderedBanners.length">
        <div :class="containerClass">
            <component
                :is="banner.url ? AppLink : 'div'"
                v-for="banner in renderedBanners"
                :key="banner.id"
                :href="banner.url || undefined"
                class="overflow-hidden rounded-xl"
                :class="frameClass"
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
