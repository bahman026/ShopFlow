<script setup>
import { toPersianDigits } from '@/composables/useFormat';

defineProps({
    total: {
        type: Number,
        default: 0,
    },
    sort: {
        type: String,
        default: 'newest',
    },
});

const emit = defineEmits(['update:sort', 'toggle-filters']);

const sortOptions = [
    { value: 'newest', label: 'جدیدترین' },
    { value: 'cheapest', label: 'ارزان‌ترین' },
    { value: 'expensive', label: 'گران‌ترین' },
    { value: 'popular', label: 'پربازدیدترین' },
];
</script>

<template>
    <div
        class="flex items-center justify-between gap-3 rounded-xl border border-gray-100 bg-white p-3"
    >
        <div class="flex items-center gap-3">
            <button
                type="button"
                class="hover:border-brand hover:text-brand rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-600 transition lg:hidden"
                @click="emit('toggle-filters')"
            >
                فیلترها
            </button>
            <span class="text-sm text-gray-500"> {{ toPersianDigits(total) }} کالا </span>
        </div>

        <label class="flex items-center gap-2 text-sm text-gray-600">
            <span class="hidden sm:inline">مرتب‌سازی:</span>
            <select
                class="focus:border-brand rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none"
                :value="sort"
                @change="emit('update:sort', $event.target.value)"
            >
                <option v-for="option in sortOptions" :key="option.value" :value="option.value">
                    {{ option.label }}
                </option>
            </select>
        </label>
    </div>
</template>
