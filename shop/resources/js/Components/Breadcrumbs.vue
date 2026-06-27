<script setup>
import AppLink from '@/Components/AppLink.vue';
import Icon from '@/Components/Icon.vue';
import { uiIcons } from '@/fontawesome';

defineProps({
    items: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <nav v-if="items.length" aria-label="مسیر" class="text-sm text-gray-500">
        <ol class="flex flex-wrap items-center gap-1">
            <li v-for="(item, index) in items" :key="index" class="flex items-center gap-1">
                <AppLink
                    v-if="item.url && index < items.length - 1"
                    :href="item.url"
                    class="hover:text-brand transition"
                >
                    {{ item.heading }}
                </AppLink>
                <span v-else class="font-medium text-gray-700">
                    {{ item.heading }}
                </span>

                <Icon
                    v-if="index < items.length - 1"
                    :icon="uiIcons.chevronLeft"
                    class="text-xs text-gray-300"
                />
            </li>
        </ol>
    </nav>
</template>
