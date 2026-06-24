<script setup>
defineProps({
    title: {
        type: String,
        default: 'ارتباط با ما',
    },
    phone: {
        type: String,
        default: '',
    },
    email: {
        type: String,
        default: '',
    },
    hours: {
        type: Array,
        default: () => [],
    },
    address: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <div
        v-if="phone || email || address || hours.length"
        class="space-y-3 text-sm text-gray-600"
    >
        <p class="font-bold text-gray-800">{{ title }}</p>

        <p v-if="phone">
            <a
                :href="`tel:${phone}`"
                class="transition hover:text-brand"
            >{{ phone }}</a>
        </p>

        <p v-if="email">
            <a
                :href="`mailto:${email}`"
                class="transition hover:text-brand"
            >{{ email }}</a>
        </p>

        <p
            v-for="line in hours"
            :key="line"
            class="leading-6"
        >
            {{ line }}
        </p>

        <p
            v-if="address"
            class="leading-7"
        >
            {{ address }}
        </p>
    </div>
</template>
