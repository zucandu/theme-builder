<template>
    <footer class="mt-5 pt-3">
        <block-element menu-key="footer-middle" :block-loading="1"></block-element>
        <section class="py-4 border-top mt-5 bg-gray-200">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-8 order-md-0 order-1">
                        &copy; {{ storeConfig.store_name }} {{ year }}. All rights reserved.
                        <img src="/storage/credit-card-accept.png" alt="Credit Cards" width="197" height="20">
                    </div>
                    <div class="col-md-4 text-md-end order-md-1 order-0 mb-3 mb-md-0">
                        <subscribe-form></subscribe-form>
                    </div>
                </div>
            </div>
        </section>
    </footer>
</template>

<script>
import SubscribeForm from '@theme/storefront/templates/element/SubscribeForm'
import BlockElement from '@theme/storefront/templates/menu/BlockElement'
import { mapState } from 'vuex'
export default {
    data: () => ({
        year: new Date().getFullYear()
    }),
    components: {
        SubscribeForm, BlockElement
    },
    computed: {
        ...mapState({
            storeConfig: state => state.setting.storeConfig,
        })
    }
}
</script>