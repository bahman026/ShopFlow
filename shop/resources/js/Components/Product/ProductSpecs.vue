<script setup>
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

        <dl class="grid grid-cols-1 gap-2 sm:grid-cols-2">
            <div
                v-for="(spec, index) in specs"
                :key="index"
                class="flex items-center justify-between gap-2 rounded-lg bg-gray-50 px-3 py-2 text-sm"
            >
                <dt class="text-gray-500">{{ spec.group }}</dt>
                <dd class="font-medium text-gray-800">{{ spec.value }}</dd>
            </div>
        </dl>
    </section>
</template>
