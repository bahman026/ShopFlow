<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import AppHead from '@/Components/AppHead.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import { uiIcons } from '@/fontawesome';

const DEFAULT_RESEND_SECONDS = 120;

const page = usePage();
const flash = computed(() => page.props.flash ?? {});

const step = ref(flash.value.authStep || 'mobile');
const resendIn = ref(0);

let timer = null;

const identifyForm = useForm({ mobile: flash.value.authMobile || '' });
const otpForm = useForm({ mobile: flash.value.authMobile || '', code: '' });
const passwordForm = useForm({ mobile: flash.value.authMobile || '', password: '' });

const mobile = computed(() => identifyForm.mobile || flash.value.authMobile || '');

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
    if (value.authMobile) {
        identifyForm.mobile = value.authMobile;
        otpForm.mobile = value.authMobile;
        passwordForm.mobile = value.authMobile;
    }

    if (value.authStep) {
        step.value = value.authStep;

        if (value.authStep === 'otp') {
            startTimer(value.authResendIn);
        }
    }
});

onMounted(() => {
    if (step.value === 'otp') {
        startTimer(flash.value.authResendIn);
    }
});

function identify() {
    identifyForm.post('/login/identify', { preserveScroll: true });
}

function sendOtp() {
    otpForm.reset('code');
    router.post('/login/otp', { mobile: mobile.value }, { preserveScroll: true });
}

function verifyOtp() {
    otpForm.mobile = mobile.value;
    otpForm.post('/login/otp/verify', { preserveScroll: true });
}

function loginWithPassword() {
    passwordForm.mobile = mobile.value;
    passwordForm.post('/login/password', { preserveScroll: true });
}

function editMobile() {
    step.value = 'mobile';
}

onBeforeUnmount(() => {
    if (timer) {
        clearInterval(timer);
    }
});
</script>

<template>
    <AppHead title="ورود یا ثبت‌نام" :noindex="true" />

    <AppLayout>
        <div class="flex justify-center py-8">
            <div
                class="w-full max-w-md rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8"
            >
                <h1 class="mb-6 text-center text-lg font-bold text-gray-900">ورود یا ثبت‌نام</h1>

                <!-- Step 1: mobile -->
                <form
                    v-if="step === 'mobile'"
                    class="flex flex-col gap-4"
                    @submit.prevent="identify"
                >
                    <p class="text-sm text-gray-500">لطفا شماره موبایل خود را وارد کنید</p>

                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-gray-500" for="mobile">شماره موبایل</label>
                        <input
                            id="mobile"
                            v-model="identifyForm.mobile"
                            type="tel"
                            inputmode="tel"
                            autocomplete="tel"
                            placeholder="۰۹۱۲۳۴۵۶۷۸۹"
                            class="focus:border-brand rounded-xl border border-gray-200 px-4 py-3 text-sm focus:outline-none"
                        />
                        <span v-if="identifyForm.errors.mobile" class="text-xs text-red-600">
                            {{ identifyForm.errors.mobile }}
                        </span>
                    </div>

                    <button
                        type="submit"
                        class="bg-brand rounded-xl py-3 text-sm font-bold text-white transition hover:opacity-90 disabled:opacity-60"
                        :disabled="identifyForm.processing"
                    >
                        ادامه
                    </button>
                </form>

                <!-- Step 2a: OTP -->
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
                        تایید و ورود
                    </button>

                    <div class="flex items-center justify-between text-xs">
                        <span v-if="resendIn > 0" class="text-gray-400">
                            ارسال مجدد کد بعد از {{ resendIn }} ثانیه
                        </span>
                        <button v-else type="button" class="text-brand" @click="sendOtp">
                            ارسال مجدد کد
                        </button>

                        <button type="button" class="text-brand" @click="step = 'password'">
                            ورود با رمز عبور
                        </button>
                    </div>
                </form>

                <!-- Step 2b: password -->
                <form v-else class="flex flex-col gap-4" @submit.prevent="loginWithPassword">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-500">رمز عبور خود را وارد کنید</p>
                        <button type="button" class="text-brand text-xs" @click="editMobile">
                            اصلاح شماره
                        </button>
                    </div>

                    <p class="text-xs text-gray-400">شماره: {{ mobile }}</p>

                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-gray-500" for="password">رمز عبور</label>
                        <input
                            id="password"
                            v-model="passwordForm.password"
                            type="password"
                            autocomplete="current-password"
                            class="focus:border-brand rounded-xl border border-gray-200 px-4 py-3 text-sm focus:outline-none"
                        />
                        <span v-if="passwordForm.errors.password" class="text-xs text-red-600">
                            {{ passwordForm.errors.password }}
                        </span>
                    </div>

                    <button
                        type="submit"
                        class="bg-brand rounded-xl py-3 text-sm font-bold text-white transition hover:opacity-90 disabled:opacity-60"
                        :disabled="passwordForm.processing"
                    >
                        ورود
                    </button>

                    <button type="button" class="text-brand text-xs" @click="sendOtp">
                        ورود با رمز یکبار مصرف
                    </button>
                </form>

                <p
                    class="mt-6 flex items-center justify-center gap-2 text-center text-xs text-gray-400"
                >
                    <Icon :icon="uiIcons.shield" />
                    ورود شما به معنای پذیرش قوانین فروشگاه است
                </p>
            </div>
        </div>
    </AppLayout>
</template>
