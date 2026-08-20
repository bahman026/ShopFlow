<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import AppLink from '@/Components/AppLink.vue';
import Icon from '@/Components/Icon.vue';
import { uiIcons } from '@/fontawesome';

/**
 * Renders the slider assigned to one SliderPositionEnum slot.
 *
 * `aspect` is the only thing that differs between slots: the home hero is a
 * wide band, a category header is shorter, and a product sidebar is a narrow
 * portrait. Everything else — autoplay, arrows, dots, captions — is shared.
 *
 * Renders nothing at all when the slot has no slides, so a page can hand it an
 * empty array without guarding.
 */
const props = defineProps({
    slides: {
        type: Array,
        default: () => [],
    },
    aspect: {
        type: String,
        default: 'hero',
        validator: (value) => ['hero', 'wide', 'portrait'].includes(value),
    },
    rounded: {
        type: String,
        default: 'rounded-2xl',
    },
});

const aspectClasses = {
    hero: 'aspect-[21/9] sm:aspect-[3/1]',
    wide: 'aspect-[16/9] sm:aspect-[4/1]',
    portrait: 'aspect-[4/5]',
};

const aspectClass = computed(() => aspectClasses[props.aspect] ?? aspectClasses.hero);

// A narrow slot has no room for side arrows; dots are enough to navigate.
const showArrows = computed(() => props.aspect !== 'portrait');

const current = ref(0);
const count = computed(() => props.slides.length);

function go(index) {
    if (count.value === 0) {
        return;
    }

    current.value = (index + count.value) % count.value;
}

const next = () => go(current.value + 1);
const prev = () => go(current.value - 1);

let timer = null;

onMounted(() => {
    if (count.value > 1) {
        timer = window.setInterval(next, 6000);
    }
});

onBeforeUnmount(() => {
    if (timer !== null) {
        window.clearInterval(timer);
    }
});
</script>

<template>
    <section
        v-if="count"
        class="relative overflow-hidden bg-gray-100"
        :class="rounded"
        aria-roledescription="carousel"
    >
        <div class="relative w-full" :class="aspectClass">
            <component
                :is="slide.url ? AppLink : 'div'"
                v-for="(slide, index) in slides"
                :key="slide.id"
                :href="slide.url || undefined"
                class="absolute inset-0 transition-opacity duration-700"
                :class="index === current ? 'opacity-100' : 'pointer-events-none opacity-0'"
            >
                <img
                    v-if="slide.image"
                    :src="slide.image.url"
                    :alt="slide.image.alt || slide.heading || ''"
                    class="h-full w-full object-cover"
                />
                <div
                    v-if="slide.heading || slide.label"
                    class="absolute right-0 bottom-0 left-0 bg-gradient-to-t from-black/60 to-transparent p-4 text-white sm:p-6"
                >
                    <p v-if="slide.heading" class="text-base font-bold sm:text-2xl">
                        {{ slide.heading }}
                    </p>
                    <p v-if="slide.label" class="mt-1 text-xs sm:text-sm">
                        {{ slide.label }}
                    </p>
                </div>
            </component>
        </div>

        <template v-if="count > 1">
            <template v-if="showArrows">
                <button
                    type="button"
                    class="absolute top-1/2 right-3 -translate-y-1/2 rounded-full bg-white/80 p-2 text-gray-800 shadow transition hover:bg-white"
                    aria-label="اسلاید قبلی"
                    @click="prev"
                >
                    <Icon :icon="uiIcons.chevronRight" />
                </button>
                <button
                    type="button"
                    class="absolute top-1/2 left-3 -translate-y-1/2 rounded-full bg-white/80 p-2 text-gray-800 shadow transition hover:bg-white"
                    aria-label="اسلاید بعدی"
                    @click="next"
                >
                    <Icon :icon="uiIcons.chevronLeft" />
                </button>
            </template>

            <div class="absolute bottom-3 left-1/2 flex -translate-x-1/2 gap-2">
                <button
                    v-for="(slide, index) in slides"
                    :key="slide.id"
                    type="button"
                    class="h-2 rounded-full transition-all"
                    :class="index === current ? 'bg-brand w-5' : 'w-2 bg-white/70'"
                    :aria-label="`اسلاید ${index + 1}`"
                    @click="go(index)"
                />
            </div>
        </template>
    </section>
</template>
