<template>
    <section class="cart row">
        <div class="col-12 mt-5">
            <div v-if="items && items.length > 0">
                <p class="text-danger" v-if="foundItemOutOfStock !== false">
                    {{$t('Some items in your shopping cart currently do not have enough stock. Please make adjustments before continuing to checkout.')}}
                </p>

                <!-- Hook cart top -->
                <template v-for="(component, index) in $pluginStorefrontHooks['cart_top']" :key="index">
                    <component :is="component"></component>
                </template>
                
                <div class="tbl-cart">
                    <div class="row fw-bold d-none d-md-flex mb-3">
                        <div class="col-md-2">{{ $t('Image') }}</div>
                        <div class="col-md-4">{{ $t('Name') }}</div>
                        <div class="col-md-2">{{ $t('Quantity') }}</div>
                        <div class="col-md-2 text-end">{{ $t('Price') }}</div>
                        <div class="col-md-2 text-end">{{ $t('Total') }}</div>
                    </div>
                    <div v-for="item in items" :key="item.id" class="row mb-3 pb-3 border-bottom">
                        <div class="col-3 col-md-2">
                            <router-link :to="`/${translation(item, 'slug', $i18n.locale)}`">
                                <img :src="`/storage/${storeConfig.small_image_size}/${item.images[0].src}`" alt="" class="img-thumbnail">
                            </router-link>
                        </div>
                        <div class="col-9 col-md-4">
                            <router-link :to="`/${translation(item, 'slug', $i18n.locale)}`" class="text-dark text-decoration-none">{{ translation(item, 'name', $i18n.locale) }}</router-link>
                            <button class="btn btn-sm btn-link" @click.stop="this.$store.dispatch('removeProduct', item.id)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                    <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                    <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                                </svg>
                            </button>

                            <!-- Hook product title. -->
                            <template v-for="(component, index) in $pluginStorefrontHooks['cart_product_title']" :key="index">
                                <component :is="component" :product="item"></component>
                            </template>

                        </div>
                        <div class="offset-3 offset-md-0 col-3 col-md-2 pb-md-0 pb-3">
                            <input @input="setProductQtyInput(item, $event)" class="form-control" :value="item.qty" type="number">
                            <p class="small text-danger" v-if="item.qty > item.inventory">{{$t('Current inventory in stock:')}} {{item.inventory}}</p>
                        </div>
                        <div class="offset-3 offset-md-0 col-6 col-md-2 text-md-end">
                            <product-display-price :product-price="productPrice(item, 1)"></product-display-price>
                        </div>
                        <div class="offset-3 offset-md-0 col-6 col-md-2 text-md-end">
                            <product-display-price :product-price="productPrice(item, item.qty)"></product-display-price>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6">
                        <estimate-shipping-costs :subtotal="cartTotal"></estimate-shipping-costs>
                    </div>
                    <div class="col-lg-6 text-end">
                        <div class="text-end fw-bold h5 mb-3">
                            {{ $t('Subtotal') }}: <display-price :price="cartTotal"></display-price>
                        </div>
                        <div class="text-end">
                            <router-link class="btn btn-primary" to="/checkout">{{$t('Go to the checkout')}}</router-link>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else>
                <p class="display-1 text-center">{{$t('Your cart is empty!')}}</p>
                <hr class="bg-gray-200 opacity-50 my-5">
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
                <hr class="bg-gray-200 opacity-50 my-5">
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
                <hr class="bg-gray-200 opacity-50 my-5">
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
            </div>
        </div>

        <!-- Hook cart bottom -->
        <template v-for="(component, index) in $pluginStorefrontHooks['cart_bottom']" :key="index">
            <component :is="component"></component>
        </template>

    </section>

    <product-crosssell :loaded="loadedWidgets" :items="items"></product-crosssell>
    <product-upsell :loaded="loadedWidgets" :items="items"></product-upsell>
    <product-restock-modal :product-id="picked.id" :product-name="picked.name" :show-modal="showModal" @updateModalStatus="updateModalStatus"></product-restock-modal>

</template>

<script>
import DisplayPrice from '@theme/storefront/templates/currency/DisplayPrice'
import ProductDisplayPrice from '@theme/storefront/templates/product/DisplayPrice'
import EstimateShippingCosts from '@theme/storefront/templates/element/EstimateShippingCosts'
import ProductWidget from '@theme/storefront/templates/product/ProductWidget'
import ProductWidgetLoading from '@theme/storefront/templates/product/ProductWidgetLoading'
import ProductRestockModal from '@theme/storefront/templates/product/Restock'
import ProductCrosssell from '@theme/storefront/templates/product/Crosssell'
import ProductUpsell from '@theme/storefront/templates/product/Upsell'
import { mapGetters, mapState } from 'vuex'
export default {
    data: () => ({
        loadedWidgets: false,
        loadingTinySliderNew: true,
        loadingTinySliderFeatured: true,
        loadingTinySliderSale: true,
        picked: {
            id: undefined,
            name: undefined
        },
        showModal: false
    }),
    components: {
        DisplayPrice, ProductDisplayPrice, EstimateShippingCosts, ProductWidget, 
        ProductWidgetLoading, ProductRestockModal, ProductCrosssell, ProductUpsell
    },
    created() {
        this.$store.dispatch('productWidget').then(() => this.loadedWidgets = true)
    },
    methods: {
        setProductQtyInput(product, event) {
            this.$store.dispatch('updateProductQtyInCart', { ...product, ...{ id: product.id, cart_quantity: event.target.value }}).then(() => {
                // 
            }).catch(error => {
                this.$store.commit('setAlert', {
                    'color': 'danger', 
                    'message': this.$t(error.response.data.message)
                })
            })
        },
        removeProduct(product) {
            this.$store.dispatch('removeProduct', product.id);
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
        ...mapGetters(['cartTotal', 'foundItemOutOfStock', 'translation', 'productImageSrc', 'productPrice', 'cartAllItems']),
        ...mapState({
            items: state => state.cart.items,
            storeConfig: state => state.setting.storeConfig,
            productWidget: state => state.product.productWidget,
        })
    }
}
</script>