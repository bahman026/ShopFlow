<script setup>
import AppLink from '@/Components/AppLink.vue';
import SectionHeading from '@/Components/SectionHeading.vue';

defineProps({
    brands: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <section v-if="brands.length">
        <SectionHeading title="برندهای منتخب" />

        <div class="grid grid-cols-3 gap-4 sm:grid-cols-4 md:grid-cols-6">
            <AppLink
                v-for="brand in brands"
                :key="brand.id"
                :href="brand.url"
                class="flex items-center justify-center rounded-xl border border-gray-100 bg-white p-4 transition hover:border-brand"
            >
                <img
                    v-if="brand.image"
                    :src="brand.image.url"
                    :alt="brand.image.alt || brand.heading"
                    loading="lazy"
                    class="h-12 w-full object-contain"
                >
                <span v-else class="text-sm font-medium text-gray-700">
                    {{ brand.heading }}
                </span>
            </AppLink>
        </div>
    </section>
</template>
