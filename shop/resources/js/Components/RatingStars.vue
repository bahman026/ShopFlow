<script setup>
import { computed } from 'vue';
import Icon from '@/Components/Icon.vue';
import { uiIcons } from '@/fontawesome';

const props = defineProps({
    rating: {
        type: Number,
        default: 0,
    },
    count: {
        type: Number,
        default: null,
    },
});

const stars = computed(() =>
    Array.from({ length: 5 }, (_, index) => index < Math.round(props.rating)),
);
</script>

<template>
    <div class="flex items-center gap-2">
        <div class="flex items-center gap-0.5 text-sm">
            <Icon
                v-for="(filled, index) in stars"
                :key="index"
                :icon="uiIcons.star"
                :class="filled ? 'text-amber-400' : 'text-gray-200'"
            />
        </div>
        <span v-if="count !== null" class="text-xs text-gray-400"> ({{ count }}) </span>
    </div>
</template>
