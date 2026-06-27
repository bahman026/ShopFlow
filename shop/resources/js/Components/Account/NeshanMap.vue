<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: Object,
        default: () => ({ lat: null, lng: null }),
    },
    mapKey: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:modelValue', 'selected']);

const CSS_URL = 'https://static.neshan.org/sdk/leaflet/v1.9.4/neshan-sdk/v1.0.8/index.css';
const JS_URL = 'https://static.neshan.org/sdk/leaflet/v1.9.4/neshan-sdk/v1.0.8/index.js';
const TEHRAN = { lat: 35.6892, lng: 51.389 };

const mapEl = ref(null);
const failed = ref(false);
let map = null;
let marker = null;

function loadSdk() {
    return new Promise((resolve, reject) => {
        if (window.L && window.L.Map) {
            resolve();
            return;
        }

        if (!document.querySelector(`link[href="${CSS_URL}"]`)) {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = CSS_URL;
            document.head.appendChild(link);
        }

        const existing = document.querySelector('script[data-neshan]');
        if (existing) {
            existing.addEventListener('load', () => resolve());
            existing.addEventListener('error', () => reject(new Error('sdk')));
            return;
        }

        const script = document.createElement('script');
        script.src = JS_URL;
        script.dataset.neshan = 'true';
        script.onload = () => resolve();
        script.onerror = () => reject(new Error('sdk'));
        document.body.appendChild(script);
    });
}

function publish(latlng) {
    const value = {
        lat: Number(latlng.lat.toFixed(6)),
        lng: Number(latlng.lng.toFixed(6)),
    };
    emit('update:modelValue', value);
    emit('selected', value);
}

async function init() {
    if (!props.mapKey) {
        failed.value = true;
        return;
    }

    try {
        await loadSdk();
    } catch {
        failed.value = true;
        return;
    }

    if (!mapEl.value) {
        return;
    }

    const start = {
        lat: props.modelValue?.lat ?? TEHRAN.lat,
        lng: props.modelValue?.lng ?? TEHRAN.lng,
    };

    map = new window.L.Map(mapEl.value, {
        key: props.mapKey,
        maptype: 'standard-day',
        poi: true,
        traffic: false,
        center: [start.lat, start.lng],
        zoom: 14,
    });

    marker = window.L.marker([start.lat, start.lng], { draggable: true }).addTo(map);
    marker.on('dragend', () => publish(marker.getLatLng()));
    map.on('click', (event) => {
        marker.setLatLng(event.latlng);
        publish(event.latlng);
    });
}

watch(
    () => props.modelValue,
    (value) => {
        if (marker && value?.lat != null && value?.lng != null) {
            const current = marker.getLatLng();
            if (current.lat !== value.lat || current.lng !== value.lng) {
                marker.setLatLng([value.lat, value.lng]);
                map?.setView([value.lat, value.lng]);
            }
        }
    },
);

onMounted(init);

onBeforeUnmount(() => {
    map?.remove();
    map = null;
    marker = null;
});
</script>

<template>
    <div class="overflow-hidden rounded-xl border border-gray-200">
        <div
            v-if="failed"
            class="flex h-48 items-center justify-center bg-gray-50 p-4 text-center text-xs text-gray-500"
        >
            نقشه در دسترس نیست (به کلید نقشه وب نشان نیاز است). می‌توانید نشانی را به‌صورت دستی وارد
            کنید.
        </div>
        <div v-else ref="mapEl" class="h-56 w-full"></div>
    </div>
</template>
