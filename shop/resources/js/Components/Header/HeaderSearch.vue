<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Icon from '@/Components/Icon.vue';
import { uiIcons } from '@/fontawesome';

const query = ref('');
const categories = ref([]);
const products = ref([]);
const open = ref(false);
const root = ref(null);

let debounce = null;
let token = 0;

function submit() {
    const term = query.value.trim();

    if (term === '') {
        return;
    }

    open.value = false;
    router.get('/search', { q: term });
}

function onInput() {
    const term = query.value.trim();

    if (debounce) {
        clearTimeout(debounce);
    }

    if (term === '') {
        categories.value = [];
        products.value = [];
        open.value = false;

        return;
    }

    debounce = setTimeout(() => fetchSuggestions(term), 250);
}

async function fetchSuggestions(term) {
    const current = ++token;

    try {
        const response = await fetch('/search/suggest?q=' + encodeURIComponent(term), {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok || current !== token) {
            return;
        }

        const data = await response.json();

        categories.value = data.categories ?? [];
        products.value = data.products ?? [];
        open.value = categories.value.length > 0 || products.value.length > 0;
    } catch {
        // Network hiccup: keep the box usable, just no suggestions.
    }
}

function go(url) {
    open.value = false;
    router.get(url);
}

function onClickOutside(event) {
    if (root.value && !root.value.contains(event.target)) {
        open.value = false;
    }
}

onMounted(() => document.addEventListener('mousedown', onClickOutside));
onBeforeUnmount(() => document.removeEventListener('mousedown', onClickOutside));
</script>

<template>
    <div ref="root" class="relative w-full">
        <form
            class="focus-within:ring-brand flex w-full items-center gap-2 rounded-xl bg-gray-100 px-3 py-2 focus-within:ring-2"
            role="search"
            @submit.prevent="submit"
        >
            <Icon :icon="uiIcons.search" class="text-gray-400" />
            <input
                v-model="query"
                type="search"
                name="q"
                placeholder="جستجو در فروشگاه..."
                autocomplete="off"
                class="w-full bg-transparent text-sm text-gray-700 placeholder-gray-400 focus:outline-none"
                aria-label="جستجو"
                @input="onInput"
                @focus="onInput"
                @keydown.esc="open = false"
            />
        </form>

        <div
            v-if="open"
            class="absolute inset-x-0 top-full z-50 mt-2 overflow-hidden rounded-xl border border-gray-100 bg-white shadow-lg"
        >
            <ul class="max-h-96 overflow-y-auto py-1 text-sm">
                <li v-for="category in categories" :key="'c-' + category.url">
                    <button
                        type="button"
                        class="flex w-full items-center gap-3 px-4 py-2.5 text-right hover:bg-gray-50"
                        @click="go(category.url)"
                    >
                        <Icon :icon="uiIcons.grid" class="text-gray-400" />
                        <span class="flex-1 truncate font-medium text-gray-800">
                            {{ category.heading }}
                        </span>
                        <span class="text-brand shrink-0 text-xs">دسته‌بندی</span>
                    </button>
                </li>

                <li v-for="product in products" :key="'p-' + product.url">
                    <button
                        type="button"
                        class="flex w-full items-center gap-3 px-4 py-2.5 text-right hover:bg-gray-50"
                        @click="go(product.url)"
                    >
                        <Icon :icon="uiIcons.search" class="text-gray-400" />
                        <span class="flex-1 truncate text-gray-700">{{ product.heading }}</span>
                        <span v-if="product.categoryHeading" class="text-brand shrink-0 text-xs">
                            در {{ product.categoryHeading }}
                        </span>
                    </button>
                </li>
            </ul>
        </div>
    </div>
</template>
