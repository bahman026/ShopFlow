<script setup>
import AppHead from '@/Components/AppHead.vue';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    message: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <AppHead
        title="خانه"
        description="فروشگاه آنلاین ShopFlow"
    />

    <AppLayout>
        <section class="rounded-xl bg-white p-10 text-center shadow-sm">
            <h1 class="text-2xl font-bold">به ShopFlow خوش آمدید</h1>
            <p class="mt-4 text-gray-600">{{ message }}</p>
        </section>
    </AppLayout>
</template>
