<script setup>
import { computed } from 'vue';
import AppLink from '@/Components/AppLink.vue';
import Icon from '@/Components/Icon.vue';
import { uiIcons } from '@/fontawesome';
import { useFormat } from '@/composables/useFormat';

const props = defineProps({
    line: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['update', 'remove']);

const { formatPrice, toPersianDigits } = useFormat();

const hasDiscount = computed(
    () => props.line.discountPercent && props.line.unitPrice < props.line.originalPrice,
);
const atMax = computed(() => props.line.count >= props.line.inventory);

function increase() {
    if (!atMax.value) {
        emit('update', props.line.count + 1);
    }
}

function decrease() {
    if (props.line.count > 1) {
        emit('update', props.line.count - 1);
    }
}
</script>

<template>
    <div class="flex gap-4 border-b border-gray-100 py-5 last:border-b-0">
        <AppLink :href="line.url" class="shrink-0">
            <img
                v-if="line.image"
                :src="line.image.url"
                :alt="line.image.alt || line.heading"
                class="h-24 w-24 rounded-xl border border-gray-100 object-cover"
            />
            <div
                v-else
                class="flex h-24 w-24 items-center justify-center rounded-xl border border-gray-100 bg-gray-50 text-gray-300"
            >
                <Icon :icon="uiIcons.image" />
            </div>
        </AppLink>

        <div class="flex min-w-0 flex-1 flex-col gap-2">
            <AppLink
                :href="line.url"
                class="hover:text-brand line-clamp-2 text-sm font-medium text-gray-800 transition"
            >
                {{ line.heading }}
            </AppLink>

            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-500">
                <span v-if="line.color">رنگ: {{ line.color }}</span>
                <span v-for="attribute in line.attributes" :key="attribute.group + attribute.value">
                    {{ attribute.group ? attribute.group + ': ' : '' }}{{ attribute.value }}
                </span>
            </div>

            <p v-if="!line.inStock" class="text-xs font-medium text-red-500">ناموجود</p>

            <div class="mt-auto flex items-end justify-between gap-3 pt-2">
                <div class="flex h-9 items-center rounded-lg border border-gray-200">
                    <button
                        type="button"
                        class="hover:text-brand px-2 text-gray-600 transition disabled:text-gray-300"
                        aria-label="افزایش"
                        :disabled="atMax"
                        @click="increase"
                    >
                        <Icon :icon="uiIcons.plus" class="text-xs" />
                    </button>
                    <span class="min-w-[2rem] text-center text-sm font-medium">
                        {{ toPersianDigits(line.count) }}
                    </span>
                    <button
                        v-if="line.count > 1"
                        type="button"
                        class="hover:text-brand px-2 text-gray-600 transition"
                        aria-label="کاهش"
                        @click="decrease"
                    >
                        <Icon :icon="uiIcons.minus" class="text-xs" />
                    </button>
                    <button
                        v-else
                        type="button"
                        class="px-2 text-gray-500 transition hover:text-red-600"
                        aria-label="حذف"
                        @click="emit('remove')"
                    >
                        <Icon :icon="uiIcons.trash" class="text-xs" />
                    </button>
                </div>

                <div class="text-left">
                    <p v-if="hasDiscount" class="text-xs text-gray-400 line-through">
                        {{ formatPrice(line.lineOriginalTotal) }}
                    </p>
                    <p class="text-sm font-bold text-gray-900">
                        {{ formatPrice(line.lineTotal) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
