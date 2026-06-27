<script setup>
import { computed } from 'vue';
import AppHead from '@/Components/AppHead.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';

const props = defineProps({
    page: {
        type: Object,
        required: true,
    },
    breadcrumbs: {
        type: Array,
        default: () => [],
    },
});

const metaTitle = computed(() => props.page.title || props.page.heading);
const metaDescription = computed(() => props.page.description || props.page.heading);

const jsonLd = computed(() => [
    {
        '@context': 'https://schema.org',
        '@type': 'BreadcrumbList',
        itemListElement: props.breadcrumbs.map((item, index) => ({
            '@type': 'ListItem',
            position: index + 1,
            name: item.heading,
        })),
    },
]);
</script>

<template>
    <AppHead
        :title="metaTitle"
        :description="metaDescription"
        :canonical="page.canonical || ''"
        :image="page.image ? page.image.url : ''"
        :noindex="page.noIndex"
        :json-ld="jsonLd"
    />

    <AppLayout>
        <article class="flex flex-col gap-6">
            <Breadcrumbs :items="breadcrumbs" />

            <header>
                <h1 class="text-2xl font-bold text-gray-900">{{ page.heading }}</h1>
            </header>

            <img
                v-if="page.image"
                :src="page.image.url"
                :alt="page.image.alt || page.heading"
                class="w-full rounded-xl border border-gray-100 object-cover"
            />

            <div
                v-if="page.content"
                class="prose prose-sm max-w-none leading-loose text-gray-700"
                v-html="page.content"
            />
        </article>
    </AppLayout>
</template>
