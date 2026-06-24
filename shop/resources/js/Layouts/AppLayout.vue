<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppHeader from '@/Components/Header/AppHeader.vue';
import AppFooter from '@/Components/Footer/AppFooter.vue';

const page = usePage();

const footer = computed(() => page.props.footer ?? {});
const navCategories = computed(() => page.props.nav?.categories ?? []);
</script>

<template>
    <div class="min-h-screen bg-gray-50 text-gray-900">
        <AppHeader :categories="navCategories" />

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
