<template>
    <section v-if="loaded" class="section-upsells row mt-5">
        <template v-if="loadedUpsell">
            <template v-if="upsells.length > 0">
                <img @load="initTinySliderUpSell" src="/storage/pixel.gif" alt="js" class="d-none">
                <div class="col-12 h3 fw-light pb-5 mb-0"><span class="fw-bold text-dark">{{ $t("Some things could be") }}</span> {{ $t('better') }}</div>
                <div :class="`col-12 position-relative ${loadingTinySliderUpSell ? 'd-none' : ''}`">
                    <div class="z-tiny-slider-upsell-section">
                        <product-widget :products="upsells" @pickItemRestock="pickItemRestock"></product-widget>
                    </div>
                </div>
                <template v-if="loadingTinySliderUpSell">
                    <product-widget-loading></product-widget-loading>
                </template>
            </template>
        </template>
    </section>
    <section v-else class="section-upsells row mt-5">
        <div class="col-12 h3 fw-light pb-5 mb-0">
            <div class="py-4 rounded col-4 bg-gray-200"></div>
        </div>
        <product-widget-loading></product-widget-loading>
    </section>
    <product-restock-modal :product-id="picked.id" :product-name="picked.name" :show-modal="showModal" @updateModalStatus="updateModalStatus"></product-restock-modal>

</template>

<script>
import ProductWidget from '@theme/storefront/templates/product/ProductWidget'
import ProductWidgetLoading from '@theme/storefront/templates/product/ProductWidgetLoading'
import ProductRestockModal from '@theme/storefront/templates/product/Restock'
import { mapGetters, mapState } from 'vuex'
export default {
    data: () => ({
        loadedUpsell: false,
        upsells: [],
        loadingTinySliderUpSell: true,
        picked: {
            id: undefined,
            name: undefined
        },
        showModal: false
    }),
    components: {  ProductWidget, ProductWidgetLoading, ProductRestockModal },
    props: ['loaded', 'items'],
    methods: {
        addToCart(item) {
            const productName = this.translation(item, 'name', this.$i18n.locale)
            this.$store.dispatch('addProduct2Cart', { id: item.id, cart_quantity: 1 }).then(() => {
                this.$store.commit('setAlert', {
                    'color': 'success', 
                    'message': `${productName} ${this.$t("has been added to your cart.")}`
                })
            }).catch(error => {
                this.$store.commit('setAlert', {
                    'color': 'danger', 
                    'message': this.$t(error.response.data.message)
                })
            })
        },
        initTinySliderUpSell() {
            tns({
                container: '.z-tiny-slider-upsell-section',
                items: 2,
                controlsText: [
                    `<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-arrow-bar-left text-primary opacity-50" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M12.5 15a.5.5 0 0 1-.5-.5v-13a.5.5 0 0 1 1 0v13a.5.5 0 0 1-.5.5zM10 8a.5.5 0 0 1-.5.5H3.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L3.707 7.5H9.5a.5.5 0 0 1 .5.5z"/>
                    </svg>`, 
                    `<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-arrow-bar-right text-primary opacity-50" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M6 8a.5.5 0 0 0 .5.5h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L12.293 7.5H6.5A.5.5 0 0 0 6 8zm-2.5 7a.5.5 0 0 1-.5-.5v-13a.5.5 0 0 1 1 0v13a.5.5 0 0 1-.5.5z"/>
                    </svg>`
                ],
                responsive: {
                    576: {
                        items: 3
                    },
                    992: {
                        items: 4
                    },
                    1200: {
                        items: 6
                    }
                }
            })
            this.loadingTinySliderUpSell = false
        },
        pickItemRestock(v) {
            this.picked = v
            this.showModal = true
        },
        updateModalStatus(v) {
            this.showModal = v
        }
    },
    computed: {
        ...mapGetters(['translation', 'productPrice']),
        ...mapState({
            storeConfig: state => state.setting.storeConfig,
            productDetails: state => state.product.productDetails,
        })
    },
    watch: {
        loaded(v) {

            if(!v)  return false
            
            let ids = []
            if(this.productDetails.meta && this.productDetails.meta.upsells) {
                ids = this.productDetails.meta.upsells
            } else if(this.items && this.items.length > 0) {
                this.items.map(i => ids.push(i.id))
            }
            if(ids.length > 0) {
                axios.get(`/api/v1/storefront/product/ids/${ids.join('_')}`)
                        .then(res => this.upsells = res.data.products)
                        .finally(() => this.loadedUpsell = true)
            }
        }
    }
}
</script>