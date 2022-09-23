<template>
    <div class="h5 text-center my-5 d-flex justify-content-center">
        <div class="alert alert-warning">
            {{$t('You have been logged out. It is now safe to leave the computer.')}}
        </div>
    </div>
    <section class="container">
        <div class="row mt-lg-5 mt-3">
            <template v-if="loadedWidgets">
                <template v-if="productWidget.new.length > 0">
                    <img @load="initTinySliderNew" src="/storage/pixel.gif" alt="js" class="d-none">
                    <div class="col-12 h3 fw-light pb-5 mb-0"><span class="fw-bold text-dark">{{ $t("New Arrival") }}</span> {{ $t('products') }}</div>
                    <div :class="`col-12 position-relative ${loadingTinySliderNew ? 'd-none' : ''}`">
                        <div class="z-tiny-slider-new-section">
                            <product-widget :products="productWidget.new" @pickItemRestock="pickItemRestock"></product-widget>
                        </div>
                    </div>
                    <template v-if="loadingTinySliderNew">
                        <product-widget-loading></product-widget-loading>
                    </template>
                </template>
            </template>
            <template v-else>
                <div class="col-12 h3 fw-light pb-5 mb-0">
                    <div class="py-4 rounded col-4 bg-gray-200"></div>
                </div>
                <product-widget-loading></product-widget-loading>
            </template>
        </div>
    </section>
    <hr>
    <section class="container">
        <div class="row mt-lg-5 mt-3">
            <template v-if="loadedWidgets">
                <template v-if="productWidget.featured.length > 0">
                    <img @load="initTinySliderFeatured" src="/storage/pixel.gif" alt="js" class="d-none">
                    <div class="col-12 h3 fw-light pb-5 mb-0"><span class="fw-bold text-dark">{{ $t("Best Selling") }}</span> {{ $t('products') }}</div>
                    <div :class="`col-12 position-relative ${loadingTinySliderFeatured ? 'd-none' : ''}`">
                        <div class="z-tiny-slider-featured-section">
                            <product-widget :products="productWidget.featured" @pickItemRestock="pickItemRestock"></product-widget>
                        </div>
                    </div>
                    <template v-if="loadingTinySliderFeatured">
                        <product-widget-loading></product-widget-loading>
                    </template>
                </template>
            </template>
            <template v-else>
                <div class="col-12 h3 fw-light pb-5 mb-0">
                    <div class="py-4 rounded col-4 bg-gray-200"></div>
                </div>
                <product-widget-loading></product-widget-loading>
            </template>
        </div>
    </section>
    <hr>
    <section class="container">
        <div class="row mt-lg-5 mt-3">
            <template v-if="loadedWidgets">
                <template v-if="productWidget.sale.length > 0">
                    <img @load="initTinySliderSale" src="/storage/pixel.gif" alt="js" class="d-none">
                    <div class="col-12 h3 fw-light pb-5 mb-0"><span class="fw-bold text-dark">{{ $t("Sale") }}</span> {{ $t('products') }}</div>
                    <div :class="`col-12 position-relative ${loadingTinySliderSale ? 'd-none' : ''}`">
                        <div class="z-tiny-slider-sale-section">
                            <product-widget :products="productWidget.sale" @pickItemRestock="pickItemRestock"></product-widget>
                        </div>
                    </div>
                    <template v-if="loadingTinySliderSale">
                        <product-widget-loading></product-widget-loading>
                    </template>
                </template>
            </template>
            <template v-else>
                <div class="col-12 h3 fw-light pb-5 mb-0">
                    <div class="py-4 rounded col-4 bg-gray-200"></div>
                </div>
                <product-widget-loading></product-widget-loading>
            </template>
        </div>
    </section>
    
    <product-restock-modal :product-id="picked.id" :product-name="picked.name" :show-modal="showModal" @updateModalStatus="updateModalStatus"></product-restock-modal>
</template>

<script>
import ProductWidget from '@theme/storefront/templates/product/ProductWidget'
import ProductWidgetLoading from '@theme/storefront/templates/product/ProductWidgetLoading'
import ProductRestockModal from '@theme/storefront/templates/product/Restock'
import { mapState, mapGetters } from 'vuex'
export default {
    data: () => ({
        loadedWidgets: false,
        loadingTinySliderNew: true,
        loadingTinySliderFeatured: true,
        loadingTinySliderSale: true,
        cartQty: {},
        picked: {
            id: undefined,
            name: undefined
        },
        showModal: false
    }),
    components: { ProductWidget, ProductWidgetLoading, ProductRestockModal },
    created() {
        this.$store.dispatch('productWidget').then(() => {
            this.loadedWidgets = true
            Object.keys(this.productWidget).forEach(k => this.productWidget[k].map(item => this.cartQty[item.id] = 1))
        })
    },
    mounted() {
        this.$store.dispatch('logout')
    },
    methods: {
        addToCart(item, qty) {
            const productName = this.translation(item, 'name', this.$i18n.locale)
            this.$store.dispatch('addProduct2Cart', { id: item.id, cart_quantity: qty }).then(() => {
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
        initTinySliderNew() {
            tns({
                container: '.z-tiny-slider-new-section',
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
            this.loadingTinySliderNew = false
        },
        initTinySliderFeatured() {
            tns({
                container: '.z-tiny-slider-featured-section',
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
            this.loadingTinySliderFeatured = false
        },
        initTinySliderSale() {
            tns({
                container: '.z-tiny-slider-sale-section',
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
            this.loadingTinySliderSale = false
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
        ...mapGetters(['productPrice', 'translation']),
        ...mapState({
            storeConfig: state => state.setting.storeConfig,
            productWidget: state => state.product.productWidget,
        }),
    }
}
</script>