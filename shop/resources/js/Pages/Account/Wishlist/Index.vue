<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppHead from '@/Components/AppHead.vue';
import AccountLayout from '@/Layouts/AccountLayout.vue';
import AppLink from '@/Components/AppLink.vue';
import EmptyState from '@/Components/EmptyState.vue';
import Icon from '@/Components/Icon.vue';
import Pagination from '@/Components/Pagination.vue';
import PriceTag from '@/Components/PriceTag.vue';
import { uiIcons } from '@/fontawesome';

defineProps({
    products: {
        type: Object,
        default: () => ({ data: [], meta: { currentPage: 1, lastPage: 1 } }),
    },
});

const removingIds = ref([]);

function onPage(page) {
    router.get('/account/wishlist', page > 1 ? { page } : {}, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
}

function remove(productId) {
    removingIds.value = [...removingIds.value, productId];
    router.post(
        `/products/${productId}/wishlist`,
        {},
        {
            preserveScroll: true,
            preserveState: true,
            onFinish: () => {
                removingIds.value = removingIds.value.filter((id) => id !== productId);
            },
        },
    );
}
</script>

<template>
    <AppHead title="علاقه‌مندی‌های من" :noindex="true" />

    <AccountLayout>
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h1 class="mb-6 text-lg font-bold text-gray-900">علاقه‌مندی‌های من</h1>

            <EmptyState
                v-if="products.data.length === 0"
                :icon="uiIcons.heart"
                title="هنوز کالایی به علاقه‌مندی‌ها اضافه نکرده‌اید"
                description="با کلیک روی «افزودن به علاقه‌مندی‌ها» در صفحه هر کالا، آن را اینجا ذخیره کنید."
            />

            <div v-else class="flex flex-col gap-3">
                <div
                    v-for="product in products.data"
                    :key="product.id"
                    class="flex items-center gap-4 rounded-xl border border-gray-100 p-4 transition hover:border-gray-200"
                >
                    <AppLink :href="product.url" class="shrink-0">
                        <img
                            v-if="product.image"
                            :src="product.image.url"
                            :alt="product.image.alt || product.heading"
                            class="h-16 w-16 rounded-xl border border-gray-100 object-cover"
                        />
                        <div
                            v-else
                            class="flex h-16 w-16 items-center justify-center rounded-xl border border-gray-100 bg-gray-50 text-gray-300"
                        >
                            <Icon :icon="uiIcons.image" />
                        </div>
                    </AppLink>

                    <AppLink :href="product.url" class="min-w-0 flex-1">
                        <p class="line-clamp-2 text-sm font-medium text-gray-800">
                            {{ product.heading }}
                        </p>
                        <div class="mt-2">
                            <PriceTag
                                :price="product.price"
                                :sale-price="product.salePrice"
                                :discount-percent="product.discountPercent"
                            />
                        </div>
                    </AppLink>

                    <button
                        type="button"
                        class="shrink-0 text-gray-400 transition hover:text-red-500 disabled:opacity-50"
                        aria-label="حذف از علاقه‌مندی‌ها"
                        :disabled="removingIds.includes(product.id)"
                        @click="remove(product.id)"
                    >
                        <Icon :icon="uiIcons.trash" />
                    </button>
                </div>

                <Pagination
                    :current-page="products.meta.currentPage"
                    :last-page="products.meta.lastPage"
                    @change="onPage"
                />
            </div>
        </div>
    </AccountLayout>
</template>
