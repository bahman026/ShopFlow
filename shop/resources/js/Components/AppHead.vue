<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';

const props = defineProps({
    title: {
        type: String,
        default: '',
    },
    description: {
        type: String,
        default: '',
    },
    canonical: {
        type: String,
        default: '',
    },
    image: {
        type: String,
        default: '',
    },
    type: {
        type: String,
        default: 'website',
    },
    noindex: {
        type: Boolean,
        default: false,
    },
    jsonLd: {
        type: [Object, Array],
        default: null,
    },
});

const page = usePage();

const seo = computed(() => page.props.seo ?? {});
const siteName = computed(() => seo.value.siteName ?? '');
const locale = computed(() => seo.value.locale ?? 'fa_IR');
const canonicalUrl = computed(() => props.canonical || seo.value.url || '');
const fullTitle = computed(() =>
    props.title ? `${props.title} - ${siteName.value}` : siteName.value,
);
const jsonLdString = computed(() => (props.jsonLd ? JSON.stringify(props.jsonLd) : ''));
</script>

<template>
    <Head :title="title">
        <meta v-if="description" head-key="description" name="description" :content="description" />
        <link v-if="canonicalUrl" head-key="canonical" rel="canonical" :href="canonicalUrl" />
        <meta v-if="noindex" head-key="robots" name="robots" content="noindex, nofollow" />

        <meta head-key="og:type" property="og:type" :content="type" />
        <meta head-key="og:site_name" property="og:site_name" :content="siteName" />
        <meta head-key="og:locale" property="og:locale" :content="locale" />
        <meta head-key="og:title" property="og:title" :content="fullTitle" />
        <meta
            v-if="description"
            head-key="og:description"
            property="og:description"
            :content="description"
        />
        <meta v-if="canonicalUrl" head-key="og:url" property="og:url" :content="canonicalUrl" />
        <meta v-if="image" head-key="og:image" property="og:image" :content="image" />

        <meta
            head-key="twitter:card"
            name="twitter:card"
            :content="image ? 'summary_large_image' : 'summary'"
        />
        <meta head-key="twitter:title" name="twitter:title" :content="fullTitle" />
        <meta
            v-if="description"
            head-key="twitter:description"
            name="twitter:description"
            :content="description"
        />
        <meta v-if="image" head-key="twitter:image" name="twitter:image" :content="image" />

        <!-- eslint-disable vue/no-v-text-v-html-on-component -- JSON-LD must be injected as raw script content -->
        <component
            :is="'script'"
            v-if="jsonLdString"
            head-key="ld-json"
            type="application/ld+json"
            v-html="jsonLdString"
        />
        <!-- eslint-enable vue/no-v-text-v-html-on-component -->
    </Head>
</template>
