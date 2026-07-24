<script setup>
import AppLink from '@/Components/AppLink.vue';
import Icon from '@/Components/Icon.vue';
import { uiIcons } from '@/fontawesome';

defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <section v-if="categories.length">
        <div class="grid grid-cols-3 gap-4 sm:grid-cols-4 md:grid-cols-6">
            <AppLink
                v-for="category in categories"
                :key="category.id"
                :href="category.url"
                class="group flex flex-col items-center gap-2 text-center"
            >
                <span
                    class="group-hover:ring-brand flex h-16 w-16 items-center justify-center overflow-hidden rounded-full bg-gray-100 ring-1 ring-gray-100 transition sm:h-20 sm:w-20"
                >
                    <img
                        v-if="category.image"
                        :src="category.image.url"
                        :alt="category.image.alt || category.heading"
                        loading="lazy"
                        class="h-full w-full object-cover"
                    />
                    <Icon v-else :icon="uiIcons.image" class="text-2xl text-gray-300" />
                </span>
                <span class="group-hover:text-brand text-xs text-gray-700 sm:text-sm">
                    {{ category.heading }}
                </span>
            </AppLink>
        </div>
    </section>
</template>
