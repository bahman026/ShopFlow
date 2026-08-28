<script setup>
import Icon from '@/Components/Icon.vue';
import { uiIcons } from '@/fontawesome';

defineProps({
    specs: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <section v-if="specs.length" id="specs">
        <h2 class="mb-4 text-lg font-bold text-gray-900">مشخصات کالا</h2>

        <dl class="divide-y divide-gray-100 overflow-hidden rounded-xl border border-gray-100">
            <div
                v-for="spec in specs"
                :key="spec.group"
                class="flex flex-col gap-2 px-4 py-3 sm:flex-row sm:items-start sm:gap-6"
                :class="spec.highlight ? 'bg-brand/5' : 'bg-white'"
            >
                <dt
                    class="flex shrink-0 items-center gap-1.5 pt-0.5 text-sm font-medium sm:w-36"
                    :class="spec.highlight ? 'text-brand' : 'text-gray-500'"
                >
                    <Icon v-if="spec.highlight" :icon="uiIcons.star" class="text-xs" />
                    {{ spec.group }}
                </dt>
                <dd class="flex flex-1 flex-wrap gap-1.5">
                    <span
                        v-for="(item, index) in spec.values"
                        :key="index"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1 text-sm text-gray-700"
                    >
                        <span
                            v-if="item.color"
                            class="h-3.5 w-3.5 rounded-full border border-gray-300"
                            :style="{ backgroundColor: item.color }"
                        />
                        {{ item.value }}
                    </span>
                </dd>
            </div>
        </dl>
    </section>
</template>
