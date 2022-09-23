<template>

    <!-- Slideshow -->
    <div class="row">
        <banner-slideshow></banner-slideshow>
    </div>

    <block-element menu-key="home-top" :block-loading="1"></block-element>

    <hr class="my-5 bg-gray-300">
    
    <section v-if="loadedWidgets" class="container">
        <div v-if="productWidget.new.length > 0" class="row mt-lg-5 mt-3">
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
            <div class="col-12 h3 fw-light pb-5 mb-0"><span class="fw-bold text-dark">{{ $t("Best Selling") }}</span> {{ $t('products') }}</div>
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
            <div class="col-12 h3 fw-light pb-5 mb-0"><span class="fw-bold text-dark">{{ $t("Sale") }}</span> {{ $t('products') }}</div>
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

    <section class="container">
        <div class="row mt-lg-5 mt-3">
            <div class="col-12 h3 fw-light pb-5 mb-0"><span class="fw-bold text-dark">{{ $t("Our") }}</span> {{ $t('blog') }}</div>
        </div>
        <div class="row g-4">
            <div v-for="post in latestPosts" :key="post.id" class="post-index col-lg col-lg-4 col-md-6 col-12">
                <div class="post-index__bg w-100 bg-gray-200" :style="`height:180px; background: #e9ecef url('/storage/${storeConfig.medium_image_size}/${post.image}') center center no-repeat;background-size: cover;`"></div>
                <h4 class="h6 fw-bold my-3">
                    <router-link class="text-dark text-decoration-none" :to="`/article/${translation(post, 'slug', $i18n.locale)}`">
                        {{ translation(post, 'title', $i18n.locale) }}
                    </router-link>
                </h4>
                <div class="post-index__date text-success opacity-75">{{ dateFormat(post.created_at) }}</div>
            </div>
        </div>
    </section>
    <product-restock-modal :product-id="picked.id" :product-name="picked.name" :show-modal="showModal" @updateModalStatus="updateModalStatus"></product-restock-modal>

</template>


<script>
import BannerSlideshow from '@theme/storefront/templates/banner/Slideshow'
import BannersTop from '@theme/storefront/templates/banner/Top'
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
    components: { BannerSlideshow, BannersTop, BlockElement, ProductWidget, ProductWidgetLoading, ProductRestockModal },
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