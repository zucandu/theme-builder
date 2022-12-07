<template>
    <div class="zuc-listing-container row g-3 justify-content-center">
        <div v-if="manufacturerTranslation" class="col-12 text-center my-4">
            <div class="small text-gray-500 pb-3">
                <router-link to="/">{{ $t('Home') }}</router-link>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right-short mx-2" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M4 8a.5.5 0 0 1 .5-.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5A.5.5 0 0 1 4 8z"/>
                </svg>
                {{ manufacturerTranslation.name }}
            </div>
            <h1>{{ manufacturerTranslation.name }}</h1>
            <div class="col-lg-8 col-12 text-gray-500">{{ manufacturerTranslation.description }}</div>
        </div>
        <div v-else class="col-12 text-center my-4">
            <div class="pb-3">
                <div class="rounded bg-secondary opacity-25 px-4 py-2 d-inline-block"></div>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right-short mx-2" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M4 8a.5.5 0 0 1 .5-.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5A.5.5 0 0 1 4 8z"/>
                </svg>
                <div class="rounded bg-secondary opacity-25 px-4 py-2 d-inline-block"></div>
            </div>
            <div class="py-3 px-5 rounded bg-secondary opacity-25 d-inline-block col-lg-2 col-6"></div>
        </div>
        <div class="zuc-listing-sidebar col-lg-3">
            <section class="zuc-listing-sidebar__filter">
                <listing-filters :filters="filters" :reset-filter="resetFilters" @updateContent="updateContent"></listing-filters>
            </section>
        </div>
        <div class="zuc-listing-products col-lg-8 offset-lg-1">
            <section class="zuc-listing-products__sort d-sm-flex justify-content-between mb-5">
                <div class="showing">{{ $t('Showing') }} {{ paginationShowing.from }} {{ $t('to') }} {{ paginationShowing.to }} {{ $t('of') }} {{ paginationShowing.total }} {{ $t('products') }}</div>
                <div class="btn-group me-4">
                    <a href="#" class="dropdown-toggle text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false">{{ $t(sortByItems.find(s => s.field === sortBy).name) }}</a>
                    <ul class="dropdown-menu cursor-pointer">
                        <li v-for="(option, index) in sortByItems" :key="index"><a class="dropdown-item" @click="sortBy = option.field">{{ $t(option.name) }}</a></li>
                    </ul>
                </div>
            </section>
            <section v-if="!loading" class="zuc-listing-products__row row g-3">
                <img @load="getMaxHeightFromProductName" src="/storage/pixel.gif" alt="js" class="d-none">
                <div v-for="(item, index) in products" :key="index" class="col-lg-4 col-6">
                    <div class="product-widget__inner mb-3">
                        <div class="inner__img mb-3">
                            <router-link :to="`/${translation(item, 'slug', $i18n.locale)}`" class="text-decoration-none">
                                <img :src="`/storage/${storeConfig.medium_image_size}/${item.images[0].src}`" :width="storeConfig.medium_image_size" :height="storeConfig.medium_image_size" :alt="translation(item, 'name', $i18n.locale)" :id="`img-listing-${item.id}`" class="img-fluid">
                            </router-link>
                            <div class="inner__additional-images d-flex justify-content-center align-items-center py-2">
                                <template v-if="item.images.length > 1">
                                    <template v-for="(ai, index) in item.images" :key="ai">
                                        <img v-if="index < 3" @mouseover="changeImgSrc(item.id, ai.src)" :src="`/storage/${storeConfig.small_image_size}/${ai.src}`" width="30" height="30" :alt="translation(item, 'name', $i18n.locale)" class="img-loading img-fluid cursor-pointer mx-1">
                                    </template>
                                </template>
                                <router-link v-if="item.images.length > 3" :to="`/${translation(item, 'slug', $i18n.locale)}`" class="square-4x small text-gray-500 text-decoration-none">+{{ item.images.length-3 }} {{ $t('options') }}</router-link>
                            </div>
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
                                <button class="btn btn-sm btn-link d-flex align-items-center text-dark p-0 text-decoration-none" @click.stop="addToCart(item, cartQty[item.id])" data-bs-toggle="modal" data-bs-target="#z-cart-modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cart-plus me-2" viewBox="0 0 16 16">
                                        <path d="M9 5.5a.5.5 0 0 0-1 0V7H6.5a.5.5 0 0 0 0 1H8v1.5a.5.5 0 0 0 1 0V8h1.5a.5.5 0 0 0 0-1H9V5.5z"/>
                                        <path d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H2.89l-.405-1.621A.5.5 0 0 0 2 1H.5zm3.915 10L3.102 4h10.796l-1.313 7h-8.17zM6 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm7 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                                    </svg>
                                    {{ $t('Add to cart') }}
                                </button>
                            </div>
                            <div v-else class="inner__cart d-flex w-100 bottom-0 mt-3">
                                <router-link class="btn btn-sm btn-link d-flex align-items-center text-dark p-0 text-decoration-none" :to="`/${translation(item, 'slug', $i18n.locale)}`">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-in-right me-2 opacity-50" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M6 3.5a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 0-1 0v2A1.5 1.5 0 0 0 6.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-8A1.5 1.5 0 0 0 5 3.5v2a.5.5 0 0 0 1 0v-2z"/>
                                        <path fill-rule="evenodd" d="M11.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H1.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z"/>
                                    </svg>
                                    {{ $t('Choose options') }}
                                </router-link>
                            </div>
                        </template>
                        <template v-else>
                            <div class="inner__cart d-flex w-100 bottom-0 mt-3">
                                <button @click.prevent="showModal = true, picked = { id: item.id, name: translation(item, 'name', $i18n.locale) }" class="btn btn-sm btn-link d-flex align-items-center text-dark p-0 text-decoration-none" type="button">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-envelope me-2" viewBox="0 0 16 16">
                                        <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2Zm13 2.383-4.708 2.825L15 11.105V5.383Zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741ZM1 11.105l4.708-2.897L1 5.383v5.722Z"/>
                                    </svg>
                                    {{ $t('Notify') }}
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </section>
            <section class="zuc-listing-products__pagination text-end" v-if="paginationLinks.length > 0">
                <router-link :to="{ path: `/category/${$route.params.slug}`, query: Object.assign({}, urlGetAllParams(['page']), { page: urlParamValueFromName(link.url, 'page') })}" v-for="(link, index) in paginationLinks" :key="index" :class="`btn btn-outline-dark mx-1${(!link.url ? ' disabled' : '')}${(link.active === true ? ' btn-primary text-white' : '')}`" v-html="link.label"></router-link>
            </section>
            <section v-if="loading" class="row g-3 mt-lg-5 mt-3">
                <div v-for="i in itemPerPage" :key="i" class="col-lg-4 col-6">
                    <div class="card card-body border-0 product-widget">
                        <div class="inner__img bg-gray-200 mb-3 rounded w-100 py-5"></div>
                        <div class="inner__title bg-gray-200 mb-1 rounded w-75 py-2"></div>
                        <div class="inner__rating bg-gray-200 mb-3 rounded w-50 py-2"></div>
                        <div class="inner__price bg-gray-200 rounded w-25 py-2"></div>
                    </div>
                </div>
            </section>
        </div>
    </div>
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
        picked: { id: undefined, name: undefined }
    }),
    components: { ProductDisplayPrice, DisplayRating, ListingFilters, Overlay, ProductRestockModal },
    created() {

        // Set the selected filter
        const sortByParam = this.urlParamValueFromName(window.location.href, 'sort')
        if(sortByParam && sortByParam !== this.sortBy) {
            this.sortBy = sortByParam
        }

        // First load
        this.queryProductListing(this.$route.params.slug, this.urlGetAllParams())

    },
    beforeRouteUpdate (to, from, next) {

        if(to.params.slug !== this.$route.params.slug) {
            this.resetFilters = true
        }

        this.queryProductListing(to.params.slug, {  ...to.query, sort: this.sortBy })

        next()

    },
    unmounted() {
        this.$store.commit('resetListing')
    },
    methods: {
        queryProductListing(slug, params) {

            this.loading = true

            this.$store.dispatch('productListingFromManufacturer', {slug: slug, objParams: params}).catch(error => {
                this.$store.commit('setAlert', {
                    'color': 'danger', 
                    'message': this.$t(error.response.data.message)
                })
            }).finally(() => {

                // Redirect to home page if listing is empty
                if(this.products && this.products.length === 0) {
                    this.$store.commit('setAlert', {
                        'color': 'danger', 
                        'message': this.$t(`there are no products in this manufacturer: ${slug}`)
                    })
                    this.$router.push(`/`)
                }

                // Set cartQty
                this.products.map(p => this.cartQty[p.id] = 1)

                // Reset filter when slug changed
                if(this.resetFilters === true) {
                    this.selectedFilters = []
                    this.resetFilters = false
                }

                this.loading = false

            })

        },
        updateContent(v) {
            this.selectedFilters = v
        },
        addToCart(item, qty = 1) {
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
        },
        getMaxHeightFromProductName() {
            const elImgs = document.querySelectorAll('.inner__img')
            const elImgMaxHeight = Math.max.apply(null, [...elImgs].map(e => +e.offsetHeight))
            elImgs.forEach(e => e.style.minHeight = `${elImgMaxHeight}px`)

            const els = document.querySelectorAll('.inner__title')
            const maxHeight = Math.max.apply(null, [...els].map(e => +e.offsetHeight))
            els.forEach(e => e.style.minHeight = `${maxHeight}px`)
        }
    },
    computed: {
        ...mapGetters(['translation', 'transObj', 'urlParamValueFromName', 'productPrice', 'urlGetAllParams',
                        'displayPriceRange']),
        ...mapState({
            products: state => state.listing.products,
            paginationLinks: state => state.listing.paginationLinks,
            paginationShowing: state => state.listing.paginationShowing,
            filters: state => state.listing.filters,
            manufacturerDetails: state => state.listing.object,
            storeConfig: state => state.setting.storeConfig
        }),
        manufacturerTranslation() {
            return !_.isEmpty(this.manufacturerDetails) ? this.transObj(this.manufacturerDetails, this.$i18n.locale) : undefined
        },
        itemPerPage() {
            return +this.storeConfig.number_of_query_limit > 0 ? +this.storeConfig.number_of_query_limit : 20
        },
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
        manufacturerTranslation(v) {
            if(v) {
                document.title = v.meta_title
                document.querySelector('meta[name="description"]').setAttribute("content", v.meta_description)
            }
        }
    }
}
</script>