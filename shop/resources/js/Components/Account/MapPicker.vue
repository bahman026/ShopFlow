<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import Icon from '@/Components/Icon.vue';
import { uiIcons } from '@/fontawesome';

const props = defineProps({
    modelValue: {
        type: Object,
        default: () => ({ lat: null, lng: null }),
    },
});

const emit = defineEmits(['update:modelValue', 'selected']);

const TILE = 256;
const TEHRAN = { lat: 35.6892, lng: 51.389 };

const center = ref({
    lat: props.modelValue?.lat ?? TEHRAN.lat,
    lng: props.modelValue?.lng ?? TEHRAN.lng,
});
const zoom = ref(props.modelValue?.lat != null ? 16 : 12);

const imgEl = ref(null);
const dragging = ref(false);
const offset = ref({ x: 0, y: 0 });
let start = null;

const hasValue = computed(() => props.modelValue?.lat != null && props.modelValue?.lng != null);

const imageUrl = computed(
    () =>
        '/account/addresses-static?lat=' +
        center.value.lat +
        '&lng=' +
        center.value.lng +
        '&zoom=' +
        zoom.value,
);

watch(
    () => props.modelValue,
    (value) => {
        if (value?.lat != null && value?.lng != null) {
            if (value.lat !== center.value.lat || value.lng !== center.value.lng) {
                center.value = { lat: value.lat, lng: value.lng };
            }
        }
    },
);

function project(lat, lng) {
    const sin = Math.sin((lat * Math.PI) / 180);
    const scale = TILE * Math.pow(2, zoom.value);
    return {
        x: ((lng + 180) / 360) * scale,
        y: (0.5 - Math.log((1 + sin) / (1 - sin)) / (4 * Math.PI)) * scale,
    };
}

function unproject(x, y) {
    const scale = TILE * Math.pow(2, zoom.value);
    const lng = (x / scale) * 360 - 180;
    const n = Math.PI - (2 * Math.PI * y) / scale;
    const lat = (180 / Math.PI) * Math.atan(0.5 * (Math.exp(n) - Math.exp(-n)));
    return { lat, lng };
}

function emitCenter() {
    const value = {
        lat: Number(center.value.lat.toFixed(6)),
        lng: Number(center.value.lng.toFixed(6)),
    };
    emit('update:modelValue', value);
    emit('selected', value);
}

function onPointerDown(event) {
    dragging.value = true;
    start = { x: event.clientX, y: event.clientY };
    offset.value = { x: 0, y: 0 };
    window.addEventListener('pointermove', onPointerMove);
    window.addEventListener('pointerup', onPointerUp);
}

function onPointerMove(event) {
    if (!dragging.value || !start) {
        return;
    }
    offset.value = { x: event.clientX - start.x, y: event.clientY - start.y };
}

function onPointerUp() {
    if (!dragging.value) {
        return;
    }
    dragging.value = false;
    window.removeEventListener('pointermove', onPointerMove);
    window.removeEventListener('pointerup', onPointerUp);

    const img = imgEl.value;
    const dx = offset.value.x;
    const dy = offset.value.y;
    offset.value = { x: 0, y: 0 };

    if (img && img.clientWidth > 0 && img.naturalWidth > 0) {
        // Convert the on-screen drag into natural map pixels, then shift the
        // center the opposite way (dragging the map right moves the view left).
        const scale = img.naturalWidth / img.clientWidth;
        const world = project(center.value.lat, center.value.lng);
        center.value = unproject(world.x - dx * scale, world.y - dy * scale);
    }

    emitCenter();
}

function zoomIn() {
    zoom.value = Math.min(19, zoom.value + 1);
}

function zoomOut() {
    zoom.value = Math.max(5, zoom.value - 1);
}

function clear() {
    emit('update:modelValue', { lat: null, lng: null });
}

onBeforeUnmount(() => {
    window.removeEventListener('pointermove', onPointerMove);
    window.removeEventListener('pointerup', onPointerUp);
});
</script>

<template>
    <div class="flex flex-col gap-2">
        <div class="relative h-64 touch-none overflow-hidden rounded-xl border border-gray-200">
            <img
                ref="imgEl"
                :src="imageUrl"
                alt="انتخاب موقعیت روی نقشه"
                class="absolute inset-0 h-full w-full object-cover select-none"
                :class="dragging ? 'cursor-grabbing' : 'cursor-grab'"
                draggable="false"
                :style="{ transform: `translate(${offset.x}px, ${offset.y}px)` }"
                @pointerdown.prevent="onPointerDown"
            />

            <!-- Fixed center pin: the selected location is always the map center. -->
            <div
                class="pointer-events-none absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-full text-3xl text-red-600 drop-shadow"
            >
                <Icon :icon="uiIcons.location" />
            </div>

            <div class="absolute bottom-2 left-2 flex flex-col gap-1">
                <button
                    type="button"
                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/90 text-gray-700 shadow hover:bg-white"
                    aria-label="بزرگنمایی"
                    @click="zoomIn"
                >
                    <Icon :icon="uiIcons.plus" />
                </button>
                <button
                    type="button"
                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/90 text-gray-700 shadow hover:bg-white"
                    aria-label="کوچک‌نمایی"
                    @click="zoomOut"
                >
                    <Icon :icon="uiIcons.minus" />
                </button>
            </div>
        </div>

        <div class="flex items-center justify-between text-xs text-gray-400">
            <span v-if="hasValue" dir="ltr">{{ modelValue.lat }}, {{ modelValue.lng }}</span>
            <span v-else>نقشه را بکشید تا نشانگر روی موقعیت دقیق قرار گیرد.</span>
            <button v-if="hasValue" type="button" class="text-brand" @click="clear">
                حذف موقعیت
            </button>
        </div>
    </div>
</template>
