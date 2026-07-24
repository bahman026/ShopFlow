<script setup>
import { computed, reactive, ref, watch } from 'vue';
import Icon from '@/Components/Icon.vue';
import { faChevronDown } from '@fortawesome/free-solid-svg-icons';
import { formatNumber } from '@/composables/useFormat';

const props = defineProps({
    filters: {
        type: Object,
        required: true,
    },
    applied: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['apply', 'reset', 'close']);

const priceFloor = computed(() => props.filters.price.min);
const priceCeil = computed(() => props.filters.price.max);
const priceStep = computed(() => {
    const span = priceCeil.value - priceFloor.value;
    return span > 0 ? Math.max(1, Math.round(span / 100)) : 1;
});

const form = reactive({
    categories: [...props.applied.categories],
    minPrice: props.applied.minPrice ?? priceFloor.value,
    maxPrice: props.applied.maxPrice ?? priceCeil.value,
    inStock: props.applied.inStock,
});

const categoryQuery = ref('');

const open = reactive({
    availability: true,
    price: true,
    category: true,
});

watch(
    () => props.applied,
    (applied) => {
        form.categories = [...applied.categories];
        form.minPrice = applied.minPrice ?? priceFloor.value;
        form.maxPrice = applied.maxPrice ?? priceCeil.value;
        form.inStock = applied.inStock;
    },
    { deep: true },
);

const filteredCategories = computed(() => {
    const q = categoryQuery.value.trim();
    if (!q) {
        return props.filters.categories;
    }
    return props.filters.categories.filter((category) => category.heading.includes(q));
});

const hasCategories = () => props.filters.categories.length > 0;
const hasPriceRange = () => priceCeil.value > priceFloor.value;

const fillStyle = computed(() => {
    const span = priceCeil.value - priceFloor.value || 1;
    const right = ((form.minPrice - priceFloor.value) / span) * 100;
    const left = ((priceCeil.value - form.maxPrice) / span) * 100;
    return { right: right + '%', left: left + '%' };
});

function clampPrice(edge) {
    if (form.minPrice > form.maxPrice) {
        if (edge === 'min') {
            form.minPrice = form.maxPrice;
        } else {
            form.maxPrice = form.minPrice;
        }
    }
}

function commit() {
    emit('apply', {
        categories: [...form.categories],
        minPrice: form.minPrice > priceFloor.value ? form.minPrice : null,
        maxPrice: form.maxPrice < priceCeil.value ? form.maxPrice : null,
        inStock: form.inStock,
    });
}
</script>

<template>
    <aside class="flex flex-col rounded-xl border border-gray-100 bg-white">
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
            <h2 class="text-sm font-bold text-gray-800">فیلترها</h2>
            <button
                type="button"
                class="hover:text-brand text-xs text-gray-400 transition"
                @click="emit('reset')"
            >
                حذف همه
            </button>
        </div>

        <!-- Availability -->
        <section class="border-b border-gray-100 px-4 py-3">
            <button
                type="button"
                class="flex w-full items-center justify-between"
                @click="open.availability = !open.availability"
            >
                <span class="text-xs font-bold text-gray-700">موجود بودن کالا</span>
                <Icon
                    :icon="faChevronDown"
                    class="text-[10px] text-gray-400 transition"
                    :class="{ 'rotate-180': open.availability }"
                />
            </button>
            <label
                v-show="open.availability"
                class="mt-3 flex cursor-pointer items-center justify-between"
            >
                <span class="text-sm text-gray-700">فقط کالاهای موجود</span>
                <input
                    v-model="form.inStock"
                    type="checkbox"
                    class="accent-brand h-4 w-4 rounded"
                    @change="commit"
                />
            </label>
        </section>

        <!-- Price -->
        <section v-if="hasPriceRange()" class="border-b border-gray-100 px-4 py-3">
            <button
                type="button"
                class="flex w-full items-center justify-between"
                @click="open.price = !open.price"
            >
                <span class="text-xs font-bold text-gray-700">محدوده قیمت (تومان)</span>
                <Icon
                    :icon="faChevronDown"
                    class="text-[10px] text-gray-400 transition"
                    :class="{ 'rotate-180': open.price }"
                />
            </button>

            <div v-show="open.price" class="mt-4">
                <div class="mb-2 flex items-center justify-between text-xs text-gray-600">
                    <span>{{ formatNumber(form.minPrice) }}</span>
                    <span>{{ formatNumber(form.maxPrice) }}</span>
                </div>
                <div class="price-slider relative h-5">
                    <div
                        class="absolute top-1/2 h-1 w-full -translate-y-1/2 rounded bg-gray-200"
                    ></div>
                    <div
                        class="bg-brand absolute top-1/2 h-1 -translate-y-1/2 rounded"
                        :style="fillStyle"
                    ></div>
                    <input
                        v-model.number="form.minPrice"
                        type="range"
                        :min="priceFloor"
                        :max="priceCeil"
                        :step="priceStep"
                        @input="clampPrice('min')"
                        @change="commit"
                    />
                    <input
                        v-model.number="form.maxPrice"
                        type="range"
                        :min="priceFloor"
                        :max="priceCeil"
                        :step="priceStep"
                        @input="clampPrice('max')"
                        @change="commit"
                    />
                </div>
            </div>
        </section>

        <!-- Category -->
        <section v-if="hasCategories()" class="border-b border-gray-100 px-4 py-3">
            <button
                type="button"
                class="flex w-full items-center justify-between"
                @click="open.category = !open.category"
            >
                <span class="text-xs font-bold text-gray-700">دسته‌بندی</span>
                <Icon
                    :icon="faChevronDown"
                    class="text-[10px] text-gray-400 transition"
                    :class="{ 'rotate-180': open.category }"
                />
            </button>

            <div v-show="open.category" class="mt-3 flex flex-col gap-3">
                <input
                    v-model="categoryQuery"
                    type="text"
                    placeholder="نام مورد نظر را جستجو کنید"
                    class="focus:border-brand w-full rounded-lg border border-gray-200 px-3 py-2 text-xs focus:outline-none"
                />
                <div class="flex max-h-56 flex-col gap-2.5 overflow-y-auto">
                    <label
                        v-for="category in filteredCategories"
                        :key="category.id"
                        class="flex cursor-pointer items-center justify-between gap-2 text-sm text-gray-700"
                    >
                        <span class="flex items-center gap-2">
                            <input
                                v-model="form.categories"
                                type="checkbox"
                                :value="category.slug"
                                class="accent-brand h-4 w-4 rounded"
                                @change="commit"
                            />
                            {{ category.heading }}
                        </span>
                        <span class="text-xs text-gray-400">{{
                            formatNumber(category.count)
                        }}</span>
                    </label>
                    <p v-if="filteredCategories.length === 0" class="text-xs text-gray-400">
                        دسته‌ای یافت نشد.
                    </p>
                </div>
            </div>
        </section>

        <p v-if="!hasCategories() && !hasPriceRange()" class="px-4 py-3 text-xs text-gray-400">
            فیلتر دیگری برای این برند تعریف نشده است.
        </p>

        <button
            type="button"
            class="bg-brand m-4 h-11 rounded-xl text-sm font-bold text-white transition hover:opacity-90 lg:hidden"
            @click="emit('close')"
        >
            نمایش نتایج
        </button>
    </aside>
</template>

<style scoped>
.price-slider input[type='range'] {
    position: absolute;
    top: 0;
    right: 0;
    width: 100%;
    height: 100%;
    margin: 0;
    background: transparent;
    pointer-events: none;
    -webkit-appearance: none;
    appearance: none;
}

.price-slider input[type='range']::-webkit-slider-thumb {
    pointer-events: auto;
    -webkit-appearance: none;
    appearance: none;
    height: 16px;
    width: 16px;
    border-radius: 9999px;
    background: #fff;
    border: 2px solid var(--color-brand, #ff8615);
    cursor: pointer;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.15);
}

.price-slider input[type='range']::-moz-range-thumb {
    pointer-events: auto;
    height: 16px;
    width: 16px;
    border-radius: 9999px;
    background: #fff;
    border: 2px solid var(--color-brand, #ff8615);
    cursor: pointer;
}

.price-slider input[type='range']::-moz-range-track {
    background: transparent;
}
</style>
