<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLink from '@/Components/AppLink.vue';
import Icon from '@/Components/Icon.vue';
import HeaderSearch from '@/Components/Header/HeaderSearch.vue';
import HeaderActions from '@/Components/Header/HeaderActions.vue';
import CategoryMenu from '@/Components/Header/CategoryMenu.vue';
import { uiIcons } from '@/fontawesome';

defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
});

const mobileOpen = ref(false);
</script>

<template>
    <header class="sticky top-0 z-30 border-b border-gray-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center gap-4 px-4 py-3">
            <button
                type="button"
                class="rounded-lg p-2 text-gray-700 transition hover:bg-gray-100 md:hidden"
                aria-label="منو"
                @click="mobileOpen = !mobileOpen"
            >
                <Icon :icon="mobileOpen ? uiIcons.close : uiIcons.menu" />
            </button>

            <Link href="/" class="shrink-0 text-xl font-bold text-brand">
                ShopFlow
            </Link>

            <div class="hidden flex-1 sm:block">
                <HeaderSearch />
            </div>

            <div class="mr-auto">
                <HeaderActions />
            </div>
        </div>

        <div class="border-t border-gray-100 px-4 py-1 sm:hidden">
            <HeaderSearch />
        </div>

        <div class="mx-auto hidden max-w-6xl px-4 md:block">
            <CategoryMenu :categories="categories" />
        </div>

        <div v-if="mobileOpen" class="border-t border-gray-100 md:hidden">
            <nav class="mx-auto max-w-6xl px-4 py-2" aria-label="دسته‌بندی‌ها">
                <details
                    v-for="category in categories"
                    :key="category.url"
                    class="border-b border-gray-50 last:border-0"
                >
                    <summary class="flex cursor-pointer items-center justify-between py-2 text-sm text-gray-700">
                        <AppLink :href="category.url" class="hover:text-brand">
                            {{ category.heading }}
                        </AppLink>
                        <Icon
                            v-if="category.children.length"
                            :icon="uiIcons.chevronDown"
                            class="text-xs text-gray-400"
                        />
                    </summary>
                    <div v-if="category.children.length" class="pb-2 pr-3">
                        <AppLink
                            v-for="child in category.children"
                            :key="child.url"
                            :href="child.url"
                            class="block py-1.5 text-sm text-gray-500 hover:text-brand"
                        >
                            {{ child.heading }}
                        </AppLink>
                    </div>
                </details>
            </nav>
        </div>
    </header>
</template>
