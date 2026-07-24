<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    newTab: {
        type: Boolean,
        default: false,
    },
});

const isExternal = computed(
    () =>
        /^(https?:)?\/\//.test(props.href) ||
        props.href.startsWith('mailto:') ||
        props.href.startsWith('tel:'),
);

const target = computed(() => (props.newTab ? '_blank' : undefined));
const rel = computed(() => (props.newTab || isExternal.value ? 'noopener noreferrer' : undefined));
</script>

<template>
    <a v-if="isExternal" :href="href" :target="target" :rel="rel">
        <slot />
    </a>
    <Link v-else :href="href" :target="target" :rel="rel">
        <slot />
    </Link>
</template>
