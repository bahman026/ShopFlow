<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        required: true,
    },
});

// Mirrors OrderStatusEnum's sequential lifecycle (PHP is the source of
// truth; shop enums never define label()/color(), so this stays client-side
// like useOrderStatus.js does for badge colors).
const STAGES = ['PENDING', 'PAID', 'PROCESSING', 'SHIPPED', 'DELIVERED'];

const STAGE_LABELS = {
    PENDING: 'در انتظار پرداخت',
    PAID: 'پرداخت‌شده',
    PROCESSING: 'در حال آماده‌سازی',
    SHIPPED: 'ارسال‌شده',
    DELIVERED: 'تحویل داده‌شده',
};

const isStopped = computed(() => props.status === 'CANCELED' || props.status === 'RETURNED');
const currentIndex = computed(() => STAGES.indexOf(props.status));

const progressPercent = computed(() => {
    if (currentIndex.value < 0) {
        return 0;
    }
    return (currentIndex.value / (STAGES.length - 1)) * 100;
});

const nextStageLabel = computed(() => {
    if (currentIndex.value < 0 || currentIndex.value >= STAGES.length - 1) {
        return null;
    }
    return STAGE_LABELS[STAGES[currentIndex.value + 1]];
});
</script>

<template>
    <div
        v-if="isStopped"
        class="rounded-xl px-4 py-3 text-center text-sm font-medium"
        :class="status === 'CANCELED' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700'"
    >
        {{ status === 'CANCELED' ? 'این سفارش لغو شده است.' : 'این سفارش مرجوع شده است.' }}
    </div>

    <div v-else class="flex flex-col gap-2">
        <div class="flex items-center justify-between text-xs text-gray-500">
            <span class="font-bold text-gray-900">{{ STAGE_LABELS[status] ?? status }}</span>
            <span v-if="nextStageLabel">مرحله بعد: {{ nextStageLabel }}</span>
        </div>
        <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200">
            <div
                class="h-full rounded-full bg-green-500 transition-all"
                :style="{ width: progressPercent + '%' }"
            />
        </div>
    </div>
</template>
