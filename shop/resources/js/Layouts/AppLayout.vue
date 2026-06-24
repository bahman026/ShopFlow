<script setup>
import AppFooter from '@/Components/Footer/AppFooter.vue';

defineProps({
    footer: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 text-gray-900">
        <header class="border-b border-gray-200 bg-white">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
                <span class="text-lg font-bold">ShopFlow</span>
                <nav class="flex gap-4 text-sm">
                    <a href="/" class="hover:text-brand">خانه</a>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-8">
            <slot />
        </main>

        <AppFooter
            :columns="footer.columns ?? []"
            :contact="footer.contact ?? {}"
            :socials="footer.socials ?? []"
            :copyright="footer.copyright ?? ''"
        />
    </div>
</template>
