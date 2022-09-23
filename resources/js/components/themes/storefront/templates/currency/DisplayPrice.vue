<template>
    <span>
        <span v-if="currencyConverter">
            <span v-if="currencyConverter.position === 'l'">
                {{ currencyConverter.symbol }}{{ moneyFormat(price, this.currencyConverter.decimal_digits) }}
            </span>
            <span v-else-if="currencyConverter.position === 'r'">
                {{ moneyFormat(price, this.currencyConverter.decimal_digits) }}{{ currencyConverter.symbol }}
            </span>
        </span>
        <span v-else>___</span>
    </span>
</template>

<script>
import { mapGetters } from 'vuex'
export default {
    props: ['price'],
    computed: {
        ...mapGetters(['currencyConverter', 'moneyFormat']),
    }
}
</script>