<script setup>
defineProps({
    varieties: {
        type: Array,
        default: () => [],
    },
    modelValue: {
        type: Number,
        default: null,
    },
});

const emit = defineEmits(['update:modelValue']);

function select(variety) {
    if (!variety.inStock) {
        return;
    }

    emit('update:modelValue', variety.id);
}
</script>

<template>
    <div
        v-if="varieties.length"
        class="flex flex-col gap-3"
    >
        <span class="text-sm font-medium text-gray-700">انتخاب نوع کالا</span>

        <div class="flex flex-wrap gap-2">
            <button
                v-for="variety in varieties"
                :key="variety.id"
                type="button"
                :disabled="!variety.inStock"
                class="flex items-center gap-2 rounded-lg border px-3 py-2 text-sm transition"
                :class="[
                    variety.id === modelValue
                        ? 'border-brand bg-brand/5 text-brand'
                        : 'border-gray-200 text-gray-700 hover:border-gray-300',
                    !variety.inStock ? 'cursor-not-allowed text-gray-300 line-through' : '',
                ]"
                @click="select(variety)"
            >
                <span
                    v-if="variety.color"
                    class="h-4 w-4 rounded-full border border-gray-200"
                    :style="{ backgroundColor: variety.color }"
                />
                <span>{{ variety.label || 'پیش‌فرض' }}</span>
            </button>
        </div>
    </div>
</template>
