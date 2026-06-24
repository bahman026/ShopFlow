<script setup>
import AppLink from '@/Components/AppLink.vue';
import Icon from '@/Components/Icon.vue';
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
    viewAllLabel: {
        type: String,
        default: 'مشاهده همه',
    },
});
</script>

<template>
    <div class="mb-4 flex items-center justify-between">
        <h2 class="text-lg font-bold text-gray-900 sm:text-xl">
            {{ title }}
        </h2>

        <AppLink
            v-if="viewAllUrl"
            :href="viewAllUrl"
            class="flex items-center gap-1 text-sm font-medium text-brand hover:opacity-80"
        >
            {{ viewAllLabel }}
            <Icon :icon="uiIcons.chevronLeft" class="text-xs" />
        </AppLink>
    </div>
</template>
