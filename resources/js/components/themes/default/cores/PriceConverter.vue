<script setup>
import { defineProps, computed } from 'vue';
import { useSettingsStore } from '@/stores/settings';
import { useHelpers } from '@/composables/useHelpers';
import { useProductStore } from '@/stores/catalog/product';

const settingsStore = useSettingsStore();
const { formatCurrency } = useHelpers();

// Define props to accept `price` and `currency` from the parent component
const props = defineProps({
    product: {
        type: Object,
        required: true
    },
    qty: {
        type: Number,
        default: 1
    },
});

const productStore = useProductStore();

// Compute price object every time product changes
const price = computed(() => productStore.finalizeProductPrice(props.product));

// Compute formatted prices
const formattedPrice = computed(() => {
    const { decimal_digits, code, rate } = settingsStore.selectedCurrencyObject;

    // Calculate original retail price
    const retail = (price.value?.retail ?? 0) * rate * (props.qty || 1);
    const original = formatCurrency(retail, decimal_digits, code);

    // Calculate discounted price if sale price is valid
    let discounted;
    if (+((price.value?.sale) ?? 0) > 0) {
        const sale = price.value.sale * rate * (props.qty || 1);
        discounted = formatCurrency(sale, decimal_digits, code);
    }

    return { original, discounted };
});

</script>

<template>
    <div class="inline">
        <template v-if="formattedPrice.discounted">
            <span class="line-through text-gray-500">{{ formattedPrice.original }}</span>
            <span class="ml-2 text-red-600">{{ formattedPrice.discounted }}</span>
        </template>
        <template v-else>
            <span>{{ formattedPrice.original }}</span>
        </template>
    </div>
</template>