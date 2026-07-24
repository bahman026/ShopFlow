<script setup>
import { computed, ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppHead from '@/Components/AppHead.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import AppLink from '@/Components/AppLink.vue';
import EmptyState from '@/Components/EmptyState.vue';
import Icon from '@/Components/Icon.vue';
import CheckoutSteps from '@/Components/Checkout/CheckoutSteps.vue';
import CartSummary from '@/Components/Cart/CartSummary.vue';
import AddressFormModal from '@/Components/Account/AddressFormModal.vue';
import { uiIcons } from '@/fontawesome';
import { formatPrice } from '@/composables/useFormat';

const props = defineProps({
    summary: {
        type: Object,
        default: () => ({ count: 0, itemsTotal: 0, discount: 0, payable: 0 }),
    },
    addresses: { type: Array, default: () => [] },
    selectedAddressId: { type: Number, default: null },
    shippingMethods: { type: Array, default: () => [] },
    selectedMethodId: { type: Number, default: null },
    provinces: { type: Array, default: () => [] },
    neshanMapKey: { type: String, default: '' },
});

const page = usePage();
const status = computed(() => page.props.flash?.status ?? null);

const selected = ref(props.selectedAddressId);
const methods = ref(props.shippingMethods);
const selectedMethod = ref(props.selectedMethodId);
const showModal = ref(false);
const processing = ref(false);
const loadingMethods = ref(false);

const selectedMethodObj = computed(
    () => methods.value.find((method) => method.id === selectedMethod.value) ?? null,
);

function fullAddress(address) {
    const parts = [address.provinceName, address.cityName, address.address].filter(Boolean);
    let text = parts.join('، ');
    if (address.plate) {
        text += '، پلاک ' + address.plate;
    }
    if (address.unit) {
        text += '، واحد ' + address.unit;
    }
    return text;
}

function methodCostLabel(method) {
    if (method.payOnDelivery) {
        return 'پس‌کرایه';
    }
    if (!method.cost) {
        return 'رایگان';
    }
    return formatPrice(method.cost);
}

// Available methods depend on the destination, so refresh when the address
// changes (skipping the initial server-provided list).
watch(selected, async (addressId, previous) => {
    if (!addressId || addressId === previous) {
        return;
    }
    loadingMethods.value = true;
    try {
        const response = await fetch('/checkout/methods?address_id=' + addressId, {
            headers: { Accept: 'application/json' },
        });
        methods.value = response.ok ? ((await response.json()).methods ?? []) : [];
    } catch {
        methods.value = [];
    } finally {
        loadingMethods.value = false;
    }
    if (!methods.value.some((method) => method.id === selectedMethod.value)) {
        selectedMethod.value = null;
    }
});

function proceed() {
    if (!selected.value || !selectedMethod.value) {
        return;
    }
    router.post(
        '/checkout/shipping',
        { address_id: selected.value, shipping_method_id: selectedMethod.value },
        { onStart: () => (processing.value = true), onFinish: () => (processing.value = false) },
    );
}
</script>

<template>
    <AppHead title="اطلاعات ارسال" :noindex="true" />

    <AppLayout>
        <div class="flex flex-col gap-8">
            <CheckoutSteps :current="2" />

            <p v-if="status" class="rounded-lg bg-green-50 p-3 text-center text-sm text-green-700">
                {{ status }}
            </p>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <div class="flex flex-col gap-6 lg:col-span-8">
                    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                        <div class="mb-4 flex items-center justify-between">
                            <h1 class="text-base font-bold text-gray-900">انتخاب نشانی ارسال</h1>
                            <button
                                v-if="addresses.length > 0"
                                type="button"
                                class="border-brand text-brand hover:bg-brand flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-medium transition hover:text-white"
                                @click="showModal = true"
                            >
                                <Icon :icon="uiIcons.plus" class="w-3" />
                                نشانی جدید
                            </button>
                        </div>

                        <EmptyState
                            v-if="addresses.length === 0"
                            :icon="uiIcons.location"
                            title="هنوز نشانی‌ای ثبت نکرده‌اید"
                            description="برای ادامه‌ی خرید، یک نشانی برای ارسال سفارش اضافه کنید."
                        >
                            <button
                                type="button"
                                class="bg-brand hover:bg-brand/90 mt-4 inline-flex items-center gap-2 rounded-xl px-6 py-2.5 text-sm font-bold text-white transition"
                                @click="showModal = true"
                            >
                                <Icon :icon="uiIcons.plus" class="w-3" />
                                افزودن نشانی
                            </button>
                        </EmptyState>

                        <div v-else class="flex flex-col gap-3">
                            <label
                                v-for="address in addresses"
                                :key="address.id"
                                class="flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition"
                                :class="
                                    selected === address.id
                                        ? 'border-brand bg-brand/5'
                                        : 'border-gray-100 hover:border-gray-200'
                                "
                            >
                                <input
                                    v-model="selected"
                                    type="radio"
                                    :value="address.id"
                                    name="address"
                                    class="text-brand mt-1"
                                />
                                <div class="min-w-0">
                                    <div class="mb-1 flex items-center gap-2">
                                        <span class="text-sm font-bold text-gray-900">{{
                                            address.name
                                        }}</span>
                                        <span
                                            v-if="address.prime"
                                            class="bg-brand/10 text-brand rounded-full px-2 py-0.5 text-xs"
                                        >
                                            پیش‌فرض
                                        </span>
                                    </div>
                                    <p class="text-sm leading-6 text-gray-600">
                                        {{ fullAddress(address) }}
                                    </p>
                                    <div class="mt-1 flex flex-wrap gap-x-4 text-xs text-gray-400">
                                        <span v-if="address.postalCode"
                                            >کد پستی: {{ address.postalCode }}</span
                                        >
                                        <span dir="ltr">{{ address.phone }}</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div
                        v-if="addresses.length > 0"
                        class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm"
                    >
                        <div class="mb-4 flex items-center gap-2">
                            <Icon :icon="uiIcons.truck" class="text-brand" />
                            <h2 class="text-base font-bold text-gray-900">نحوه ارسال مرسوله</h2>
                        </div>

                        <p v-if="loadingMethods" class="py-6 text-center text-sm text-gray-400">
                            در حال بارگذاری روش‌های ارسال…
                        </p>

                        <EmptyState
                            v-else-if="methods.length === 0"
                            :icon="uiIcons.truck"
                            title="روش ارسالی برای این نشانی یافت نشد"
                            description="لطفاً نشانی دیگری انتخاب کنید."
                        />

                        <div v-else class="flex flex-col gap-3">
                            <label
                                v-for="method in methods"
                                :key="method.id"
                                class="flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition"
                                :class="
                                    selectedMethod === method.id
                                        ? 'border-brand bg-brand/5'
                                        : 'border-gray-100 hover:border-gray-200'
                                "
                            >
                                <input
                                    v-model="selectedMethod"
                                    type="radio"
                                    :value="method.id"
                                    name="shipping-method"
                                    class="text-brand mt-1"
                                />
                                <div class="min-w-0 flex-1">
                                    <div class="mb-1 flex items-center justify-between gap-2">
                                        <span class="text-sm font-bold text-gray-900">{{
                                            method.name
                                        }}</span>
                                        <span class="text-brand text-sm font-bold">{{
                                            methodCostLabel(method)
                                        }}</span>
                                    </div>
                                    <p
                                        v-if="method.description"
                                        class="text-xs leading-6 text-gray-500"
                                    >
                                        {{ method.description }}
                                    </p>
                                    <p v-if="method.sendingDays" class="mt-1 text-xs text-gray-400">
                                        زمان ارسال: {{ method.sendingDays }}
                                    </p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-3 lg:col-span-4">
                    <CartSummary
                        :summary="summary"
                        cta-label="ادامه و پرداخت"
                        :processing="processing || !selected || !selectedMethod"
                        :show-shipping="true"
                        :shipping="selectedMethodObj"
                        @submit="proceed"
                    />
                    <AppLink
                        href="/cart"
                        class="flex h-11 w-full items-center justify-center rounded-xl border border-gray-200 text-sm font-medium text-gray-600 transition hover:bg-gray-50"
                    >
                        بازگشت به سبد خرید
                    </AppLink>
                </div>
            </div>
        </div>

        <AddressFormModal
            :show="showModal"
            :provinces="provinces"
            :neshan-map-key="neshanMapKey"
            @close="showModal = false"
        />
    </AppLayout>
</template>
