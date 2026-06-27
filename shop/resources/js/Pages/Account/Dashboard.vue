<script setup>
import AppHead from '@/Components/AppHead.vue';
import AccountLayout from '@/Layouts/AccountLayout.vue';
import AppLink from '@/Components/AppLink.vue';
import Icon from '@/Components/Icon.vue';
import { uiIcons } from '@/fontawesome';

defineProps({
    user: { type: Object, required: true },
});

const shortcuts = [
    { label: 'سفارش‌های من', href: '/account/orders', icon: uiIcons.orders },
    { label: 'علاقه‌مندی‌ها', href: '/account/wishlist', icon: uiIcons.heart },
    { label: 'نشانی‌ها', href: '/account/addresses', icon: uiIcons.location },
    { label: 'حساب کاربری', href: '/account/profile', icon: uiIcons.user },
];
</script>

<template>
    <AppHead title="حساب کاربری" :noindex="true" />

    <AccountLayout>
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h1 class="text-lg font-bold text-gray-900">سلام {{ user.displayName }} 👋</h1>
            <p class="mt-1 text-sm text-gray-500">به حساب کاربری خود خوش آمدید.</p>
        </div>

        <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
            <AppLink
                v-for="item in shortcuts"
                :key="item.href"
                :href="item.href"
                class="hover:border-brand/40 flex flex-col items-center gap-3 rounded-2xl border border-gray-100 bg-white p-5 text-center shadow-sm transition"
            >
                <span
                    class="bg-brand/10 text-brand flex h-12 w-12 items-center justify-center rounded-full"
                >
                    <Icon :icon="item.icon" />
                </span>
                <span class="text-sm text-gray-700">{{ item.label }}</span>
            </AppLink>
        </div>
    </AccountLayout>
</template>
