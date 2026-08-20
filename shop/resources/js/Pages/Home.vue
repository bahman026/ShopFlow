<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppHead from '@/Components/AppHead.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import SliderSlot from '@/Components/SliderSlot.vue';
import CategoryStrip from '@/Components/Home/CategoryStrip.vue';
import BannerSlot from '@/Components/BannerSlot.vue';
import ProductCarousel from '@/Components/ProductCarousel.vue';
import BrandStrip from '@/Components/Home/BrandStrip.vue';

defineProps({
    topBanners: {
        type: Array,
        default: () => [],
    },
    slides: {
        type: Array,
        default: () => [],
    },
    categories: {
        type: Array,
        default: () => [],
    },
    banners: {
        type: Array,
        default: () => [],
    },
    secondarySlides: {
        type: Array,
        default: () => [],
    },
    productRows: {
        type: Array,
        default: () => [],
    },
    tagRows: {
        type: Array,
        default: () => [],
    },
    brands: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const seo = computed(() => page.props.seo ?? {});

const jsonLd = computed(() => [
    {
        '@context': 'https://schema.org',
        '@type': 'Organization',
        name: seo.value.siteName ?? '',
        url: seo.value.url ?? '',
    },
    {
        '@context': 'https://schema.org',
        '@type': 'WebSite',
        name: seo.value.siteName ?? '',
        url: seo.value.url ?? '',
    },
]);
</script>

<template>
    <AppHead
        title="فروشگاه اینترنتی"
        description="خرید آنلاین از فروشگاه اینترنتی ShopFlow؛ پوشاک، لوازم آرایشی، کالای دیجیتال و کالاهای خانه با بهترین قیمت."
        :json-ld="jsonLd"
    />

    <AppLayout>
        <div class="flex flex-col gap-10">
            <BannerSlot :banners="topBanners" layout="wide" />

            <SliderSlot :slides="slides" />

            <CategoryStrip :categories="categories" />

            <BannerSlot :banners="banners" />

            <SliderSlot :slides="secondarySlides" aspect="wide" />

            <ProductCarousel
                v-for="row in productRows"
                :key="row.title"
                :title="row.title"
                :view-all-url="row.viewAllUrl"
                :products="row.products"
            />

            <ProductCarousel
                v-for="row in tagRows"
                :key="row.viewAllUrl"
                :title="row.title"
                :view-all-url="row.viewAllUrl"
                :products="row.products"
            />

            <BrandStrip :brands="brands" />
        </div>
    </AppLayout>
</template>
