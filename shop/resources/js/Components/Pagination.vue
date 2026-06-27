<script setup>
import { computed } from 'vue';
import Icon from '@/Components/Icon.vue';
import { uiIcons } from '@/fontawesome';
import { toPersianDigits } from '@/composables/useFormat';

const props = defineProps({
    currentPage: {
        type: Number,
        required: true,
    },
    lastPage: {
        type: Number,
        required: true,
    },
});

const emit = defineEmits(['change']);

// Windowed page list with gaps (…) for long ranges.
const pages = computed(() => {
    const total = props.lastPage;
    const current = props.currentPage;

    if (total <= 7) {
        return Array.from({ length: total }, (_, index) => index + 1);
    }

    const around = [current - 1, current, current + 1].filter((page) => page > 1 && page < total);
    const result = [1, ...around, total];
    const withGaps = [];

    result.forEach((page, index) => {
        if (index > 0 && page - result[index - 1] > 1) {
            withGaps.push('gap');
        }
        withGaps.push(page);
    });

    return withGaps;
});

function go(page) {
    if (page < 1 || page > props.lastPage || page === props.currentPage) {
        return;
    }

    emit('change', page);
}
</script>

<template>
    <nav v-if="lastPage > 1" aria-label="صفحه‌بندی" class="flex items-center justify-center gap-1">
        <button
            type="button"
            class="enabled:hover:border-brand enabled:hover:text-brand flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 text-gray-600 transition disabled:opacity-40"
            :disabled="currentPage <= 1"
            aria-label="صفحه قبل"
            @click="go(currentPage - 1)"
        >
            <Icon :icon="uiIcons.chevronRight" class="text-xs" />
        </button>

        <template v-for="(page, index) in pages" :key="index">
            <span v-if="page === 'gap'" class="px-2 text-gray-400">…</span>
            <button
                v-else
                type="button"
                class="flex h-9 min-w-9 items-center justify-center rounded-lg border px-2 text-sm transition"
                :class="
                    page === currentPage
                        ? 'border-brand bg-brand text-white'
                        : 'hover:border-brand hover:text-brand border-gray-200 text-gray-600'
                "
                @click="go(page)"
            >
                {{ toPersianDigits(page) }}
            </button>
        </template>

        <button
            type="button"
            class="enabled:hover:border-brand enabled:hover:text-brand flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 text-gray-600 transition disabled:opacity-40"
            :disabled="currentPage >= lastPage"
            aria-label="صفحه بعد"
            @click="go(currentPage + 1)"
        >
            <Icon :icon="uiIcons.chevronLeft" class="text-xs" />
        </button>
    </nav>
</template>
