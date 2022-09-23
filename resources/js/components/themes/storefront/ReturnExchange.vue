<template>
    <router-view></router-view>
</template>

<script>
import rma from '@/store/modules/storefront/account/rma'
export default {
    created() {
        this.$store.registerModule("rma", rma)

    }, 
    beforeUnmount() {
        this.$store.unregisterModule('rma')
    }
}
</script>