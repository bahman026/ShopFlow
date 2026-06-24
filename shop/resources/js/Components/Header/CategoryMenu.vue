<script setup>
import AppLink from '@/Components/AppLink.vue';
import Icon from '@/Components/Icon.vue';
import { uiIcons } from '@/fontawesome';

defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <nav v-if="categories.length" class="hidden md:block" aria-label="دسته‌بندی‌ها">
        <ul class="flex items-center gap-1">
            <li
                v-for="category in categories"
                :key="category.url"
                class="group relative"
            >
                <AppLink
                    :href="category.url"
                    class="flex items-center gap-1 rounded-lg px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-50 hover:text-brand"
                >
                    {{ category.heading }}
                    <Icon
                        v-if="category.children.length"
                        :icon="uiIcons.chevronDown"
                        class="text-xs text-gray-400"
                    />
                </AppLink>

                <div
                    v-if="category.children.length"
                    class="invisible absolute top-full right-0 z-20 min-w-48 translate-y-1 rounded-xl border border-gray-100 bg-white p-2 opacity-0 shadow-lg transition group-hover:visible group-hover:translate-y-0 group-hover:opacity-100"
                >
                    <AppLink
                        v-for="child in category.children"
                        :key="child.url"
                        :href="child.url"
                        class="block rounded-lg px-3 py-2 text-sm text-gray-600 transition hover:bg-gray-50 hover:text-brand"
                    >
                        {{ child.heading }}
                    </AppLink>
                </div>
            </li>
        </ul>
    </nav>
</template>
