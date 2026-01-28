<script setup>
import { defineProps } from 'vue';
import { useSettingsStore } from '@/stores/settings';

const settingsStore = useSettingsStore();

// Define props to accept `price` and `currency` from the parent component
const props = defineProps({
    price: {
        type: [Number, String],
        required: true
    },
    currencyCode: {
        type: String,
        required: true
    }
});

const currency = settingsStore.findCurrencyByCode(props.currencyCode);
function moneyFormat(price, decimal) {
    return (+price).toFixed(decimal).toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
}
</script>

<template>
    <span class="display-price-with-currency">
        <template v-if="currency">
            <span v-if="currency.position === 'l'">
                {{ currency.symbol }}{{ moneyFormat(price, currency.decimal_digits) }}
            </span>
            <span v-else>
                {{ moneyFormat(price, currency.decimal_digits) }}{{ currency.symbol }}
            </span>
        </template>
        <span v-else>___</span>
    </span>
</template>