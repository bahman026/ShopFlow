<script setup>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

defineProps({
    icon: {
        type: [Object, Array, String],
        required: true,
    },
});
</script>

<template>
    <FontAwesomeIcon :icon="icon" />
</template>
