<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
});

const isExternal = computed(() =>
    /^(https?:)?\/\//.test(props.href) ||
    props.href.startsWith('mailto:') ||
    props.href.startsWith('tel:'),
);
</script>

<template>
    <a
        v-if="isExternal"
        :href="href"
        rel="noopener noreferrer"
    >
        <slot />
    </a>
    <Link
        v-else
        :href="href"
    >
        <slot />
    </Link>
</template>
