<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Icon from '@/Components/Icon.vue';
import { uiIcons } from '@/fontawesome';

const query = ref('');

function submit() {
    const term = query.value.trim();

    if (term === '') {
        return;
    }

    router.get('/search', { q: term });
}
</script>

<template>
    <form
        class="flex w-full items-center gap-2 rounded-xl bg-gray-100 px-3 py-2 focus-within:ring-2 focus-within:ring-brand"
        role="search"
        @submit.prevent="submit"
    >
        <Icon :icon="uiIcons.search" class="text-gray-400" />
        <input
            v-model="query"
            type="search"
            name="q"
            placeholder="جستجو در فروشگاه..."
            class="w-full bg-transparent text-sm text-gray-700 placeholder-gray-400 focus:outline-none"
            aria-label="جستجو"
        >
    </form>
</template>
