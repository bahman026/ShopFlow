<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    message: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <Head title="خانه">
        <meta name="description" content="فروشگاه آنلاین ShopFlow">
    </Head>

    <AppLayout>
        <section class="rounded-xl bg-white p-10 text-center shadow-sm">
            <h1 class="text-2xl font-bold">به ShopFlow خوش آمدید</h1>
            <p class="mt-4 text-gray-600">{{ message }}</p>
        </section>
    </AppLayout>
</template>
