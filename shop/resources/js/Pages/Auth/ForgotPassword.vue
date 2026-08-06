<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import AppHead from '@/Components/AppHead.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import AppLink from '@/Components/AppLink.vue';

const DEFAULT_RESEND_SECONDS = 120;

const page = usePage();
const flash = computed(() => page.props.flash ?? {});

// 'sms' walks mobile -> otp -> password; 'email' sends a reset link.
const channel = ref(flash.value.resetChannel || 'sms');
const step = ref(flash.value.resetStep || 'mobile');
const resendIn = ref(0);
const emailSent = ref(Boolean(flash.value.resetEmailSent));

let timer = null;

const mobileForm = useForm({ mobile: flash.value.resetMobile || '' });
const otpForm = useForm({ mobile: flash.value.resetMobile || '', code: '' });
const passwordForm = useForm({ password: '', password_confirmation: '' });
const emailForm = useForm({ email: '' });

const mobile = computed(() => mobileForm.mobile || flash.value.resetMobile || '');

function startTimer(seconds) {
    resendIn.value = Number(seconds) > 0 ? Number(seconds) : DEFAULT_RESEND_SECONDS;

    if (timer) {
        clearInterval(timer);
    }

    timer = setInterval(() => {
        resendIn.value -= 1;

        if (resendIn.value <= 0 && timer) {
            clearInterval(timer);
            timer = null;
        }
    }, 1000);
}

watch(flash, (value) => {
    if (value.resetMobile) {
        mobileForm.mobile = value.resetMobile;
        otpForm.mobile = value.resetMobile;
    }

    if (value.resetChannel) {
        channel.value = value.resetChannel;
    }

    emailSent.value = Boolean(value.resetEmailSent);

    if (value.resetStep) {
        step.value = value.resetStep;

        if (value.resetStep === 'otp') {
            startTimer(value.resetResendIn);
        }
    }
});

onMounted(() => {
    if (step.value === 'otp') {
        startTimer(flash.value.resetResendIn);
    }
});

onBeforeUnmount(() => {
    if (timer) {
        clearInterval(timer);
    }
});

function sendOtp() {
    mobileForm.post('/forgot-password/otp', { preserveScroll: true });
}

function resendOtp() {
    otpForm.reset('code');
    router.post('/forgot-password/otp/resend', { mobile: mobile.value }, { preserveScroll: true });
}

function verifyOtp() {
    otpForm.mobile = mobile.value;
    otpForm.post('/forgot-password/otp/verify', { preserveScroll: true });
}

function savePassword() {
    passwordForm.post('/forgot-password/mobile', { preserveScroll: true });
}

function sendEmailLink() {
    emailForm.post('/forgot-password/email', { preserveScroll: true });
}

function useChannel(next) {
    channel.value = next;
    step.value = 'mobile';
}

function editMobile() {
    step.value = 'mobile';
}
</script>

