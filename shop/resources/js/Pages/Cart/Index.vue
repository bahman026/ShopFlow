<script setup>
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppHead from '@/Components/AppHead.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import AppLink from '@/Components/AppLink.vue';
import EmptyState from '@/Components/EmptyState.vue';
import CheckoutSteps from '@/Components/Checkout/CheckoutSteps.vue';
import CartLine from '@/Components/Cart/CartLine.vue';
import CartSummary from '@/Components/Cart/CartSummary.vue';
import { uiIcons } from '@/fontawesome';

const props = defineProps({
    lines: { type: Array, default: () => [] },
    summary: {
        type: Object,
        default: () => ({ count: 0, itemsTotal: 0, discount: 0, payable: 0 }),
    },
});

const page = usePage();
const status = computed(() => page.props.flash?.status ?? null);
const isEmpty = computed(() => props.lines.length === 0);

function updateCount(line, count) {
    router.patch('/cart/' + line.id, { count }, { preserveScroll: true });
}

function removeLine(line) {
    router.delete('/cart/' + line.id, { preserveScroll: true });
}

function checkout() {
    router.get('/checkout');
}
</script>

<template>
    <AppHead title="سبد خرید" :noindex="true" />

    <AppLayout>
        <div class="flex flex-col gap-8">
            <CheckoutSteps :current="1" />

            <p v-if="status" class="rounded-lg bg-green-50 p-3 text-center text-sm text-green-700">
                {{ status }}
            </p>

            <EmptyState
                v-if="isEmpty"
                :icon="uiIcons.cart"
                title="سبد خرید شما خالی است"
                description="می‌توانید برای مشاهده محصولات بیشتر به صفحه اصلی بروید."
            >
                <AppLink
                    href="/"
                    class="bg-brand hover:bg-brand/90 mt-4 inline-flex rounded-xl px-6 py-2.5 text-sm font-bold text-white transition"
                >
                    بازگشت به فروشگاه
                </AppLink>
            </EmptyState>

            <div v-else class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <div class="lg:col-span-8">
                    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                        <CartLine
                            v-for="line in lines"
                            :key="line.id"
                            :line="line"
                            @update="(count) => updateCount(line, count)"
                            @remove="removeLine(line)"
                        />
                    </div>
                </div>

                <div class="lg:col-span-4">
                    <CartSummary :summary="summary" @submit="checkout" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
