<template>
    <div v-if="currencyConverter" class="inline-block">
        <template v-if="currencyConverter.position === 'l'">
            <span class="product-price is-sale" v-if="productPrice.sale > 0">
                <del>{{ currencyConverter.symbol }}{{ moneyFormat(productPrice.base, this.currencyConverter.decimal_digits) }}</del>
                <span class="text-danger ps-1">{{ currencyConverter.symbol }}{{ moneyFormat(productPrice.sale, this.currencyConverter.decimal_digits) }}</span>
            </span>
            <span class="product-price" v-else>
                {{ currencyConverter.symbol }}{{ moneyFormat(productPrice.base, this.currencyConverter.decimal_digits) }}
            </span>
        </template>
        <template v-else-if="currencyConverter.position === 'r'">
            <span class="product-price is-sale" v-if="productPrice.sale > 0">
                <del>{{ moneyFormat(productPrice.base, this.currencyConverter.decimal_digits) }}{{ currencyConverter.symbol }}</del>
                <span class="text-danger ps-1">{{ moneyFormat(productPrice.sale, this.currencyConverter.decimal_digits) }}{{ currencyConverter.symbol }}</span>
            </span>
            <span class="product-price" v-else>
                {{ moneyFormat(productPrice.base, this.currencyConverter.decimal_digits) }}{{ currencyConverter.symbol }}
            </span>
        </template>
    </div>
    <div v-else>___</div>
</template>

<script>
import { mapGetters } from 'vuex'
export default {
    props: ['productPrice'],
    computed: {
        ...mapGetters(['currencyConverter', 'moneyFormat']),
    }
}
</script>