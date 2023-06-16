<template>
    <div class="zuc-listing-container row g-3 justify-content-center">

        <div class="col-12">
            <!-- Hook: search result top -->
            <template v-for="(component, index) in $pluginStorefrontHooks['search_result_top']" :key="index">
                <component :is="component" :product="item"></component>
            </template>
        </div>

        <div :class="`zuc-listing-sidebar col-lg-2 ${!noProduct ? `d-lg-block` : `d-none`}`">
            <section class="zuc-listing-sidebar__filter">
                <listing-filters :filters="filters" :reset-filter="resetFilters" @updateContent="updateContent"></listing-filters>
            </section>
        </div>
        <div :class="`zuc-listing-products ${!noProduct ? `col-lg-9 offset-lg-1` : `col-lg-12`}`">
            <section v-if="!noProduct" class="zuc-listing-products__sort d-sm-flex justify-content-between mb-5">
                <div class="showing">{{ $t('Showing') }} {{ paginationShowing.from }} {{ $t('to') }} {{ paginationShowing.to }} {{ $t('of') }} {{ paginationShowing.total }} {{ $t('products') }}</div>
                <div class="btn-group me-4">
                    <a href="#" class="dropdown-toggle text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false">{{ $t(sortByItems.find(s => s.field === sortBy).name) }}</a>
                    <ul class="dropdown-menu cursor-pointer">
                        <li v-for="(option, index) in sortByItems" :key="index"><a class="dropdown-item" @click="sortBy = option.field">{{ $t(option.name) }}</a></li>
                    </ul>
                </div>
            </section>
            <section v-if="!loading" class="zuc-listing-products__row row g-3">
                <template v-if="!noProduct">
                    <div v-for="item in products" :key="item.id" class="col-lg-3 col-md-4 col-6">
                        <div class="product-widget__inner mb-3">
                            <div class="inner__img mb-3">
                                <router-link :to="`/${translation(item, 'slug', $i18n.locale)}`" class="text-decoration-none">
                                    <img :src="`/storage/${storeConfig.medium_image_size}/${item.images[0].src}`" :width="storeConfig.medium_image_size" :height="storeConfig.medium_image_size" :alt="translation(item, 'name', $i18n.locale)" :id="`img-listing-${item.id}`" class="img-fluid">
                                </router-link>
                            </div>
                            <div class="inner__additional-images d-flex justify-content-center align-items-center">
                                <template v-if="item.images.length > 1">
                                    <template v-for="(ai, index) in item.images" :key="ai">
                                        <img v-if="index < 3" @mouseover="changeImgSrc(item.id, ai.src)" :src="`/storage/${storeConfig.small_image_size}/${ai.src}`" width="30" height="30" :alt="translation(item, 'name', $i18n.locale)" class="img-loading img-fluid cursor-pointer mx-1">
                                    </template>
                                </template>
                                <router-link v-if="item.images.length > 3" :to="`/${translation(item, 'slug', $i18n.locale)}`" class="small">+{{ item.images.length-3 }} {{ $t('options') }}</router-link>
                            </div>
                            <h3 class="inner__title h6 fw-light mb-0">
                                <router-link :to="`/${translation(item, 'slug', $i18n.locale)}`" class="text-decoration-none text-dark">
                                    {{ translation(item, 'name', $i18n.locale) }}
                                </router-link>
                            </h3>
                            <div v-if="item.total_reviews > 0" class="d-flex justify-content-between">
                                <div class="inner__rating">
                                    <display-rating :rating="item.rating" :total="item.total_reviews"></display-rating>
                                </div>
                            </div>
                            <div class="inner__price fw-bold mt-3">
                                <product-display-price :product-price="productPrice(item, 1)"></product-display-price>
                            </div>
                            <template v-if="item.quantity > 0">
                                <div v-if="+item.has_attributes === 0" class="inner__cart d-flex w-100 bottom-0 mt-3">
                                    <input type="number" v-model="cartQty[item.id]" class="form-control form-control-sm border-end-0">
                                    <button class="btn btn-sm btn-success rounded-0 border-start-0 text-white px-3" @click.stop="addToCart(item, cartQty[item.id])" data-bs-toggle="modal" data-bs-target="#z-cart-modal">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cart-plus" viewBox="0 0 16 16">
                                            <path d="M9 5.5a.5.5 0 0 0-1 0V7H6.5a.5.5 0 0 0 0 1H8v1.5a.5.5 0 0 0 1 0V8h1.5a.5.5 0 0 0 0-1H9V5.5z"/>
                                            <path d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H2.89l-.405-1.621A.5.5 0 0 0 2 1H.5zm3.915 10L3.102 4h10.796l-1.313 7h-8.17zM6 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm7 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                                        </svg>
                                    </button>
                                </div>
                                <div v-else class="inner__cart d-flex w-100 bottom-0 mt-3">
                                    <router-link class="btn btn-sm btn-success text-white w-100 d-block" :to="`/${translation(item, 'slug', $i18n.locale)}`">{{ $t('Choose options') }}</router-link>
                                </div>
                            </template>
                            <template v-else>
                                <div class="inner__cart d-flex w-100 bottom-0 mt-3">
                                    <button @click.prevent="showModal = true, picked = { id: item.id, name: translation(item, 'name', $i18n.locale) }" class="btn btn-info btn-sm text-white w-100" type="button">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-send-plus" viewBox="0 0 16 16">
                                            <path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855a.75.75 0 0 0-.124 1.329l4.995 3.178 1.531 2.406a.5.5 0 0 0 .844-.536L6.637 10.07l7.494-7.494-1.895 4.738a.5.5 0 1 0 .928.372l2.8-7Zm-2.54 1.183L5.93 9.363 1.591 6.602l11.833-4.733Z"/>
                                            <path d="M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Zm-3.5-2a.5.5 0 0 0-.5.5v1h-1a.5.5 0 0 0 0 1h1v1a.5.5 0 0 0 1 0v-1h1a.5.5 0 0 0 0-1h-1v-1a.5.5 0 0 0-.5-.5Z"/>
                                        </svg>
                                        {{ $t('Notify') }}
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
                <template v-else>
                    <div class="col-12 text-center">
                        {{ $t('There is no product in this keyword.') }}
                    </div>
                </template>
            </section>
            <section class="zuc-listing-products__pagination text-end" v-if="paginationLinks.length > 0 && !noProduct">
                <router-link :to="{ path: `/category/${$route.params.slug}`, query: Object.assign({}, urlGetAllParams(['page']), { page: urlParamValueFromName(link.url, 'page') })}" v-for="(link, index) in paginationLinks" :key="index" :class="`btn btn-outline-dark mx-1${(!link.url ? ' disabled' : '')}${(link.active === true ? ' btn-primary text-white' : '')}`"><span v-html="link.label"></span></router-link>
            </section>
            <section v-if="loading" class="row g-3 mt-lg-5 mt-3">
                <div v-for="i in 20" :key="i" class="col-lg-3 col-md-4 col-6">
                    <div class="card card-body border-0 product-widget">
                        <div class="inner__img bg-gray-200 mb-3 rounded w-100 py-5"></div>
                        <div class="inner__title bg-gray-200 mb-1 rounded w-75 py-2"></div>
                        <div class="inner__rating bg-gray-200 mb-3 rounded w-50 py-2"></div>
                        <div class="inner__price bg-gray-200 rounded w-25 py-2"></div>
                    </div>
                </div>
            </section>
            <hr class="my-4 bg-info">

            <!-- Display search info -->
            <listing-object-info :object="{name: $route.params.keyword}"></listing-object-info>

        </div>
    </div>

    <!-- Hook: search result bottom -->
    <template v-for="(component, index) in $pluginStorefrontHooks['search_result_bottom']" :key="index">
        <component :is="component"></component>
    </template>

    <overlay v-if="loading"></overlay>
    <product-restock-modal :product-id="picked.id" :product-name="picked.name" :show-modal="showModal" @updateModalStatus="updateModalStatus"></product-restock-modal>
