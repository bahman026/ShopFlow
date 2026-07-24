<script setup>
import AppLink from '@/Components/AppLink.vue';

defineProps({
    title: {
        type: String,
        default: '',
    },
    links: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <div v-if="links.length">
        <p v-if="title" class="mb-4 font-bold text-gray-800">
            {{ title }}
        </p>

        <ul class="space-y-2">
            <li v-for="link in links" :key="link.url">
                <AppLink :href="link.url" class="hover:text-brand text-sm text-gray-600 transition">
                    {{ link.label }}
                    <span
                        v-if="link.badge"
                        class="bg-brand me-1 rounded px-1.5 py-0.5 text-xs text-white"
                    >
                        {{ link.badge }}
                    </span>
                </AppLink>
            </li>
        </ul>
    </div>
</template>
