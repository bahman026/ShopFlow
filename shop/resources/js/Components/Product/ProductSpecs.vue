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

        <ul class="grid grid-cols-1 gap-2 sm:grid-cols-2">
            <li
                v-for="(spec, index) in specs"
                :key="index"
                class="flex items-center gap-2 rounded-lg bg-gray-50 px-3 py-2 text-sm text-gray-700"
            >
                <Icon :icon="uiIcons.check" class="text-brand" />
                <span>{{ spec.value }}</span>
            </li>
        </ul>
    </section>
</template>