</template>


<script>
import Overlay from '@theme/storefront/templates/element/Overlay'
import ListingFilters from '@theme/storefront/templates/listing/ListingFilters'
import DisplayRating from '@theme/storefront/templates/product/DisplayRating'
import ProductDisplayPrice from '@theme/storefront/templates/product/DisplayPrice'
import ProductRestockModal from '@theme/storefront/templates/product/Restock'
import { mapState, mapGetters } from 'vuex'
export default {
    data: () => ({
        sortByItems: [
            { field: 'popular', name: 'popular' },
            { field: 'price_asc', name: 'Price: Lowest First' },
            { field: 'price_desc', name: 'Price: Highest First' },
            { field: 'date_asc', name: 'Date Added: Old to New' },
            { field: 'date_desc', name: 'Date Added: New to Old' },
            { field: 'sku', name: 'Model Code: A to Z' },
            { field: 'sorting', name: 'Default Sorting' },
        ],
        selectedFilters: [],
        sortBy: `sorting`,
        resetFilters: false,
        loading: false,
        cartQty: {},
        showModal: false,
        picked: { id: undefined, name: undefined },
        noProduct: false
    }),
    components: { ProductDisplayPrice, DisplayRating, ListingFilters, Overlay, ProductRestockModal },
    created() {

        // Set the selected filter
        this.sortBy = this.urlParamValueFromName(window.location.href, 'sort') || this.sortBy

        // First load
        this.queryProductListing(this.$route.params.keyword, this.urlGetAllParams())

        // Meta tags
        const metaTitle = this.$route.params.keyword.replace(/(^\w|\s\w)/g, m => m.toUpperCase()) + " " + this.$t(`Search Results`)
        document.title = metaTitle
        document.querySelector('meta[name="description"]').setAttribute("content", metaTitle)

    },
    beforeRouteUpdate (to, from, next) {

        if(to.params.keyword !== this.$route.params.keyword) {
            this.resetFilters = true
        }

        this.queryProductListing(to.params.keyword, {  ...to.query, sort: this.sortBy })

        next()

    },
    unmounted() {
        this.$store.commit('resetListing')
    },
    methods: {
        queryProductListing(keyword, params) {

            this.loading = true

            this.$store.dispatch('productListingFromSearchResult', {keyword: keyword, objParams: params}).catch(error => {
                this.$store.commit('setAlert', {
                    'color': 'danger', 
                    'message': this.$t(error.response.data.message)
                })
            }).finally(() => {
                
                // Set cartQty
                this.products.map(p => this.cartQty[p.id] = 1)
                this.noProduct = this.products && this.products.length === 0 ? true : false
                this.loading = false

                // Reset filter when slug changed
                if(this.resetFilters)   this.selectedFilters = [], this.resetFilters = false

            })

        },
        updateContent(v) {
            this.selectedFilters = v
        },
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
        updateModalStatus(v) {
            this.showModal = v
        },
        changeImgSrc(id, src) {
            const tmp = document.getElementById(`img-listing-${id}`).src
            document.getElementById(`img-listing-${id}`).src = tmp.substring(0, tmp.lastIndexOf("/")) + "/" + src
        }
    },
    computed: {
        ...mapGetters(['translation', 'transObj', 'urlParamValueFromName', 'productPrice', 'urlGetAllParams']),
        ...mapState({
            products: state => state.listing.products,
            paginationLinks: state => state.listing.paginationLinks,
            paginationShowing: state => state.listing.paginationShowing,
            filters: state => state.listing.filters,
            storeConfig: state => state.setting.storeConfig
        })
    },
    watch: {
        sortBy(newval, oldval) {
            if(newval !== oldval) {
                this.$router.replace({ query: { ...this.urlGetAllParams(), sort: newval } })
            }
        },
        selectedFilters(nv, ov) {
            if(Object.keys(nv).length !== Object.keys(ov).length) {
                this.$router.replace({ query: { ...this.urlGetAllParams(), flt: nv ? nv.join('|') : undefined } })
            }
        },
    }
}
</script>