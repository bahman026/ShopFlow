<script setup>
import { useForm } from '@inertiajs/vue3';
import AppHead from '@/Components/AppHead.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import AppLink from '@/Components/AppLink.vue';

const props = defineProps({
    token: { type: String, required: true },
    email: { type: String, default: '' },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post('/reset-password', { preserveScroll: true });
}
</script>

<template>
    <AppHead title="تعیین رمز عبور جدید" :noindex="true" />

    <AppLayout>
        <div class="flex justify-center py-8">
            <div
                class="w-full max-w-md rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8"
            >
                <h1 class="mb-6 text-center text-lg font-bold text-gray-900">
                    تعیین رمز عبور جدید
                </h1>

                <form class="flex flex-col gap-4" @submit.prevent="submit">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-gray-500" for="email">ایمیل</label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            autocomplete="email"
                            dir="ltr"
                            class="focus:border-brand rounded-xl border border-gray-200 px-4 py-3 text-sm focus:outline-none"
                        />
                        <span v-if="form.errors.email" class="text-xs text-red-600">
                            {{ form.errors.email }}
                        </span>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-gray-500" for="password">رمز عبور جدید</label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            autocomplete="new-password"
                            class="focus:border-brand rounded-xl border border-gray-200 px-4 py-3 text-sm focus:outline-none"
                        />
                        <span v-if="form.errors.password" class="text-xs text-red-600">
                            {{ form.errors.password }}
                        </span>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-gray-500" for="password_confirmation">
                            تکرار رمز عبور
                        </label>
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            class="focus:border-brand rounded-xl border border-gray-200 px-4 py-3 text-sm focus:outline-none"
                        />
                    </div>

                    <button
                        type="submit"
                        class="bg-brand rounded-xl py-3 text-sm font-bold text-white transition hover:opacity-90 disabled:opacity-60"
                        :disabled="form.processing"
                    >
                        ثبت رمز عبور
                    </button>
                </form>

                <p class="mt-6 text-center text-xs text-gray-400">
                    <AppLink href="/login" class="text-brand">بازگشت به صفحه ورود</AppLink>
                </p>
            </div>
        </div>
    </AppLayout>
</template>
