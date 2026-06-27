<script setup>
import AppLink from '@/Components/AppLink.vue';
import Icon from '@/Components/Icon.vue';
import { uiIcons } from '@/fontawesome';
</script>

<template>
    <div class="flex items-center gap-1 sm:gap-2">
        <AppLink
            href="/login"
            class="hover:text-brand flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-100"
        >
            <Icon :icon="uiIcons.user" />
            <span class="hidden sm:inline">حساب کاربری</span>
        </AppLink>

        <AppLink
            href="/cart"
            class="hover:text-brand flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-100"
            aria-label="سبد خرید"
        >
            <Icon :icon="uiIcons.cart" />
            <span class="hidden sm:inline">سبد خرید</span>
        </AppLink>
    </div>
</template>
