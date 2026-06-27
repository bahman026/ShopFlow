<script setup>
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppLink from '@/Components/AppLink.vue';
import Icon from '@/Components/Icon.vue';
import { uiIcons } from '@/fontawesome';

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);

function logout() {
    router.post('/logout');
}
</script>

<template>
    <div class="flex items-center gap-1 sm:gap-2">
        <AppLink
            v-if="!user"
            href="/login"
            class="hover:text-brand flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-100"
        >
            <Icon :icon="uiIcons.user" />
            <span class="hidden sm:inline">ورود | ثبت‌نام</span>
        </AppLink>

        <div v-else class="flex items-center gap-1">
            <span class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700">
                <Icon :icon="uiIcons.user" />
                <span class="hidden max-w-[8rem] truncate sm:inline">{{ user.name }}</span>
            </span>
            <button
                type="button"
                class="hover:text-brand rounded-lg px-2 py-2 text-xs text-gray-500 transition hover:bg-gray-100"
                @click="logout"
            >
                خروج
            </button>
        </div>

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
