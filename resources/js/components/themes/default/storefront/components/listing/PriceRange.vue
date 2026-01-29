<script setup>
import { computed } from 'vue';
import { useSettingsStore } from '@/stores/settings';
import { useHelpers } from '@/composables/useHelpers';

const settingsStore = useSettingsStore();
const { formatCurrency } = useHelpers();


// Define props to accept `price` and `currency` from the parent component
const props = defineProps({
    priceRange: {
        type: String,
        required: true
    }
});

// Get price min, max
const priceMinMax = computed(() => {
    const { decimal_digits, code, rate } = settingsStore.selectedCurrencyObject;
    const [minPrice, maxPrice] = props.priceRange.split('-');
    return { 
        min: formatCurrency(minPrice * rate, 0/* decimal_digits */, code), 
        max: formatCurrency(maxPrice * rate, 0/* decimal_digits */, code) 
    }
});

</script>

<template>
    <span>{{ priceMinMax.min }} - {{ priceMinMax.max }}</span>
</template>
