<script setup>
import { computed } from 'vue';
import Icon from '@/Components/Icon.vue';
import { uiIcons } from '@/fontawesome';

const props = defineProps({
    current: {
        type: Number,
        default: 1,
    },
});

const steps = [
    { id: 1, label: 'سبد خرید', icon: uiIcons.cart },
    { id: 2, label: 'اطلاعات ارسال', icon: uiIcons.truck },
    { id: 3, label: 'اطلاعات پرداخت', icon: uiIcons.creditCard },
];

const items = computed(() =>
    steps.map((step) => ({
        ...step,
        active: step.id === props.current,
        done: step.id < props.current,
    })),
);
</script>

<template>
    <div class="flex items-center justify-center gap-2 sm:gap-4">
        <template v-for="(step, index) in items" :key="step.id">
            <div v-if="index > 0" class="h-px w-8 bg-gray-200 sm:w-16" aria-hidden="true" />

            <div class="flex flex-col items-center gap-2">
                <div
                    class="flex h-11 w-11 items-center justify-center rounded-full border-2 transition"
                    :class="
                        step.active || step.done
                            ? 'border-brand bg-brand text-white'
                            : 'border-gray-200 bg-white text-gray-400'
                    "
                >
                    <Icon :icon="step.icon" />
                </div>
                <span
                    class="text-xs font-medium sm:text-sm"
                    :class="step.active || step.done ? 'text-brand' : 'text-gray-400'"
                >
                    {{ step.label }}
                </span>
            </div>
        </template>
    </div>
</template>
