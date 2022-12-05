<template>
    <div class="container">
        <div class="row">
            <div class="z-index-sidebar col-lg-3 order-lg-0 order-1">
                <block-element menu-key="home-top" :block-loading="3"></block-element>
                <banners-left></banners-left>
            </div>
            <div class="col-lg-9 order-lg-1 order-0">

                <section v-if="loadedWidgets" class="container">
                    <div v-if="productWidget.new.length > 0" class="row">
                        <img @load="initTinySliderNew" src="/storage/pixel.gif" alt="js" class="d-none">
                        <div class="col-12 h3 fw-light pb-5 mb-0 text-uppercase text-dark"><span class="fw-bold text-info">{{ $t("New Arrival") }}</span> {{ $t('products') }}</div>
                        <div :class="`col-12 position-relative ${loadingTinySliderNew ? 'd-none' : ''}`">
                            <div class="z-tiny-slider-new-section">
                                <product-widget :products="productWidget.new" @pickItemRestock="pickItemRestock"></product-widget>
                            </div>
                        </div>
                        <template v-if="loadingTinySliderNew">
                            <product-widget-loading></product-widget-loading>
                        </template>
                    </div>
                </section>
                <section v-else class="container">
                    <div class="row mt-lg-5 mt-3">
                        <div class="col-12 h3 fw-light pb-5 mb-0">
                            <div class="py-4 rounded col-4 bg-gray-200"></div>
                        </div>
                        <product-widget-loading></product-widget-loading>
                    </div>
                </section>

                <banners-top></banners-top>

                <section v-if="loadedWidgets" class="container">
                    <div v-if="productWidget.featured.length > 0" class="row mt-lg-5 mt-3">
                        <img @load="initTinySliderFeatured" src="/storage/pixel.gif" alt="js" class="d-none">
                        <div class="col-12 h3 fw-light pb-5 mb-0 text-uppercase text-dark"><span class="fw-bold text-info">{{ $t("Best Selling") }}</span> {{ $t('products') }}</div>
                        <div :class="`col-12 position-relative ${loadingTinySliderFeatured ? 'd-none' : ''}`">
                            <div class="z-tiny-slider-featured-section">
                                <product-widget :products="productWidget.featured" @pickItemRestock="pickItemRestock"></product-widget>
                            </div>
                        </div>
                        <template v-if="loadingTinySliderFeatured">
                            <product-widget-loading></product-widget-loading>
                        </template>
                    </div>
                </section>
                <section v-else class="container">
                    <div class="row mt-lg-5 mt-3">
                        <div class="col-12 h3 fw-light pb-5 mb-0">
                            <div class="py-4 rounded col-4 bg-gray-200"></div>
                        </div>
                        <product-widget-loading></product-widget-loading>
                    </div>
                </section>

                <block-element menu-key="home-bottom" img-type="original" :block-loading="3"></block-element>

                <section v-if="loadedWidgets" class="container sale">
                    <div v-if="productWidget.sale.length > 0" class="row mt-lg-5 mt-3">
                        <img @load="initTinySliderSale" src="/storage/pixel.gif" alt="js" class="d-none">
                        <div class="col-12 h3 fw-light pb-5 mb-0 text-uppercase text-dark"><span class="fw-bold text-info">{{ $t("Sale") }}</span> {{ $t('products') }}</div>
                        <div :class="`col-12 position-relative ${loadingTinySliderSale ? 'd-none' : ''}`">
                            <div class="z-tiny-slider-sale-section">
                                <product-widget :products="productWidget.sale" @pickItemRestock="pickItemRestock"></product-widget>
                            </div>
                        </div>
                        <template v-if="loadingTinySliderSale">
                            <product-widget-loading></product-widget-loading>
                        </template>
                    </div>
                </section>
                <section v-else class="container">
                    <div class="row mt-lg-5 mt-3">
                        <div class="col-12 h3 fw-light pb-5 mb-0">
                            <div class="py-4 rounded col-4 bg-gray-200"></div>
                        </div>
                        <product-widget-loading></product-widget-loading>
                    </div>
                </section>

            </div>
        </div>
    </div>
    <product-restock-modal :product-id="picked.id" :product-name="picked.name" :show-modal="showModal" @updateModalStatus="updateModalStatus"></product-restock-modal>

</template>


<script>
import BannerSlideshow from '@theme/storefront/templates/banner/Slideshow'
import BannersTop from '@theme/storefront/templates/banner/Top'
import BannersLeft from '@theme/storefront/templates/banner/Left'
import BlockElement from '@theme/storefront/templates/menu/BlockElement'
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
        picked: {
            id: undefined,
            name: undefined
        },
        showModal: false
    }),
    components: { BannerSlideshow, BannersLeft, BannersTop, BlockElement, ProductWidget, ProductWidgetLoading, ProductRestockModal },
    created() {
        if(this.posts.length === 0) {
            this.$store.dispatch('latestPosts')
        }
        this.$store.dispatch('productWidget').then(() => this.loadedWidgets = true)
    },
    methods: {
        initTinySliderNew() {
            tns({
                container: '.z-tiny-slider-new-section',
                items: 2,
                controlsText: [
                    `<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-arrow-left-circle text-light" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-4.5-.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5z"/>
                    </svg>`, 
                    `<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-arrow-right-circle text-light" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H4.5z"/>
                    </svg>`
                ],
                responsive: {
                    576: {
                        items: 3
                    },
                    1200: {
                        items: 5
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
                    `<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-arrow-left-circle text-light" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-4.5-.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5z"/>
                    </svg>`, 
                    `<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-arrow-right-circle text-light" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H4.5z"/>
                    </svg>`
                ],
                responsive: {
                    576: {
                        items: 3
                    },
                    1200: {
                        items: 5
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
                    `<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-arrow-left-circle text-light" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-4.5-.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5z"/>
                    </svg>`, 
                    `<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-arrow-right-circle text-light" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H4.5z"/>
                    </svg>`
                ],
                responsive: {
                    576: {
                        items: 3
                    },
                    1200: {
                        items: 5
                    }
                }
            })
            this.loadingTinySliderSale = false
        },
        dateFormat(date) {
            const d = new Date(date)
            const monthNames = ["January", "February", "March", "April", "May", "June",
                "July", "August", "September", "October", "November", "December"
            ];
            return monthNames[d.getMonth()] + " " + ("0" + d.getDate()).slice(-2) + " " + d.getFullYear()
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
            productWidget: state => state.product.productWidget,
            storeConfig: state => state.setting.storeConfig,
            posts: state => state.blogpost.posts
        }),
        latestPosts() {
            return _.chunk(this.posts.sort(() => 0.5 - Math.random()) , 5)[0] || []
        }
    }
}
</script>