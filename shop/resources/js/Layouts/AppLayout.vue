<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppFooter from '@/Components/Footer/AppFooter.vue';

const page = usePage();

const footer = computed(() => page.props.footer ?? {});
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
            :about="footer.about ?? ''"
            :columns="footer.columns ?? []"
            :contact="footer.contact ?? {}"
            :socials="footer.socials ?? []"
            :copyright="footer.copyright ?? ''"
        />
    </div>
</template>
