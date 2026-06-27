<script setup>
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppHead from '@/Components/AppHead.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import AppLink from '@/Components/AppLink.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import RatingStars from '@/Components/RatingStars.vue';
import ProductGallery from '@/Components/Product/ProductGallery.vue';
import VarietySelector from '@/Components/Product/VarietySelector.vue';
import BuyBox from '@/Components/Product/BuyBox.vue';
import ProductSpecs from '@/Components/Product/ProductSpecs.vue';
import ProductReviews from '@/Components/Product/ProductReviews.vue';
import ProductCarousel from '@/Components/Home/ProductCarousel.vue';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
    breadcrumbs: {
        type: Array,
        default: () => [],
    },
    related: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const seo = computed(() => page.props.seo ?? {});

const selectedVarietyId = ref(null);
const hasVarieties = props.product.varieties.length > 0;

const selectedVariety = computed(
    () => props.product.varieties.find((variety) => variety.id === selectedVarietyId.value) ?? null,
);

// Show every image: the product gallery plus each variety's image, combined
// and deduped by URL. Nothing is hidden when a variety is selected.
const galleryImages = computed(() => {
    const base = props.product.gallery ?? [];

    const varietyImages = props.product.varieties
        .map((variety) => variety.image)
        .filter((image) => image);

    const seen = new Set();

    return [...base, ...varietyImages].filter((image) => {
        if (seen.has(image.url)) {
            return false;
        }

        seen.add(image.url);

        return true;
    });
});

// Selecting a variety just switches the main image to that variety's photo.
const activeImageUrl = computed(() => selectedVariety.value?.image?.url ?? null);

// True when the product has varieties but none of them are buyable.
const allVarietiesOutOfStock = computed(
    () => hasVarieties && !props.product.varieties.some((variety) => variety.inStock),
);

const buyBox = computed(() => {
    if (selectedVariety.value) {
        return { ...selectedVariety.value, selected: true };
    }

    // Simple product without varieties: show its own price right away.
    if (!hasVarieties) {
        return {
            price: props.product.price,
            salePrice: props.product.salePrice,
            discountPercent: props.product.discountPercent,
            inStock: props.product.inStock,
            inventory: null,
            selected: true,
        };
    }

    // Everything is out of stock: show the unavailable state, not a prompt.
    if (allVarietiesOutOfStock.value) {
        return {
            price: null,
            salePrice: null,
            discountPercent: null,
            inStock: false,
            inventory: 0,
            selected: true,
        };
    }

    // Has varieties but none chosen yet: hide price until the user selects.
    return {
        price: null,
        salePrice: null,
        discountPercent: null,
        inStock: false,
        inventory: null,
        selected: false,
    };
});

const metaTitle = computed(() => props.product.title || props.product.heading);
const metaDescription = computed(
    () =>
        props.product.description ||
        `خرید ${props.product.heading} با بهترین قیمت از فروشگاه اینترنتی.`,
);

const jsonLd = computed(() => {
    const offerPrice = props.product.salePrice ?? props.product.price;

    const data = [
        {
            '@context': 'https://schema.org',
            '@type': 'Product',
            name: props.product.heading,
            description: metaDescription.value,
            sku: String(props.product.id),
            image: galleryImages.value.map((image) => image.url),
            offers: {
                '@type': 'Offer',
                price: offerPrice,
                priceCurrency: 'IRR',
                availability: props.product.inStock
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                url: props.product.canonical || seo.value.url || '',
            },
        },
        {
            '@context': 'https://schema.org',
            '@type': 'BreadcrumbList',
            itemListElement: props.breadcrumbs.map((item, index) => ({
                '@type': 'ListItem',
                position: index + 1,
                name: item.heading,
            })),
        },
    ];

    if (props.product.brand) {
        data[0].brand = { '@type': 'Brand', name: props.product.brand.heading };
    }

    return data;
});
</script>

<template>
    <AppHead
        :title="metaTitle"
        :description="metaDescription"
        :canonical="product.canonical || ''"
        :image="product.image ? product.image.url : ''"
        :noindex="product.noIndex"
        type="product"
        :json-ld="jsonLd"
    />

    <AppLayout>
        <div class="flex flex-col gap-8">
            <Breadcrumbs :items="breadcrumbs" />

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
                <div class="lg:col-span-5">
                    <ProductGallery
                        :images="galleryImages"
                        :active-url="activeImageUrl"
                        :alt="product.heading"
                    />
                </div>

                <div class="flex flex-col gap-4 lg:col-span-4">
                    <h1 class="text-xl leading-relaxed font-bold text-gray-900">
                        {{ product.heading }}
                    </h1>

                    <div class="flex flex-wrap items-center gap-4">
                        <AppLink
                            v-if="product.brand"
                            :href="product.brand.url"
                            class="text-brand text-sm transition hover:underline"
                        >
                            {{ product.brand.heading }}
                        </AppLink>

                        <a href="#reviews">
                            <RatingStars :rating="0" :count="product.reviewCount" />
                        </a>
                    </div>

                    <VarietySelector
                        v-model="selectedVarietyId"
                        :axes="product.variantAxes"
                        :varieties="product.varieties"
                    />
                </div>

                <div class="lg:col-span-3">
                    <BuyBox
                        :price="buyBox.price"
                        :sale-price="buyBox.salePrice"
                        :discount-percent="buyBox.discountPercent"
                        :in-stock="buyBox.inStock"
                        :inventory="buyBox.inventory"
                        :selected="buyBox.selected"
                    />
                </div>
            </div>

            <section v-if="product.content" id="description">
                <h2 class="mb-4 text-lg font-bold text-gray-900">معرفی محصول</h2>
                <div
                    class="prose prose-sm max-w-none leading-loose text-gray-700"
                    v-html="product.content"
                />
            </section>

            <ProductSpecs :specs="product.specs" />

            <ProductReviews :reviews="product.reviews" />

            <ProductCarousel v-if="related.length" title="محصولات مشابه" :products="related" />
        </div>
    </AppLayout>
</template>
