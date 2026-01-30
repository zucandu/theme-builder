<script setup>

import { useHelpers } from '@/composables/useHelpers';

const { translateItemField } = useHelpers();

// Define props to accept `price` and `currency` from the parent component
const props = defineProps({
    product: {
        type: Object,
        required: true
    }
});
const product = props.product;
</script>

<template>
    <div>
        <!-- Product Image -->
        <img :src="`${zucConfig.store_url}/storage/${product.images[0]?.src}`" class="w-full h-40 object-cover rounded-md" />

        <!-- Product Name -->
        <h3 class="mt-4 text-lg font-semibold text-gray-800">
            <LocalizedLink :to="`/product/${translateItemField(product, 'slug', $i18n.locale)}`" class="hover:underline">
                {{ translateItemField(product, 'name', $i18n.locale) }}
            </LocalizedLink>
        </h3>

        <!-- Product Price -->
        <p><PriceConverter :product="product" /></p>
    </div>
</template>