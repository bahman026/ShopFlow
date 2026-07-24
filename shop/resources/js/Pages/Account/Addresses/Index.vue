<script setup>
import { computed, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppHead from '@/Components/AppHead.vue';
import AccountLayout from '@/Layouts/AccountLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import Icon from '@/Components/Icon.vue';
import { uiIcons } from '@/fontawesome';
import AddressFormModal from '@/Components/Account/AddressFormModal.vue';

defineProps({
    addresses: { type: Array, default: () => [] },
    provinces: { type: Array, default: () => [] },
    neshanMapKey: { type: String, default: '' },
});

const page = usePage();
const status = computed(() => page.props.flash?.status ?? null);

const showModal = ref(false);
const editing = ref(null);

function openCreate() {
    editing.value = null;
    showModal.value = true;
}

function openEdit(address) {
    editing.value = address;
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    editing.value = null;
}

function setPrimary(address) {
    router.put('/account/addresses/' + address.id + '/primary', {}, { preserveScroll: true });
}

function removeAddress(address) {
    if (!window.confirm('این نشانی حذف شود؟')) {
        return;
    }
    router.delete('/account/addresses/' + address.id, { preserveScroll: true });
}

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
</script>

<template>
    <AppHead title="نشانی‌ها" :noindex="true" />

    <AccountLayout>
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-lg font-bold text-gray-900">نشانی‌ها</h1>
                <button
                    type="button"
                    class="border-brand text-brand hover:bg-brand flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-medium transition hover:text-white"
                    @click="openCreate"
                >
                    <Icon :icon="uiIcons.plus" class="w-3" />
                    افزودن نشانی جدید
                </button>
            </div>

            <p v-if="status" class="mb-4 rounded-lg bg-green-50 p-3 text-sm text-green-700">
                {{ status }}
            </p>

            <EmptyState
                v-if="addresses.length === 0"
                :icon="uiIcons.location"
                title="هنوز نشانی‌ای ثبت نکرده‌اید"
                description="برای ثبت سفارش، یک نشانی اضافه کنید."
            />

            <div v-else class="flex flex-col gap-3">
                <div
                    v-for="address in addresses"
                    :key="address.id"
                    class="flex items-start justify-between gap-4 rounded-xl border border-gray-100 p-4"
                >
                    <div class="min-w-0">
                        <div class="mb-1 flex items-center gap-2">
                            <span class="text-sm font-bold text-gray-900">{{ address.name }}</span>
                            <span
                                v-if="address.prime"
                                class="bg-brand/10 text-brand rounded-full px-2 py-0.5 text-xs"
                            >
                                پیش‌فرض
                            </span>
                        </div>
                        <p class="text-sm leading-6 text-gray-600">{{ fullAddress(address) }}</p>
                        <div class="mt-1 flex flex-wrap gap-x-4 text-xs text-gray-400">
                            <span v-if="address.postalCode">کد پستی: {{ address.postalCode }}</span>
                            <span dir="ltr">{{ address.phone }}</span>
                        </div>
                    </div>

                    <div class="flex shrink-0 flex-col items-end gap-1">
                        <button
                            type="button"
                            class="hover:text-brand flex items-center gap-1 rounded-lg px-2 py-1 text-xs text-gray-500 transition hover:bg-gray-50"
                            @click="openEdit(address)"
                        >
                            <Icon :icon="uiIcons.edit" />
                            ویرایش
                        </button>
                        <button
                            v-if="!address.prime"
                            type="button"
                            class="hover:text-brand flex items-center gap-1 rounded-lg px-2 py-1 text-xs text-gray-500 transition hover:bg-gray-50"
                            @click="setPrimary(address)"
                        >
                            <Icon :icon="uiIcons.check" />
                            انتخاب به‌عنوان پیش‌فرض
                        </button>
                        <button
                            type="button"
                            class="flex items-center gap-1 rounded-lg px-2 py-1 text-xs text-gray-500 transition hover:bg-red-50 hover:text-red-600"
                            @click="removeAddress(address)"
                        >
                            <Icon :icon="uiIcons.trash" />
                            حذف
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <AddressFormModal
            :show="showModal"
            :address="editing"
            :provinces="provinces"
            :neshan-map-key="neshanMapKey"
            @close="closeModal"
        />
    </AccountLayout>
</template>
