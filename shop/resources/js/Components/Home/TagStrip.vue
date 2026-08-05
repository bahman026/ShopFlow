<script setup>
import { computed } from 'vue';
import AppLink from '@/Components/AppLink.vue';

const props = defineProps({
    tags: {
        type: Array,
        default: () => [],
    },
});

// Rendered as big banner-style images, so only tags with an image qualify
// (same rule as the banner grid).
const visibleTags = computed(() => props.tags.filter((tag) => tag.image));
</script>

<template>
    <section v-if="visibleTags.length">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <AppLink
                v-for="tag in visibleTags"
                :key="tag.id"
                :href="tag.url"
                class="overflow-hidden rounded-xl"
            >
                <img
                    :src="tag.image.url"
                    :alt="tag.image.alt || tag.name"
                    loading="lazy"
                    class="h-full w-full object-cover transition duration-300 hover:scale-105"
                />
            </AppLink>
        </div>
    </section>
</template>
