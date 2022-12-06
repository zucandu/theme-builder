<template>
    <footer>
        <div class="container-fluid bg-secondary">
            <div class="container newsletter-container">
                <div class="row h-100">
                    <div class="col-6 h-100 d-flex align-items-center">
                        <div class="newsletter-form">
                            <div class="h4 fw-bold">{{ $t('Join our newsletter and get...') }}</div>
                            <p class="small">{{ $t('Join our email subscription now to get updates on promotions and coupons.') }}</p>
                            <subscribe-form></subscribe-form>
                        </div>
                    </div>
                    <div class="col-6 d-none d-md-block text-end h-100">
                        <div class="position-relative h-100">
                            <img src="/storage/coupon.png" alt="Newsletter" class="img-fluid position-absolute bottom-0 end-0">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="container-fluid bg-gray-200">
            <block-element menu-key="footer-middle" :block-loading="1"></block-element>
        </div>

        <section class="py-4 border-top]">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-8 col-6 order-md-0 order-1">
                        &copy; {{ storeConfig.store_name }} {{ year }}. All rights reserved.
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