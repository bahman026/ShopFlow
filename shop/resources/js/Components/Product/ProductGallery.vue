<script setup>
import { ref, watch } from 'vue';
import Icon from '@/Components/Icon.vue';
import { uiIcons } from '@/fontawesome';

const props = defineProps({
    images: {
        type: Array,
        default: () => [],
    },
    activeUrl: {
        type: String,
        default: null,
    },
    alt: {
        type: String,
        default: '',
    },
});

const activeIndex = ref(0);

watch(
    () => props.images,
    () => {
        activeIndex.value = 0;
    },
);

// Switch the main image to the selected variety's photo when it's in the list.
watch(
    () => props.activeUrl,
    (url) => {
        if (!url) {
            return;
        }

        const index = props.images.findIndex((image) => image.url === url);

        if (index !== -1) {
            activeIndex.value = index;
        }
    },
    { immediate: true },
);
</script>

<template>
    <div class="flex flex-col gap-3">
        <div class="flex aspect-square items-center justify-center overflow-hidden rounded-2xl border border-gray-100 bg-gray-50">
            <img
                v-if="images.length"
                :src="images[activeIndex].url"
                :alt="images[activeIndex].alt || alt"
                class="h-full w-full object-contain"
            >
            <Icon
                v-else
                :icon="uiIcons.image"
                class="text-6xl text-gray-300"
            />
        </div>

        <div
            v-if="images.length > 1"
            class="flex flex-wrap gap-2"
        >
            <button
                v-for="(image, index) in images"
                :key="index"
                type="button"
                class="h-16 w-16 overflow-hidden rounded-lg border bg-white transition"
                :class="index === activeIndex ? 'border-brand' : 'border-gray-200 hover:border-gray-300'"
                @click="activeIndex = index"
            >
                <img
                    :src="image.url"
                    :alt="image.alt || alt"
                    loading="lazy"
                    class="h-full w-full object-contain"
                >
            </button>
        </div>
    </div>
</template>
