<template>
    <span class="inline-block">
        <span v-if="currencyConverter">
            <span v-if="currencyConverter.position === 'l'">
                {{currencyConverter.symbol}}{{displayPrice(priceRange.min, 1)}} - {{currencyConverter.symbol}}{{displayPrice(priceRange.max, 1)}}
            </span>
            <span v-else-if="currencyConverter.position === 'r'">
                {{displayPrice(priceRange.min, 1)}}{{currencyConverter.symbol}} - {{displayPrice(priceRange.max, 1)}}{{currencyConverter.symbol}}
            </span>
        </span>
        <span v-else>___</span>
    </span>
</template>

<script>
import { mapGetters } from 'vuex'
export default {
    props: ['priceRange'],
    computed: {
        ...mapGetters(['currencyConverter', 'displayPrice']),
    }
}
</script>