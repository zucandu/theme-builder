<template>
    <footer class="mt-5 pt-3">
        <div class="footer__subscribe bg-success">
            <div class="container py-5">
                <div class="row align-items-center">
                    <div class="col-lg-4 text-white mb-3 mb-lg-0">
                        <div class="h3 fw-bold">{{ $t('Sign Up For Newsletter') }}</div>
                        <p class="mb-0">{{ $t('Subscribe our newsletter to receive the latest news and exclusive offers every week.') }}</p>
                    </div>
                    <div class="col-lg-7 offset-lg-1">
                        <div class="d-sm-flex">
                            <subscribe-form></subscribe-form>
                            <div class="ms-0 ms-sm-4 mt-3 mt-sm-0 text-white d-sm-inline-block z-subscribe-question d-flex">
                                <div>{{ $t('Ask a question?') }}</div>
                                <div class="h5 mb-0">
                                    <router-link to="/contact-us" class="text-white text-decoration-none ms-2 ms-sm-0">{{ $t('Contact us') }}</router-link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <block-element menu-key="footer-middle" :block-loading="1"></block-element>
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

        <!-- Used to hook into the account order list. -->
        <template v-for="(component, index) in $pluginStorefrontHooks['footer']" :key="index">
            <component :is="component"></component>
        </template>
        
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