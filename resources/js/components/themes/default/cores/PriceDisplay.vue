<script setup>
import { defineProps, computed } from 'vue';
import { useSettingsStore } from '@/stores/settings';
import { useHelpers } from '@/composables/useHelpers';

const settingsStore = useSettingsStore();
const { formatCurrency } = useHelpers();

// Define props to accept `price` and `currency` from the parent component
const props = defineProps({
    price: {
        type: [Number, String],
        required: true
    }
});

const priceReadOnly = computed(() => {
    const { decimal_digits, code, rate } = settingsStore.selectedCurrencyObject;
    return formatCurrency(props.price, decimal_digits, code);
});

</script>

<template>
    <div>{{ priceReadOnly }}</div>
</template>
