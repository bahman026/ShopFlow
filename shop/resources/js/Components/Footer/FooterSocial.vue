<script setup>
defineProps({
    title: {
        type: String,
        default: 'شبکه‌های اجتماعی',
    },
    socials: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <div v-if="socials.length">
        <p class="mb-3 font-bold text-gray-800">{{ title }}</p>

        <div class="flex gap-3">
            <a
                v-for="social in socials"
                :key="social.url"
                :href="social.url"
                :title="social.name"
                :aria-label="social.name"
                rel="noopener noreferrer nofollow"
                class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-300 text-gray-500 transition hover:border-brand hover:text-brand"
            >
                <span class="text-xs">{{ social.name }}</span>
            </a>
        </div>
    </div>
</template>
