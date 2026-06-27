<script setup>
import { onMounted, ref } from 'vue';

const STORAGE_KEY = 'pwa_ios_prompt_dismissed_at';
const SNOOZE_DAYS = 7;

const show = ref(false);

function isIos() {
    const ua = window.navigator.userAgent;
    const iDevice = /iphone|ipad|ipod/i.test(ua);
    const iPadOS = navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1;
    return iDevice || iPadOS;
}

function isSafari() {
    const ua = window.navigator.userAgent;
    // "Add to Home Screen" only exists in Safari, not Chrome/Firefox/Edge on iOS.
    return /safari/i.test(ua) && !/crios|fxios|edgios|opios/i.test(ua);
}

function isStandalone() {
    return (
        window.navigator.standalone === true ||
        window.matchMedia('(display-mode: standalone)').matches
    );
}

function recentlyDismissed() {
    const at = Number(window.localStorage.getItem(STORAGE_KEY) || 0);
    if (!at) {
        return false;
    }
    return Date.now() - at < SNOOZE_DAYS * 24 * 60 * 60 * 1000;
}

function dismiss() {
    show.value = false;
    try {
        window.localStorage.setItem(STORAGE_KEY, String(Date.now()));
    } catch {
        // ignore storage failures (private mode)
    }
}

onMounted(() => {
    if (typeof window === 'undefined') {
        return;
    }
    if (!isIos() || !isSafari() || isStandalone() || recentlyDismissed()) {
        return;
    }
    window.setTimeout(() => {
        show.value = true;
    }, 1500);
});
</script>

<template>
    <div>
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-[60] flex items-end justify-center bg-black/50 backdrop-blur-sm"
                @click.self="dismiss"
            >
                <Transition
                    appear
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="translate-y-full"
                    enter-to-class="translate-y-0"
                >
                    <div
                        v-if="show"
                        class="relative w-full max-w-md rounded-t-3xl bg-white px-6 pt-6 pb-10 text-center text-gray-900 shadow-2xl"
                        style="padding-bottom: calc(2.5rem + env(safe-area-inset-bottom))"
                    >
                        <button
                            type="button"
                            class="absolute top-4 left-4 flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-400 transition hover:bg-gray-200"
                            aria-label="بستن"
                            @click="dismiss"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18" />
                            </svg>
                        </button>

                        <img
                            src="/icons/icon-180.png"
                            alt="ShopFlow"
                            class="mx-auto h-16 w-16 rounded-2xl shadow-lg"
                        />

                        <h2 class="mt-4 text-lg font-bold">افزودن شاپ‌فلو به صفحه اصلی</h2>
                        <p class="mt-1 text-sm text-gray-500">
                            برای دسترسی سریع‌تر، وب‌اپلیکیشن شاپ‌فلو را به صفحه اصلی آیفون خود اضافه
                            کنید.
                        </p>

                        <div class="mt-6 space-y-3 text-right">
                            <div class="flex items-center gap-3 rounded-2xl bg-gray-50 p-3">
                                <span
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-500"
                                >
                                    <svg
                                        viewBox="0 0 24 24"
                                        class="h-5 w-5"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M12 15V4m0 0L8 8m4-4l4 4"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M5 12v6a2 2 0 002 2h10a2 2 0 002-2v-6"
                                        />
                                    </svg>
                                </span>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold">۱. دکمه اشتراک‌گذاری</p>
                                    <p class="text-xs text-gray-400">
                                        روی آیکن اشتراک‌گذاری در نوار پایین مرورگر بزنید.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 rounded-2xl bg-gray-50 p-3">
                                <span
                                    class="bg-brand/10 text-brand flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
                                >
                                    <svg
                                        viewBox="0 0 24 24"
                                        class="h-5 w-5"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <rect x="4" y="4" width="16" height="16" rx="4" />
                                        <path stroke-linecap="round" d="M12 9v6M9 12h6" />
                                    </svg>
                                </span>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold">۲. Add to Home Screen</p>
                                    <p class="text-xs text-gray-400">
                                        گزینه «Add to Home Screen» را انتخاب کنید.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="bg-brand mt-6 w-full rounded-2xl py-3 text-sm font-bold text-white transition hover:opacity-90"
                            @click="dismiss"
                        >
                            متوجه شدم
                        </button>
                    </div>
                </Transition>
            </div>
        </Transition>
    </div>
</template>
