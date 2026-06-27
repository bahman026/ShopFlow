<script setup>
import { computed, ref } from 'vue';
import AppHead from '@/Components/AppHead.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import EmptyState from '@/Components/EmptyState.vue';
import Icon from '@/Components/Icon.vue';
import { faChevronDown } from '@fortawesome/free-solid-svg-icons';

const props = defineProps({
    faqs: {
        type: Array,
        default: () => [],
    },
    breadcrumbs: {
        type: Array,
        default: () => [],
    },
});

const openId = ref(null);

function toggle(id) {
    openId.value = openId.value === id ? null : id;
}

function stripHtml(html) {
    return String(html || '')
        .replace(/<[^>]*>/g, '')
        .trim();
}

const jsonLd = computed(() => {
    const list = [
        {
            '@context': 'https://schema.org',
            '@type': 'BreadcrumbList',
            itemListElement: props.breadcrumbs.map((item, index) => ({
                '@type': 'ListItem',
                position: index + 1,
                name: item.heading,
            })),
        },
    ];

    if (props.faqs.length) {
        list.push({
            '@context': 'https://schema.org',
            '@type': 'FAQPage',
            mainEntity: props.faqs.map((faq) => ({
                '@type': 'Question',
                name: faq.heading,
                acceptedAnswer: {
                    '@type': 'Answer',
                    text: stripHtml(faq.content),
                },
            })),
        });
    }

    return list;
});
</script>

<template>
    <AppHead
        title="سوالات متداول"
        description="پاسخ پرسش‌های پرتکرار درباره خرید، ارسال و پشتیبانی فروشگاه."
        :json-ld="jsonLd"
    />

    <AppLayout>
        <div class="flex flex-col gap-6">
            <Breadcrumbs :items="breadcrumbs" />

            <header>
                <h1 class="text-2xl font-bold text-gray-900">سوالات متداول</h1>
            </header>

            <div v-if="faqs.length" class="flex flex-col gap-3">
                <div
                    v-for="faq in faqs"
                    :key="faq.id"
                    class="overflow-hidden rounded-xl border border-gray-100 bg-white"
                >
                    <button
                        type="button"
                        class="flex w-full items-center justify-between gap-3 px-4 py-3 text-right"
                        @click="toggle(faq.id)"
                    >
                        <span class="text-sm font-bold text-gray-800">{{ faq.heading }}</span>
                        <Icon
                            :icon="faChevronDown"
                            class="text-xs text-gray-400 transition"
                            :class="{ 'rotate-180': openId === faq.id }"
                        />
                    </button>
                    <div
                        v-show="openId === faq.id"
                        class="prose prose-sm max-w-none border-t border-gray-100 px-4 py-3 leading-loose text-gray-700"
                        v-html="faq.content"
                    />
                </div>
            </div>

            <EmptyState
                v-else
                title="سوالی ثبت نشده است"
                description="در حال حاضر پرسشی برای نمایش وجود ندارد."
            />
        </div>
    </AppLayout>
</template>
