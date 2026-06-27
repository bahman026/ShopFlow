<script setup>
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import AppLink from '@/Components/AppLink.vue';
import Icon from '@/Components/Icon.vue';
import { uiIcons } from '@/fontawesome';

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);

const items = [
    { label: 'سفارش‌های من', href: '/account/orders', icon: uiIcons.orders },
    { label: 'مرجوعی‌های من', href: '/account/returns', icon: uiIcons.returns },
    { label: 'علاقه‌مندی‌ها', href: '/account/wishlist', icon: uiIcons.heart },
    { label: 'حساب کاربری', href: '/account/profile', icon: uiIcons.user },
    { label: 'نشانی‌ها', href: '/account/addresses', icon: uiIcons.location },
    { label: 'نظرات ثبت‌شده', href: '/account/reviews', icon: uiIcons.star },
];

function isActive(href) {
    const url = page.url || '';
    return url === href || url.startsWith(href + '/');
}

function logout() {
    router.post('/logout');
}
</script>

<template>
    <AppLayout>
        <div class="flex flex-col gap-6 lg:flex-row">
            <aside class="lg:w-72 lg:shrink-0">
                <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                    <AppLink
                        href="/account"
                        class="flex items-center gap-3 rounded-xl p-3 transition hover:bg-gray-50"
                    >
                        <span
                            class="bg-brand/10 text-brand flex h-11 w-11 items-center justify-center rounded-full"
                        >
                            <Icon :icon="uiIcons.user" />
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-bold text-gray-900">
                                {{ user?.name || 'کاربر گرامی' }}
                            </span>
                            <span class="block text-xs text-gray-400">{{ user?.mobile }}</span>
                        </span>
                    </AppLink>

                    <nav class="mt-3 flex flex-col">
                        <AppLink
                            v-for="item in items"
                            :key="item.href"
                            :href="item.href"
                            class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition"
                            :class="
                                isActive(item.href)
                                    ? 'bg-brand/10 text-brand font-bold'
                                    : 'text-gray-600 hover:bg-gray-50'
                            "
                        >
                            <Icon :icon="item.icon" class="w-4" />
                            {{ item.label }}
                        </AppLink>

                        <button
                            type="button"
                            class="mt-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-gray-500 transition hover:bg-gray-50"
                            @click="logout"
                        >
                            <Icon :icon="uiIcons.logout" class="w-4" />
                            خروج از حساب
                        </button>
                    </nav>
                </div>
            </aside>

            <section class="min-w-0 flex-1">
                <slot />
            </section>
        </div>
    </AppLayout>
</template>
