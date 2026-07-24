<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppHead from '@/Components/AppHead.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import HeroSlider from '@/Components/Home/HeroSlider.vue';
import CategoryStrip from '@/Components/Home/CategoryStrip.vue';
import BannerGrid from '@/Components/Home/BannerGrid.vue';
import ProductCarousel from '@/Components/Home/ProductCarousel.vue';
import BrandStrip from '@/Components/Home/BrandStrip.vue';

defineProps({
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
    productRows: {
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
            <HeroSlider :slides="slides" />

            <CategoryStrip :categories="categories" />

            <BannerGrid :banners="banners" />

            <ProductCarousel
                v-for="row in productRows"
                :key="row.title"
                :title="row.title"
                :view-all-url="row.viewAllUrl"
                :products="row.products"
            />

            <BrandStrip :brands="brands" />
        </div>
    </AppLayout>
</template>
