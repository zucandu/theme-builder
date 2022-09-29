<template>
    <footer class="mt-5 pt-3">
        <block-element menu-key="footer-middle" :block-loading="1"></block-element>
        <section class="container">
            <div class="row mt-3">
                <div class="col-12 col-lg-3 col-md-6">
                    <subscribe-form></subscribe-form>
                </div>
            </div>
        </section>
        <section class="py-4 border-top mt-5 bg-gray-200">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6">&copy; {{ storeConfig.store_name }} {{ year }}. All rights reserved.</div>
                    <div class="col-lg-6 text-lg-end">
                        <img src="/storage/credit-card-accept.png" alt="Credit Cards" width="197" height="20">
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