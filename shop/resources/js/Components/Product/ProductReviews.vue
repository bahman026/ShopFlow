<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLink from '@/Components/AppLink.vue';
import Icon from '@/Components/Icon.vue';
import RatingStars from '@/Components/RatingStars.vue';
import { uiIcons } from '@/fontawesome';
import { useFormat } from '@/composables/useFormat';

const props = defineProps({
    productId: {
        type: Number,
        required: true,
    },
    reviews: {
        type: Array,
        default: () => [],
    },
    averageRating: {
        type: Number,
        default: null,
    },
    reviewCount: {
        type: Number,
        default: 0,
    },
    canReview: {
        type: Boolean,
        default: false,
    },
});

const { formatDate } = useFormat();

const showForm = ref(false);
const submitted = ref(false);

const form = useForm({
    rating: 0,
    heading: '',
    content: '',
});

function submit() {
    form.post(`/products/${props.productId}/reviews`, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            showForm.value = false;
            submitted.value = true;
        },
    });
}
</script>

<template>
    <section id="reviews">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <h2 class="text-lg font-bold text-gray-900">دیدگاه کاربران</h2>
                <RatingStars
                    v-if="averageRating !== null"
                    :rating="averageRating"
                    :count="reviewCount"
                />
            </div>

            <button
                v-if="canReview && !showForm"
                type="button"
                class="border-brand text-brand hover:bg-brand flex h-9 items-center rounded-xl border px-4 text-sm font-bold transition hover:text-white"
                @click="showForm = true"
            >
                ثبت دیدگاه
            </button>
        </div>

        <p v-if="submitted" class="mb-4 rounded-xl bg-green-50 px-4 py-3 text-sm text-green-700">
            دیدگاه شما ثبت شد و پس از تأیید نمایش داده می‌شود.
        </p>

        <form
            v-if="canReview && showForm"
            class="mb-6 flex flex-col gap-4 rounded-2xl border border-gray-100 bg-white p-4"
            @submit.prevent="submit"
        >
            <div class="flex flex-col gap-1">
                <span class="text-xs text-gray-500">امتیاز شما</span>
                <div class="flex items-center gap-1">
                    <button
                        v-for="star in 5"
                        :key="star"
                        type="button"
                        class="text-xl transition"
                        :class="star <= form.rating ? 'text-amber-400' : 'text-gray-300'"
                        :aria-label="star + ' ستاره'"
                        @click="form.rating = star"
                    >
                        <Icon :icon="uiIcons.star" />
                    </button>
                </div>
                <span v-if="form.errors.rating" class="text-xs text-red-600">
                    {{ form.errors.rating }}
                </span>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-xs text-gray-500" for="review-heading">عنوان</label>
                <input
                    id="review-heading"
                    v-model="form.heading"
                    type="text"
                    class="focus:border-brand h-11 rounded-xl border border-gray-200 px-3 text-sm outline-none"
                />
                <span v-if="form.errors.heading" class="text-xs text-red-600">
                    {{ form.errors.heading }}
                </span>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-xs text-gray-500" for="review-content">متن دیدگاه</label>
                <textarea
                    id="review-content"
                    v-model="form.content"
                    rows="4"
                    class="focus:border-brand rounded-xl border border-gray-200 p-3 text-sm outline-none"
                />
                <span v-if="form.errors.content" class="text-xs text-red-600">
                    {{ form.errors.content }}
                </span>
            </div>

            <div class="flex items-center gap-3">
                <button
                    type="submit"
                    class="bg-brand hover:bg-brand/90 h-11 rounded-xl px-6 text-sm font-bold text-white transition disabled:opacity-50"
                    :disabled="form.processing"
                >
                    ارسال دیدگاه
                </button>
                <button
                    type="button"
                    class="text-sm text-gray-500 transition hover:text-gray-700"
                    @click="showForm = false"
                >
                    انصراف
                </button>
            </div>
        </form>

        <p v-if="!canReview" class="mb-6 rounded-xl bg-gray-50 px-4 py-3 text-sm text-gray-500">
            برای ثبت دیدگاه ابتدا
            <AppLink href="/login" class="text-brand font-medium hover:underline">وارد شوید</AppLink
            >.
        </p>

        <div v-if="reviews.length" class="flex flex-col gap-4">
            <article
                v-for="review in reviews"
                :key="review.id"
                class="rounded-2xl border border-gray-100 bg-white p-4"
            >
                <div class="mb-2 flex items-start justify-between gap-3">
                    <div class="flex flex-col gap-1">
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-gray-800">{{ review.heading }}</h3>
                            <span
                                v-if="review.isBuyer"
                                class="flex items-center gap-1 rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700"
                            >
                                <Icon :icon="uiIcons.check" class="text-[0.6rem]" />
                                خریدار
                            </span>
                        </div>
                        <RatingStars v-if="review.rating" :rating="review.rating" />
                    </div>
                    <span v-if="review.date" class="shrink-0 text-xs text-gray-400">
                        {{ formatDate(review.date) }}
                    </span>
                </div>
                <p class="mb-2 text-sm leading-relaxed text-gray-600">{{ review.content }}</p>
                <span v-if="review.author" class="text-xs text-gray-400">
                    {{ review.author }}
                </span>
            </article>
        </div>

        <p v-else class="rounded-2xl bg-gray-50 px-4 py-6 text-center text-sm text-gray-400">
            هنوز دیدگاهی برای این محصول ثبت نشده است.
        </p>
    </section>
</template>
