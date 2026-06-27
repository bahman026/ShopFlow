<script setup>
import { useFormat } from '@/composables/useFormat';

defineProps({
    reviews: {
        type: Array,
        default: () => [],
    },
});

const { formatDate } = useFormat();
</script>

<template>
    <section id="reviews">
        <h2 class="mb-4 text-lg font-bold text-gray-900">دیدگاه کاربران</h2>

        <div v-if="reviews.length" class="flex flex-col gap-4">
            <article
                v-for="review in reviews"
                :key="review.id"
                class="rounded-2xl border border-gray-100 bg-white p-4"
            >
                <div class="mb-2 flex items-center justify-between">
                    <h3 class="font-bold text-gray-800">{{ review.heading }}</h3>
                    <span v-if="review.date" class="text-xs text-gray-400">
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