<template>
    <AppHead title="بازیابی رمز عبور" :noindex="true" />

    <AppLayout>
        <div class="flex justify-center py-8">
            <div
                class="w-full max-w-md rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8"
            >
                <h1 class="mb-6 text-center text-lg font-bold text-gray-900">بازیابی رمز عبور</h1>

                <div class="mb-6 flex rounded-xl bg-gray-50 p-1 text-sm">
                    <button
                        type="button"
                        class="flex-1 rounded-lg py-2 transition"
                        :class="
                            channel === 'sms' ? 'bg-white font-bold shadow-sm' : 'text-gray-500'
                        "
                        @click="useChannel('sms')"
                    >
                        پیامک
                    </button>
                    <button
                        type="button"
                        class="flex-1 rounded-lg py-2 transition"
                        :class="
                            channel === 'email' ? 'bg-white font-bold shadow-sm' : 'text-gray-500'
                        "
                        @click="useChannel('email')"
                    >
                        ایمیل
                    </button>
                </div>

                <template v-if="channel === 'sms'">
                    <!-- Step 1: mobile -->
                    <form
                        v-if="step === 'mobile'"
                        class="flex flex-col gap-4"
                        @submit.prevent="sendOtp"
                    >
                        <p class="text-sm text-gray-500">
                            شماره موبایل حساب خود را وارد کنید تا کد تایید برایتان ارسال شود.
                        </p>

                        <div class="flex flex-col gap-1">
                            <label class="text-xs text-gray-500" for="mobile">شماره موبایل</label>
                            <input
                                id="mobile"
                                v-model="mobileForm.mobile"
                                type="tel"
                                inputmode="tel"
                                autocomplete="tel"
                                placeholder="۰۹۱۲۳۴۵۶۷۸۹"
                                class="focus:border-brand rounded-xl border border-gray-200 px-4 py-3 text-sm focus:outline-none"
                            />
                            <span v-if="mobileForm.errors.mobile" class="text-xs text-red-600">
                                {{ mobileForm.errors.mobile }}
                            </span>
                        </div>

                        <button
                            type="submit"
                            class="bg-brand rounded-xl py-3 text-sm font-bold text-white transition hover:opacity-90 disabled:opacity-60"
                            :disabled="mobileForm.processing"
                        >
                            ارسال کد تایید
                        </button>
                    </form>

                    <!-- Step 2: code -->
                    <form
                        v-else-if="step === 'otp'"
                        class="flex flex-col gap-4"
                        @submit.prevent="verifyOtp"
                    >
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-500">کد تایید را وارد کنید</p>
                            <button type="button" class="text-brand text-xs" @click="editMobile">
                                اصلاح شماره
                            </button>
                        </div>

                        <p class="text-xs text-gray-400">
                            کد ارسال‌شده به شماره {{ mobile }} را وارد کنید.
                        </p>

                        <p
                            v-if="flash.authOtpDev"
                            class="rounded-lg bg-amber-50 p-2 text-center text-xs text-amber-700"
                        >
                            کد تست: {{ flash.authOtpDev }}
                        </p>

                        <input
                            v-model="otpForm.code"
                            type="text"
                            inputmode="numeric"
                            maxlength="6"
                            autocomplete="one-time-code"
                            class="focus:border-brand rounded-xl border border-gray-200 px-4 py-3 text-center text-lg tracking-[0.5em] focus:outline-none"
                        />
                        <span v-if="otpForm.errors.code" class="text-xs text-red-600">
                            {{ otpForm.errors.code }}
                        </span>

                        <button
                            type="submit"
                            class="bg-brand rounded-xl py-3 text-sm font-bold text-white transition hover:opacity-90 disabled:opacity-60"
                            :disabled="otpForm.processing"
                        >
                            تایید کد
                        </button>

                        <div class="text-xs">
                            <span v-if="resendIn > 0" class="text-gray-400">
                                ارسال مجدد کد بعد از {{ resendIn }} ثانیه
                            </span>
                            <button v-else type="button" class="text-brand" @click="resendOtp">
                                ارسال مجدد کد
                            </button>
                        </div>
                    </form>

                    <!-- Step 3: new password -->
                    <form v-else class="flex flex-col gap-4" @submit.prevent="savePassword">
                        <p class="text-sm text-gray-500">رمز عبور جدید خود را انتخاب کنید</p>

                        <div class="flex flex-col gap-1">
                            <label class="text-xs text-gray-500" for="password"
                                >رمز عبور جدید</label
                            >
                            <input
                                id="password"
                                v-model="passwordForm.password"
                                type="password"
                                autocomplete="new-password"
                                class="focus:border-brand rounded-xl border border-gray-200 px-4 py-3 text-sm focus:outline-none"
                            />
                            <span v-if="passwordForm.errors.password" class="text-xs text-red-600">
                                {{ passwordForm.errors.password }}
                            </span>
                        </div>

                        <div class="flex flex-col gap-1">
                            <label class="text-xs text-gray-500" for="password_confirmation">
                                تکرار رمز عبور
                            </label>
                            <input
                                id="password_confirmation"
                                v-model="passwordForm.password_confirmation"
                                type="password"
                                autocomplete="new-password"
                                class="focus:border-brand rounded-xl border border-gray-200 px-4 py-3 text-sm focus:outline-none"
                            />
                        </div>

                        <button
                            type="submit"
                            class="bg-brand rounded-xl py-3 text-sm font-bold text-white transition hover:opacity-90 disabled:opacity-60"
                            :disabled="passwordForm.processing"
                        >
                            ثبت رمز عبور
                        </button>
                    </form>
                </template>

                <!-- Email channel -->
                <template v-else>
                    <p
                        v-if="emailSent"
                        class="rounded-lg bg-green-50 p-3 text-center text-sm text-green-700"
                    >
                        اگر حسابی با این ایمیل وجود داشته باشد، لینک بازیابی رمز عبور برایتان ارسال
                        شد.
                    </p>

                    <form v-else class="flex flex-col gap-4" @submit.prevent="sendEmailLink">
                        <p class="text-sm text-gray-500">
                            ایمیل حساب خود را وارد کنید تا لینک بازیابی برایتان ارسال شود.
                        </p>

                        <div class="flex flex-col gap-1">
                            <label class="text-xs text-gray-500" for="email">ایمیل</label>
                            <input
                                id="email"
                                v-model="emailForm.email"
                                type="email"
                                inputmode="email"
                                autocomplete="email"
                                dir="ltr"
                                class="focus:border-brand rounded-xl border border-gray-200 px-4 py-3 text-sm focus:outline-none"
                            />
                            <span v-if="emailForm.errors.email" class="text-xs text-red-600">
                                {{ emailForm.errors.email }}
                            </span>
                        </div>

                        <button
                            type="submit"
                            class="bg-brand rounded-xl py-3 text-sm font-bold text-white transition hover:opacity-90 disabled:opacity-60"
                            :disabled="emailForm.processing"
                        >
                            ارسال لینک بازیابی
                        </button>
                    </form>
                </template>

                <p class="mt-6 text-center text-xs text-gray-400">
                    <AppLink href="/login" class="text-brand">بازگشت به صفحه ورود</AppLink>
                </p>
            </div>
        </div>
    </AppLayout>
</template>
