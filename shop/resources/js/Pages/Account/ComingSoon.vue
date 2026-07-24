<script setup>
import AppHead from '@/Components/AppHead.vue';
import AccountLayout from '@/Layouts/AccountLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';

defineProps({
    title: { type: String, required: true },
});
</script>

<template>
    <AppHead :title="title" :noindex="true" />

    <AccountLayout>
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h1 class="mb-6 text-lg font-bold text-gray-900">{{ title }}</h1>
            <EmptyState title="به‌زودی" description="این بخش به‌زودی در دسترس قرار می‌گیرد." />
        </div>
    </AccountLayout>
</template>
