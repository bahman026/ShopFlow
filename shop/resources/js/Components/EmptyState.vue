<script setup>
import Icon from '@/Components/Icon.vue';
import { uiIcons } from '@/fontawesome';

defineProps({
    icon: {
        type: Object,
        default: () => uiIcons.search,
    },
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <div
        class="flex flex-col items-center justify-center gap-3 rounded-xl border border-dashed border-gray-200 bg-white px-6 py-16 text-center"
    >
        <Icon :icon="icon" class="text-4xl text-gray-300" />
        <p class="text-base font-bold text-gray-700">{{ title }}</p>
        <p v-if="description" class="text-sm text-gray-500">{{ description }}</p>
        <slot />
    </div>
</template>
