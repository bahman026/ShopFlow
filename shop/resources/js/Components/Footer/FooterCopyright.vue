<script setup>
defineProps({
    text: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <div v-if="text" class="border-t border-gray-200 py-5 text-center text-sm text-gray-500">
        {{ text }}
    </div>
</template>
