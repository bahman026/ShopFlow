<script setup>
import { computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AppHead from '@/Components/AppHead.vue';
import AccountLayout from '@/Layouts/AccountLayout.vue';

const props = defineProps({
    user: { type: Object, required: true },
});

const page = usePage();
const status = computed(() => page.props.flash?.status ?? null);

const form = useForm({
    first_name: props.user.firstName ?? '',
    last_name: props.user.lastName ?? '',
    email: props.user.email ?? '',
});

function submit() {
    form.put('/account/profile', { preserveScroll: true });
}
</script>

<template>
    <AppHead title="حساب کاربری" :noindex="true" />

    <AccountLayout>
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h1 class="mb-6 text-lg font-bold text-gray-900">اطلاعات حساب</h1>

            <p v-if="status" class="mb-4 rounded-lg bg-green-50 p-3 text-sm text-green-700">
                {{ status }}
            </p>

            <form class="flex max-w-lg flex-col gap-4" @submit.prevent="submit">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-gray-500" for="first_name">نام</label>
                        <input
                            id="first_name"
                            v-model="form.first_name"
                            type="text"
                            class="focus:border-brand rounded-xl border border-gray-200 px-4 py-3 text-sm focus:outline-none"
                        />
                        <span v-if="form.errors.first_name" class="text-xs text-red-600">
                            {{ form.errors.first_name }}
                        </span>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-gray-500" for="last_name">نام خانوادگی</label>
                        <input
                            id="last_name"
                            v-model="form.last_name"
                            type="text"
                            class="focus:border-brand rounded-xl border border-gray-200 px-4 py-3 text-sm focus:outline-none"
                        />
                        <span v-if="form.errors.last_name" class="text-xs text-red-600">
                            {{ form.errors.last_name }}
                        </span>
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-xs text-gray-500" for="email">ایمیل</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        dir="ltr"
                        placeholder="example@email.com"
                        class="focus:border-brand rounded-xl border border-gray-200 px-4 py-3 text-left text-sm focus:outline-none"
                    />
                    <span v-if="form.errors.email" class="text-xs text-red-600">
                        {{ form.errors.email }}
                    </span>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-xs text-gray-500" for="mobile">شماره موبایل</label>
                    <input
                        id="mobile"
                        :value="user.mobile"
                        type="text"
                        dir="ltr"
                        readonly
                        class="cursor-not-allowed rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-left text-sm text-gray-500"
                    />
                    <span class="text-xs text-gray-400"
                        >برای تغییر شماره با پشتیبانی تماس بگیرید.</span
                    >
                </div>

                <div>
                    <button
                        type="submit"
                        class="bg-brand rounded-xl px-6 py-3 text-sm font-bold text-white transition hover:opacity-90 disabled:opacity-60"
                        :disabled="form.processing"
                    >
                        ذخیره تغییرات
                    </button>
                </div>
            </form>
        </div>
    </AccountLayout>
</template>
