<script setup>
import { computed, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppHead from '@/Components/AppHead.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import ProductCard from '@/Components/ProductCard.vue';
import Pagination from '@/Components/Pagination.vue';
import EmptyState from '@/Components/EmptyState.vue';
import CategoryFilters from '@/Components/Category/CategoryFilters.vue';
import CategoryToolbar from '@/Components/Category/CategoryToolbar.vue';
import { breadcrumbJsonLd } from '@/seo';

const props = defineProps({
    tag: {
        type: Object,
        required: true,
    },
    breadcrumbs: {
        type: Array,
        default: () => [],
    },
    products: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
    applied: {
        type: Object,
        required: true,
    },
});

const showFilters = ref(false);

const page = usePage();
const seo = computed(() => page.props.seo ?? {});

const metaTitle = computed(() => props.tag.title || props.tag.name);
const metaDescription = computed(
    () =>
        props.tag.description ||
        `خرید آنلاین ${props.tag.name} با بهترین قیمت از فروشگاه اینترنتی.`,
);

const jsonLd = computed(() => [breadcrumbJsonLd(props.breadcrumbs, seo.value.origin)]);

function navigate(overrides = {}) {
    const state = {
        brands: props.applied.brands,
        minPrice: props.applied.minPrice,
        maxPrice: props.applied.maxPrice,
        inStock: props.applied.inStock,
        sort: props.applied.sort,
        page: props.products.meta.currentPage,
        ...overrides,
    };

    const params = {};

    if (state.sort !== 'newest') {
        params.sort = state.sort;
    }
    if (state.brands.length) {
        params.brands = state.brands;
    }
    if (state.minPrice !== null && state.minPrice !== '' && state.minPrice !== undefined) {
        params.min_price = state.minPrice;
    }
    if (state.maxPrice !== null && state.maxPrice !== '' && state.maxPrice !== undefined) {
        params.max_price = state.maxPrice;
    }
    if (state.inStock) {
        params.in_stock = 1;
    }
    if (state.page > 1) {
        params.page = state.page;
    }

    router.get(props.tag.url, params, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
}

function onSort(value) {
    navigate({ sort: value, page: 1 });
}

function onApply(payload) {
    navigate({ ...payload, page: 1 });
}

function onReset() {
    showFilters.value = false;
    router.get(props.tag.url, {}, { preserveScroll: true, preserveState: true, replace: true });
}

function onPage(value) {
    navigate({ page: value });
}
</script>

<template>
    <AppHead
        :title="metaTitle"
        :description="metaDescription"
        :canonical="tag.canonical || ''"
        :noindex="tag.noIndex"
        :json-ld="jsonLd"
    />

    <AppLayout>
        <div class="flex flex-col gap-6">
            <Breadcrumbs :items="breadcrumbs" />

            <header class="flex flex-col gap-2">
                <h1 class="text-xl font-bold text-gray-900">{{ tag.name }}</h1>
                <p v-if="tag.description" class="text-sm leading-relaxed text-gray-500">
                    {{ tag.description }}
                </p>
            </header>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <div class="lg:col-span-3" :class="showFilters ? 'block' : 'hidden lg:block'">
                    <CategoryFilters
                        :filters="filters"
                        :applied="applied"
                        @apply="onApply"
                        @reset="onReset"
                        @close="showFilters = false"
                    />
                </div>

                <div class="flex flex-col gap-4 lg:col-span-9">
                    <CategoryToolbar
                        :total="products.meta.total"
                        :sort="applied.sort"
                        @update:sort="onSort"
                        @toggle-filters="showFilters = !showFilters"
                    />

                    <div v-if="products.data.length" class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                        <ProductCard
                            v-for="product in products.data"
                            :key="product.id"
                            :product="product"
                        />
                    </div>

                    <EmptyState
                        v-else
                        title="کالایی یافت نشد"
                        description="با فیلترهای انتخاب‌شده محصولی موجود نیست. فیلترها را تغییر دهید."
                    />

                    <Pagination
                        :current-page="products.meta.currentPage"
                        :last-page="products.meta.lastPage"
                        @change="onPage"
                    />
                </div>
            </div>

            <section v-if="tag.content" class="border-t border-gray-100 pt-6">
                <div
                    class="prose prose-sm max-w-none leading-loose text-gray-700"
                    v-html="tag.content"
                />
            </section>
        </div>
    </AppLayout>
</template>
