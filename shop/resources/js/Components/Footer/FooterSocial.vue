<script setup>
import Icon from '@/Components/Icon.vue';
import { socialIcons } from '@/fontawesome';

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

const iconFor = (social) => {
    const haystack = `${social.url ?? ''} ${social.name ?? ''}`.toLowerCase();

    if (haystack.includes('instagram') || haystack.includes('اینستاگرام')) {
        return socialIcons.instagram;
    }

    if (haystack.includes('t.me') || haystack.includes('telegram') || haystack.includes('تلگرام')) {
        return socialIcons.telegram;
    }

    if (haystack.includes('linkedin') || haystack.includes('لینکدین')) {
        return socialIcons.linkedin;
    }

    return socialIcons.fallback;
};
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
                class="hover:border-brand hover:text-brand flex h-10 w-10 items-center justify-center rounded-full border border-gray-300 text-gray-500 transition"
            >
                <Icon :icon="iconFor(social)" />
            </a>
        </div>
    </div>
</template>
