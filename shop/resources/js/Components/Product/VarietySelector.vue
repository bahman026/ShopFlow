<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
    axes: {
        type: Array,
        default: () => [],
    },
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

const hasAxes = computed(() => props.axes.length > 0);

// selected[groupId] = value
const selected = ref({});

function variety(id) {
    return props.varieties.find((item) => item.id === id) ?? null;
}

// A variety's values for one group (it may offer several, e.g. many sizes).
function valuesFor(item, groupId) {
    return (item.options?.[groupId] ?? []).map(String);
}

function carries(item, groupId, value) {
    return valuesFor(item, groupId).includes(String(value));
}

function matches(item, selection) {
    return Object.entries(selection).every(([group, value]) => carries(item, group, value));
}

// Every axis must be chosen before a variety counts as selected.
const isComplete = computed(
    () => hasAxes.value && props.axes.every((axis) => selected.value[axis.id] !== undefined),
);

// The variety that fits the current selection, only once it's complete.
const activeVariety = computed(() => {
    if (!isComplete.value) {
        return null;
    }

    const candidates = props.varieties.filter((item) => matches(item, selected.value));

    return candidates.find((item) => item.inStock) ?? candidates[0] ?? null;
});

// Start with nothing selected unless the parent points at a specific variety.
function initSelection() {
    selected.value = {};

    const start = variety(props.modelValue);

    if (!start) {
        return;
    }

    for (const axis of props.axes) {
        const value = valuesFor(start, axis.id)[0];

        if (value !== undefined) {
            selected.value[axis.id] = value;
        }
    }
}

initSelection();

watch(activeVariety, (item) => {
    emit('update:modelValue', item ? item.id : null);
}, { immediate: true });

function isSelected(groupId, value) {
    return String(selected.value[groupId]) === String(value);
}

// Availability rules:
// - Primary axis (e.g. color) is the variety selector, so it's only limited by
//   stock, never by the secondary axes.
// - Secondary axes (e.g. size) are limited to what the chosen primary offers.
function isAvailable(groupId, value, isPrimary) {
    return props.varieties.some((item) => {
        if (!item.inStock || !carries(item, groupId, value)) {
            return false;
        }

        if (isPrimary) {
            return true;
        }

        return Object.entries(selected.value).every(
            ([group, selectedValue]) =>
                String(group) === String(groupId) || carries(item, group, selectedValue),
        );
    });
}

function selectOption(groupId, value, isPrimary) {
    if (!isAvailable(groupId, value, isPrimary)) {
        return;
    }

    const next = { ...selected.value, [groupId]: value };

    // Drop selections on other axes that no longer have any in-stock match.
    for (const axis of props.axes) {
        if (String(axis.id) === String(groupId) || next[axis.id] === undefined) {
            continue;
        }

        const stillValid = props.varieties.some(
            (item) => item.inStock && matches(item, next),
        );

        if (!stillValid) {
            delete next[axis.id];
        }
    }

    selected.value = next;
}

// Flat fallback: products whose varieties have no grouped attributes.
function selectVariety(item) {
    if (item.inStock) {
        emit('update:modelValue', item.id);
    }
}
</script>

<template>
    <div
        v-if="hasAxes"
        class="flex flex-col gap-4"
    >
        <div
            v-for="axis in axes"
            :key="axis.id"
            class="flex flex-col gap-2"
        >
            <span class="text-sm font-medium text-gray-700">{{ axis.name }}</span>

            <div class="flex flex-wrap gap-2">
                <button
                    v-for="option in axis.options"
                    :key="option.value"
                    type="button"
                    :disabled="!isAvailable(axis.id, option.value, axis.primary)"
                    class="flex items-center gap-2 rounded-lg border px-3 py-2 text-sm transition"
                    :class="[
                        isSelected(axis.id, option.value)
                            ? 'border-brand bg-brand/5 text-brand'
                            : 'border-gray-200 text-gray-700 hover:border-gray-300',
                        !isAvailable(axis.id, option.value, axis.primary) ? 'cursor-not-allowed text-gray-300 line-through' : '',
                    ]"
                    @click="selectOption(axis.id, option.value, axis.primary)"
                >
                    <span
                        v-if="option.color"
                        class="h-4 w-4 rounded-full border border-gray-200"
                        :style="{ backgroundColor: option.color }"
                    />
                    <span>{{ option.value }}</span>
                </button>
            </div>
        </div>
    </div>

    <div
        v-else-if="varieties.length"
        class="flex flex-col gap-3"
    >
        <span class="text-sm font-medium text-gray-700">انتخاب نوع کالا</span>

        <div class="flex flex-wrap gap-2">
            <button
                v-for="item in varieties"
                :key="item.id"
                type="button"
                :disabled="!item.inStock"
                class="flex items-center gap-2 rounded-lg border px-3 py-2 text-sm transition"
                :class="[
                    item.id === modelValue
                        ? 'border-brand bg-brand/5 text-brand'
                        : 'border-gray-200 text-gray-700 hover:border-gray-300',
                    !item.inStock ? 'cursor-not-allowed text-gray-300 line-through' : '',
                ]"
                @click="selectVariety(item)"
            >
                <span
                    v-if="item.color"
                    class="h-4 w-4 rounded-full border border-gray-200"
                    :style="{ backgroundColor: item.color }"
                />
                <span>{{ item.label || 'پیش‌فرض' }}</span>
            </button>
        </div>
    </div>
</template>
