<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppHead from '@/Components/AppHead.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import ProductCard from '@/Components/ProductCard.vue';
import Pagination from '@/Components/Pagination.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { toPersianDigits } from '@/composables/useFormat';

const props = defineProps({
    query: {
        type: String,
        default: '',
    },
    products: {
        type: Object,
        required: true,
    },
    applied: {
        type: Object,
        required: true,
    },
});

const sortOptions = [
    { value: 'newest', label: 'جدیدترین' },
    { value: 'cheapest', label: 'ارزان‌ترین' },
    { value: 'expensive', label: 'گران‌ترین' },
    { value: 'popular', label: 'پربازدیدترین' },
];

const metaTitle = computed(() =>
    props.query ? `جستجو برای «${props.query}»` : 'جستجو در فروشگاه',
);

const heading = computed(() =>
    props.query ? `نتایج جستجو برای «${props.query}»` : 'جستجو در فروشگاه',
);

function navigate(overrides = {}) {
    const state = {
        sort: props.applied.sort,
        page: props.products.meta.currentPage,
        ...overrides,
    };

    const params = { q: props.query };

    if (state.sort !== 'newest') {
        params.sort = state.sort;
    }
    if (state.page > 1) {
        params.page = state.page;
    }

    router.get('/search', params, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
}

function onSort(event) {
    navigate({ sort: event.target.value, page: 1 });
}

function onPage(page) {
    navigate({ page });
}
</script>

<template>
    <AppHead :title="metaTitle" :description="metaTitle" :noindex="true" />

    <AppLayout>
        <div class="flex flex-col gap-6">
            <header class="flex flex-col gap-2">
                <h1 class="text-xl font-bold text-gray-900">{{ heading }}</h1>
                <p v-if="query" class="text-sm text-gray-500">
                    {{ toPersianDigits(products.meta.total) }} کالا یافت شد
                </p>
            </header>

            <EmptyState
                v-if="!query"
                title="عبارتی برای جستجو وارد کنید"
                description="نام کالا یا برند مورد نظر خود را در نوار جستجو بنویسید."
            />

            <template v-else>
                <div
                    v-if="products.data.length"
                    class="flex items-center justify-end rounded-xl border border-gray-100 bg-white p-3"
                >
                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <span class="hidden sm:inline">مرتب‌سازی:</span>
                        <select
                            class="focus:border-brand rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none"
                            :value="applied.sort"
                            @change="onSort"
                        >
                            <option
                                v-for="option in sortOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                    </label>
                </div>

                <div
                    v-if="products.data.length"
                    class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4"
                >
                    <ProductCard
                        v-for="product in products.data"
                        :key="product.id"
                        :product="product"
                    />
                </div>

                <EmptyState
                    v-else
                    title="کالایی یافت نشد"
                    description="برای این عبارت نتیجه‌ای پیدا نشد. عبارت دیگری را امتحان کنید."
                />

                <Pagination
                    :current-page="products.meta.currentPage"
                    :last-page="products.meta.lastPage"
                    @change="onPage"
                />
            </template>
        </div>
    </AppLayout>
</template>
