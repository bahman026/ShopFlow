<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
    variant: {
        type: String,
        default: 'primary',
    },
});

const variants = {
    primary: 'bg-brand text-white hover:opacity-90',
    outline: 'border border-brand text-brand hover:bg-brand hover:text-white',
};
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-medium transition"
        :class="variants[variant]"
    >
        <slot />
    </button>
</template>
