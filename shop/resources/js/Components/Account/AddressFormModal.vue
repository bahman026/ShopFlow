<script setup>
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Icon from '@/Components/Icon.vue';
import { uiIcons } from '@/fontawesome';
import NeshanMap from '@/Components/Account/NeshanMap.vue';
import MapPicker from '@/Components/Account/MapPicker.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    provinces: { type: Array, default: () => [] },
    address: { type: Object, default: null },
    neshanMapKey: { type: String, default: '' },
});

const emit = defineEmits(['close']);

const PRESETS = ['منزل', 'محل کار'];

const form = useForm({
    name: 'منزل',
    city_id: null,
    address: '',
    plate: '',
    unit: '',
    postal_code: '',
    phone: '',
    latitude: null,
    longitude: null,
    prime: false,
});

const provinceId = ref(null);
const cities = ref([]);
const loadingCities = ref(false);
const titlePreset = ref('منزل');
const customTitle = ref('');
const filling = ref(false);

const isEdit = computed(() => Boolean(props.address));

const mapValue = computed({
    get: () => ({ lat: form.latitude, lng: form.longitude }),
    set: (value) => {
        form.latitude = value.lat;
        form.longitude = value.lng;
    },
});

async function fetchCities(id, keep = null) {
    cities.value = [];
    if (!id) {
        return;
    }
    loadingCities.value = true;
    try {
        const response = await fetch('/account/addresses-cities?province_id=' + id, {
            headers: { Accept: 'application/json' },
        });
        cities.value = response.ok ? await response.json() : [];
    } catch {
        cities.value = [];
    } finally {
        loadingCities.value = false;
        form.city_id = keep;
    }
}

function onProvinceChange() {
    fetchCities(provinceId.value, null);
}

function pickTitle(value) {
    titlePreset.value = value;
    form.name = value === 'سایر' ? customTitle.value : value;
}

watch(customTitle, (value) => {
    if (titlePreset.value === 'سایر') {
        form.name = value;
    }
});

async function fillFromMap() {
    if (form.latitude == null || form.longitude == null) {
        return;
    }
    filling.value = true;
    try {
        const response = await fetch(
            '/account/addresses-reverse?lat=' + form.latitude + '&lng=' + form.longitude,
            { headers: { Accept: 'application/json' } },
        );
        if (response.ok) {
            const data = await response.json();
            if (data.address) {
                form.address = data.address;
            }
        }
    } catch {
        // keep whatever the user typed
    } finally {
        filling.value = false;
    }
}

function initialize() {
    form.clearErrors();
    const address = props.address;

    if (address) {
        form.name = address.name ?? '';
        form.city_id = address.cityId ?? null;
        form.address = address.address ?? '';
        form.plate = address.plate ?? '';
        form.unit = address.unit ?? '';
        form.postal_code = address.postalCode ?? '';
        form.phone = address.phone ?? '';
        form.latitude = address.latitude != null ? Number(address.latitude) : null;
        form.longitude = address.longitude != null ? Number(address.longitude) : null;
        form.prime = Boolean(address.prime);

        provinceId.value = address.provinceId ?? null;
        fetchCities(provinceId.value, address.cityId ?? null);

        if (PRESETS.includes(address.name)) {
            titlePreset.value = address.name;
            customTitle.value = '';
        } else {
            titlePreset.value = 'سایر';
            customTitle.value = address.name ?? '';
        }
    } else {
        form.reset();
        form.name = 'منزل';
        provinceId.value = null;
        cities.value = [];
        titlePreset.value = 'منزل';
        customTitle.value = '';
    }
}

watch(
    () => props.show,
    (open) => {
        if (open) {
            initialize();
        }
    },
);

function close() {
    emit('close');
}

function submit() {
    const options = {
        preserveScroll: true,
        onSuccess: () => close(),
    };

    if (isEdit.value) {
        form.put('/account/addresses/' + props.address.id, options);
    } else {
        form.post('/account/addresses', options);
    }
}
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/40 p-4"
        @click.self="close"
    >
        <div class="my-6 w-full max-w-2xl rounded-2xl bg-white p-6 shadow-xl">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-base font-bold text-gray-900">
                    {{ isEdit ? 'ویرایش نشانی' : 'افزودن نشانی جدید' }}
                </h2>
                <button
                    type="button"
                    class="flex h-8 w-8 items-center justify-center rounded-full text-gray-400 transition hover:bg-gray-100"
                    aria-label="بستن"
                    @click="close"
                >
                    <Icon :icon="uiIcons.close" />
                </button>
            </div>

            <form class="flex flex-col gap-5" @submit.prevent="submit">
                <section class="flex flex-col gap-2 border-b border-gray-100 pb-5">
                    <h3 class="text-sm font-bold text-gray-800">موقعیت روی نقشه</h3>

                    <NeshanMap
                        v-if="neshanMapKey"
                        v-model="mapValue"
                        :map-key="neshanMapKey"
                        @selected="fillFromMap"
                    />
                    <MapPicker v-else v-model="mapValue" @selected="fillFromMap" />
                </section>

                <h3 class="text-sm font-bold text-gray-800">نشانی پستی</h3>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-gray-500" for="province">استان</label>
                        <select
                            id="province"
                            v-model="provinceId"
                            class="focus:border-brand rounded-xl border border-gray-200 px-4 py-3 text-sm focus:outline-none"
                            @change="onProvinceChange"
                        >
                            <option :value="null" disabled>انتخاب استان</option>
                            <option v-for="p in provinces" :key="p.id" :value="p.id">
                                {{ p.name }}
                            </option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-gray-500" for="city">شهر</label>
                        <select
                            id="city"
                            v-model="form.city_id"
                            :disabled="!provinceId || loadingCities"
                            class="focus:border-brand rounded-xl border border-gray-200 px-4 py-3 text-sm focus:outline-none disabled:bg-gray-50"
                        >
                            <option :value="null" disabled>
                                {{ loadingCities ? 'در حال بارگذاری...' : 'انتخاب شهر' }}
                            </option>
                            <option v-for="c in cities" :key="c.id" :value="c.id">
                                {{ c.name }}
                            </option>
                        </select>
                        <span v-if="form.errors.city_id" class="text-xs text-red-600">{{
                            form.errors.city_id
                        }}</span>
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <div class="flex items-center justify-between">
                        <label class="text-xs text-gray-500" for="address">نشانی</label>
                        <button
                            type="button"
                            class="text-brand text-xs disabled:opacity-50"
                            :disabled="form.latitude == null || filling"
                            @click="fillFromMap"
                        >
                            {{ filling ? 'در حال دریافت...' : 'پر کردن نشانی از نقشه' }}
                        </button>
                    </div>
                    <textarea
                        id="address"
                        v-model="form.address"
                        rows="2"
                        class="focus:border-brand rounded-xl border border-gray-200 px-4 py-3 text-sm focus:outline-none"
                    ></textarea>
                    <span v-if="form.errors.address" class="text-xs text-red-600">{{
                        form.errors.address
                    }}</span>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-gray-500" for="plate">پلاک</label>
                        <input
                            id="plate"
                            v-model="form.plate"
                            type="text"
                            class="focus:border-brand rounded-xl border border-gray-200 px-4 py-3 text-sm focus:outline-none"
                        />
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-gray-500" for="unit">واحد</label>
                        <input
                            id="unit"
                            v-model="form.unit"
                            type="text"
                            class="focus:border-brand rounded-xl border border-gray-200 px-4 py-3 text-sm focus:outline-none"
                        />
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <span class="text-xs text-gray-500">عنوان نشانی</span>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="preset in ['منزل', 'محل کار', 'سایر']"
                            :key="preset"
                            type="button"
                            class="rounded-xl border px-4 py-2 text-sm transition"
                            :class="
                                titlePreset === preset
                                    ? 'border-brand bg-brand/10 text-brand'
                                    : 'border-gray-200 text-gray-600 hover:bg-gray-50'
                            "
                            @click="pickTitle(preset)"
                        >
                            {{ preset }}
                        </button>
                    </div>
                    <input
                        v-if="titlePreset === 'سایر'"
                        v-model="customTitle"
                        type="text"
                        placeholder="عنوان دلخواه"
                        class="focus:border-brand rounded-xl border border-gray-200 px-4 py-3 text-sm focus:outline-none"
                    />
                    <span v-if="form.errors.name" class="text-xs text-red-600">{{
                        form.errors.name
                    }}</span>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-gray-500" for="postal_code">کد پستی</label>
                        <input
                            id="postal_code"
                            v-model="form.postal_code"
                            type="text"
                            dir="ltr"
                            class="focus:border-brand rounded-xl border border-gray-200 px-4 py-3 text-left text-sm focus:outline-none"
                        />
                        <span v-if="form.errors.postal_code" class="text-xs text-red-600">{{
                            form.errors.postal_code
                        }}</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-gray-500" for="phone">شماره موبایل</label>
                        <input
                            id="phone"
                            v-model="form.phone"
                            type="text"
                            dir="ltr"
                            class="focus:border-brand rounded-xl border border-gray-200 px-4 py-3 text-left text-sm focus:outline-none"
                        />
                        <span v-if="form.errors.phone" class="text-xs text-red-600">{{
                            form.errors.phone
                        }}</span>
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input v-model="form.prime" type="checkbox" class="accent-brand h-4 w-4" />
                    نشانی پیش‌فرض
                </label>

                <div class="flex justify-end gap-2 pt-2">
                    <button
                        type="button"
                        class="rounded-xl border border-gray-200 px-5 py-3 text-sm text-gray-600 transition hover:bg-gray-50"
                        @click="close"
                    >
                        انصراف
                    </button>
                    <button
                        type="submit"
                        class="bg-brand rounded-xl px-6 py-3 text-sm font-bold text-white transition hover:opacity-90 disabled:opacity-60"
                        :disabled="form.processing"
                    >
                        {{ isEdit ? 'ذخیره تغییرات' : 'افزودن نشانی' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
