<template>
    <section class="section-product row g-3 mb-5">
        <div class="col-md-6 h-100">
            <template v-if="loadedProduct">
                <display-carousel :images="productDetails.images" :current-image="actualProductDetails.images[0]" :product-name="productTranslation.name"></display-carousel>
            </template>
            <template v-else>
                <loading-carousel :small-size="storeConfig.small_image_size" :large-size="storeConfig.large_image_size"></loading-carousel>
            </template>
        </div>
        <div class="col-md-5 offset-md-1">
            <div v-if="loadedProduct" class="section-product__details">
                <form @submit.prevent="add2Cart">
                    <h1 class="fw-light h2">{{ productTranslation.name }}</h1>
                    <display-rating :rating="productDetails.rating" :total="productDetails.total_reviews" :url="`/product-review-write/${productTranslation.slug}`" :move-to-element="`#product-reviews-section`"></display-rating>
                    <div class="mt-3">
                        <template v-if="manufacturerTranslation">
                            <router-link class="d-inline-block text-decoration-none" :to="`/manufacturer/${manufacturerTranslation.slug}`">{{ manufacturerTranslation.name }}</router-link>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots-vertical text-info opacity-50 mx-1" viewBox="0 0 16 16">
                                <path d="M9.5 13a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/>
                            </svg>
                        </template>
                        <template v-if="actualProductDetails.sku">
                            {{ actualProductDetails.sku }}
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots-vertical text-info opacity-50 mx-1" viewBox="0 0 16 16">
                                <path d="M9.5 13a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/>
                            </svg>
                        </template>
                        <span class="me-2">{{ $t('Available') }}: </span>
                        <span v-if="actualProductDetails.quantity > 0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill text-success me-1" viewBox="0 0 16 16">
                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                            </svg>
                            {{ $t('In stock') }}
                        </span>
                        <span v-else>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-dash-circle-fill text-danger me-1" viewBox="0 0 16 16">
                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM4.5 7.5a.5.5 0 0 0 0 1h7a.5.5 0 0 0 0-1h-7z"/>
                            </svg>
                            {{ $t('Out of stock') }}
                        </span>
                    </div>
                    <div v-if="productDetails.categories.length > 0" class="mt-3">
                        {{ $t('Department(s)') }}: 
                        <router-link v-for="(category, index) in productDetails.categories" :key="category.id" :to="`/category/${translation(category, 'slug', $i18n.locale)}`" class="text-decoration-none">
                            <template v-if="index > 0">, </template>
                            {{ translation(category, 'name', $i18n.locale) }}
                        </router-link>
                    </div>

                    <!-- Attributes read only -->
                    <ul v-if="Object.keys(productAttributesReadonly).length > 0" class="list-unstyled mt-3">
                        <li v-for="optId in Object.keys(productAttributesReadonly)" :key="optId">
                            <span class="fw-bold me-2">{{ translation(productAttributesReadonly[optId], 'name', this.$i18n.locale) }}:</span>
                            <span v-for="(optval, index) in productAttributesReadonly[optId].values" :key="optval.id">
                                <template v-if="index !== 0">, </template>
                                {{ translation(optval, 'name', this.$i18n.locale) }}
                            </span>
                        </li>
                    </ul>

                    <!-- Product price -->
                    <div class="h4 mt-3 mb-0 fw-bold">
                        <product-display-price :product-price="productPrice(actualProductDetails, 1)"></product-display-price>
                    </div>

                    <!-- Product variants -->
                    <div class="row mt-3" v-if="productVariants.length > 0">
                        <div class="col-12">
                            <div v-for="ao in productVariants" :key="ao.id" class="mb-3">
                                <template v-if="ao.values.length > 4">
                                    <div class="mb-2 fw-bold">{{ translation(ao, 'name', $i18n.locale) }}:</div>
                                    <div class="dropdown">
                                        <button class="btn border dropdown-toggle" type="button" data-bs-toggle="dropdown">{{ translation(ao.values.find(v => v.id === selectedAttributes[ao.id]), 'name', $i18n.locale) }}</button>
                                        <ul :id="`option${ao.id}`" class="dropdown-menu">
                                            <li v-for="aov in ao.values" :value="aov.vid" :key="aov.vid"><a class="dropdown-item cursor-pointer" @click="selectedAttributes[ao.id] = aov.vid" :data-aovid="aov.vid" :id="`attr-${ao.id}-${aov.vid}`">{{ translation(aov, 'name', $i18n.locale) }}</a></li>
                                        </ul>
                                    </div>
                                </template>
                                <template v-else>
                                    <div :id="`option${ao.id}`">
                                        <div class="mb-2"><span class="fw-bold">{{ translation(ao, 'name', $i18n.locale) }}:</span><span class="ms-2">{{ translation(ao.values.find(v => v.id === selectedAttributes[ao.id]), 'name', $i18n.locale) }}</span></div>
                                        <div v-for="aov in ao.values" :value="aov.vid" :key="aov.vid" @click.stop="selectedAttributes[ao.id] = aov.vid" :data-aovid="aov.vid" :id="`attr-${ao.id}-${aov.vid}`" :class="`dropdown-item cursor-pointer py-2 px-3 d-inline-block me-3 mb-3 pa ${selectedAttributes[ao.id] === aov.vid ? 'pa-selected' : ''}`">
                                            <template v-if="aov.image">
                                                <img :src="`/storage/${storeConfig.small_image_size}/${aov.image}`" :alt="productTranslation.name" :width="storeConfig.small_image_size" :height="storeConfig.small_image_size" class="img-fluid">
                                            </template>
                                            <template v-else>
                                                {{ translation(aov, 'name', $i18n.locale) }}
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Attributes text -->
                    <div class="row mt-3" v-if="productAttributesText.length > 0">
                        <div class="col-12">
                            <div v-for="ao in productAttributesText" :key="ao.id" class="mb-3">
                                <div :id="`option${ao.id}`">
                                    <div class="mb-2"><span class="fw-bold">{{ translation(ao, 'name', $i18n.locale) }}:</span></div>
                                    <textarea v-model="formdata.meta[`${ao.id}_${aov.vid}`]" v-for="aov in ao.values" :key="aov.vid" class="form-control" :placeholder="translation(aov, 'name', $i18n.locale)" rows="1"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Restock date -->
                    <div v-if="actualProductDetails.availability_date" class="mt-3 text-info"><strong>{{ $t('Restock date:') }}</strong> {{ actualProductDetails.availability_date }}</div>

                    <!-- Display articles for this product. E.g Size, Color, How to use... -->
                    <product-article :ids="productDetails.meta && productDetails.meta.articles"></product-article>

                    <!-- Product cart form -->
                    <div class="d-inline-block mt-3">
                        <template v-if="actualProductDetails.quantity > 0">
                            <input v-model="formdata.qty" class="form-control me-2 input-qty d-inline-block" type="number">
                            <button class="btn btn-success d-inline-block align-items-center me-2  text-white" type="submit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cart-plus-fill me-2" viewBox="0 0 16 16">
                                    <path d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H2.89l-.405-1.621A.5.5 0 0 0 2 1H.5zM6 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm7 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zM9 5.5V7h1.5a.5.5 0 0 1 0 1H9v1.5a.5.5 0 0 1-1 0V8H6.5a.5.5 0 0 1 0-1H8V5.5a.5.5 0 0 1 1 0z"/>
                                </svg>
                                {{ $t('Add to Cart') }}
                            </button>
                        </template>
                        <button v-else @click.prevent="showModal = true" class="btn btn-warning d-inline-block align-items-center me-2" type="button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-magic me-2" viewBox="0 0 16 16">
                                <path d="M9.5 2.672a.5.5 0 1 0 1 0V.843a.5.5 0 0 0-1 0v1.829Zm4.5.035A.5.5 0 0 0 13.293 2L12 3.293a.5.5 0 1 0 .707.707L14 2.707ZM7.293 4A.5.5 0 1 0 8 3.293L6.707 2A.5.5 0 0 0 6 2.707L7.293 4Zm-.621 2.5a.5.5 0 1 0 0-1H4.843a.5.5 0 1 0 0 1h1.829Zm8.485 0a.5.5 0 1 0 0-1h-1.829a.5.5 0 0 0 0 1h1.829ZM13.293 10A.5.5 0 1 0 14 9.293L12.707 8a.5.5 0 1 0-.707.707L13.293 10ZM9.5 11.157a.5.5 0 0 0 1 0V9.328a.5.5 0 0 0-1 0v1.829Zm1.854-5.097a.5.5 0 0 0 0-.706l-.708-.708a.5.5 0 0 0-.707 0L8.646 5.94a.5.5 0 0 0 0 .707l.708.708a.5.5 0 0 0 .707 0l1.293-1.293Zm-3 3a.5.5 0 0 0 0-.706l-.708-.708a.5.5 0 0 0-.707 0L.646 13.94a.5.5 0 0 0 0 .707l.708.708a.5.5 0 0 0 .707 0L8.354 9.06Z"/>
                            </svg>
                            {{ $t('Notify when available') }}
                        </button>
                    </div>
                    
                    <!-- Hook after add to cart button. -->
                    <template v-for="(component, index) in $pluginStorefrontHooks['product_after_add_to_cart_button']" :key="index">
                        <component :is="component" :product="actualProductDetails" :translation="productTranslation" @updateMetaForm="updateMetaForm"></component>
                    </template>

                    <div class="mt-3">
                        <share-buttons :title="productTranslation.name" :content="productTranslation.description"></share-buttons>
                    </div>

                </form>
				
				<!-- Hook after the form -->
                <template v-for="(component, index) in $pluginStorefrontHooks['product_after_the_form']" :key="index">
                    <component :is="component" :product="actualProductDetails" :translation="productTranslation" @updateMetaForm="updateMetaForm"></component>
                </template>

                <product-restock-modal :product-id="actualProductDetails.id" :product-name="productTranslation.name" :show-modal="showModal" @updateModalStatus="updateModalStatus"></product-restock-modal>
                
            </div> <!-- Product loaded -->
            <product-details-loading v-else></product-details-loading>
        </div>
    </section>
    
    <section v-if="loadedProduct" class="section-product-desc my-5">
        <ul class="nav nav-tabs mb-5 h5" id="app-details-description__tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#zuc-product-description" type="button" role="tab" aria-controls="zuc-product-description" aria-selected="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-body-text text-info opacity-25 me-2 d-none d-sm-inline-block" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M0 .5A.5.5 0 0 1 .5 0h4a.5.5 0 0 1 0 1h-4A.5.5 0 0 1 0 .5Zm0 2A.5.5 0 0 1 .5 2h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5Zm9 0a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5Zm-9 2A.5.5 0 0 1 .5 4h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5Zm5 0a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5Zm7 0a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5Zm-12 2A.5.5 0 0 1 .5 6h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5Zm8 0a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5Zm-8 2A.5.5 0 0 1 .5 8h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5Zm7 0a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5Zm-7 2a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 0 1h-8a.5.5 0 0 1-.5-.5Zm0 2a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5Zm0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5Z"/>
                    </svg>
                    {{ $t('Description') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#zuc-product-reviews" type="button" role="tab" aria-controls="zuc-product-reviews" aria-selected="false">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-star-half text-info opacity-25 me-2 d-none d-sm-inline-block" viewBox="0 0 16 16">
                        <path d="M5.354 5.119 7.538.792A.516.516 0 0 1 8 .5c.183 0 .366.097.465.292l2.184 4.327 4.898.696A.537.537 0 0 1 16 6.32a.548.548 0 0 1-.17.445l-3.523 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256a.52.52 0 0 1-.146.05c-.342.06-.668-.254-.6-.642l.83-4.73L.173 6.765a.55.55 0 0 1-.172-.403.58.58 0 0 1 .085-.302.513.513 0 0 1 .37-.245l4.898-.696zM8 12.027a.5.5 0 0 1 .232.056l3.686 1.894-.694-3.957a.565.565 0 0 1 .162-.505l2.907-2.77-4.052-.576a.525.525 0 0 1-.393-.288L8.001 2.223 8 2.226v9.8z"/>
                    </svg>
                    {{ $t('Reviews') }}
                </button>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="zuc-product-description" role="tabpanel" v-html="productTranslation.description"></div>
            <div class="tab-pane" id="zuc-product-reviews" role="tabpanel">
                <latest-product-reviews :id="productDetails.id" :average="productDetails.review"></latest-product-reviews>
            </div>
        </div>
    </section>
    <section v-else class="section-product-desc my-5">
        <div class="py-4 col-lg-4 col-6 bg-gray-200 mb-3 rounded"></div>
        <div class="py-2 bg-gray-200 w-100 rounded mb-1"></div>
        <div class="py-2 bg-gray-200 w-75 rounded mb-1"></div>
        <div class="py-2 bg-gray-200 w-50 rounded mb-1"></div>
        <div class="py-2 bg-gray-200 w-25 rounded"></div>
    </section>

    <product-crosssell :loaded="loadedProduct"></product-crosssell>
    <product-upsell :loaded="loadedProduct"></product-upsell>
    
    <!-- Hook at the bottom -->
    <section v-if="loadedProduct">
        <template v-for="(component, index) in $pluginStorefrontHooks['product_at_the_bottom']" :key="index">
            <component :is="component" :product="actualProductDetails" :translation="productTranslation" @updateMetaForm="updateMetaForm"></component>
        </template>
    </section>
    
</template>

<script>
import Tab from 'bootstrap/js/dist/tab';
import DisplayCarousel from '@theme/storefront/templates/product/DisplayCarousel'
import LoadingCarousel from '@theme/storefront/templates/product/LoadingCarousel'
import DisplayRating from '@theme/storefront/templates/product/DisplayRating'
import ProductDisplayPrice from '@theme/storefront/templates/product/DisplayPrice'
import LatestProductReviews from '@theme/storefront/templates/product/LatestReviews'
import ProductCrosssell from '@theme/storefront/templates/product/Crosssell'
import ProductUpsell from '@theme/storefront/templates/product/Upsell'
import ProductRelated from '@theme/storefront/templates/product/Related'
import LoadingSidebarProduct from '@theme/storefront/templates/product/LoadingSidebarProduct'
import ProductArticle from '@theme/storefront/templates/product/Article'
import ShareButtons from '@theme/storefront/templates/element/ShareButtons'
import ProductRestockModal from '@theme/storefront/templates/product/Restock'
import ProductDetailsLoading from '@theme/storefront/templates/product/ProductDetailsLoading'
import { mapState, mapGetters } from 'vuex'
export default {
    components: {
        DisplayCarousel, LoadingCarousel, DisplayRating, ProductDisplayPrice, LatestProductReviews, 
        ShareButtons, ProductCrosssell, ProductUpsell, ProductRelated, LoadingSidebarProduct, ProductArticle, 
        ProductRestockModal, ProductDetailsLoading
    },
    data: () => ({
        selectedAttributes: {},
        formdata: {
            qty: 1,
            meta: {},
        },
        loadedProduct: false,
        showModal: false
    }),
    created() {
        this.queryProductDetails(this.$route.params.productslug)
    },
    mounted() {
		const elTabs = [].slice.call(document.querySelectorAll('.section-product-desc button')).forEach(el => new Tab(el))
    },
    unmounted() {
        this.$store.commit('resetProductDetails')
    },
    beforeRouteUpdate (to, from, next) {
        if(to.params.productslug !== this.$route.params.productslug) {
            this.reinitialize()
            this.queryProductDetails(to.params.productslug)
        }
        next()
    },
    methods: {
        queryProductDetails(slug) {

            this.loadedProduct = false

            this.$store.dispatch('productDetails', slug).then(() => {

                if(this.productDetails.default_attributes !== undefined) {
                    Object.keys(this.productDetails.default_attributes).forEach(aoId => (this.selectedAttributes = {  ...this.selectedAttributes, [aoId]: this.productDetails.default_attributes[aoId] }))
                }

                this.loadedProduct = true

            }).catch(error => {
                this.$store.commit('setAlert', {
                    'color': 'danger', 
                    'message': this.$t(error.response.data.message)
                })
                this.$router.push(`/page-not-found`)
            })
        },
        add2Cart() {
            this.$store.dispatch('addProduct2Cart', { id: this.actualProductDetails.id, cart_quantity: this.formdata.qty, meta: this.formdata.meta }).then(() => {
                this.$store.commit('setAlert', {
                    'color': 'success', 
                    'message': `${this.productTranslation.name} ${this.$t("has been added to your cart.")}`
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
        reinitialize() {
            this.selectedAttributes = {}
            this.formdata = {
                qty: 1,
                meta: {},
            }
        },
        updateMetaForm(obj) {
            this.formdata.meta = { ...this.formdata.meta, ...obj }
        }
    },
    computed: {
        ...mapGetters([
            'translation', 'transObj', 'productVariants', 'productAttributesReadonly', 'productAttributesText', 
            'childProductByAttributes', 'productPrice'
        ]),
        ...mapState({
            productDetails: state => state.product.productDetails,
            storeConfig: state => state.setting.storeConfig,
        }),
        actualProductDetails() {
            const actualProduct = this.childProductByAttributes(this.productDetails, this.selectedAttributes)
            if(actualProduct !== undefined) {
                return actualProduct;
            } else {
                return this.productDetails;
            }
        },
        productTranslation() {
            return this.transObj(this.actualProductDetails, this.$i18n.locale)
        },
        manufacturerTranslation() {
            return this.transObj(this.productDetails.manufacturer , this.$i18n.locale) || undefined
        }
    },
    watch: {
        'formdata.qty': function(val) {
            if(val <= 0) {
                this.formdata.qty = 1
            }
        },
        actualProductDetails() {
            document.title = this.productTranslation.meta_title
            document.querySelector('meta[name="description"]').setAttribute("content", this.productTranslation.meta_description)
        },
        selectedAttributes: {
            deep: true,
            handler: async function(val) {
                const oids = await Promise.all(Object.keys(val))
                oids.forEach(oid => {
                    const attrEls = document.querySelectorAll(`#option${oid} .dropdown-item`)
                    attrEls.forEach(e => document.querySelector(`#attr-${oid}-${e.getAttribute('data-aovid')}`).classList.remove('outofstock'))
                    this.productDetails.attributes.filter(at => +at.attribute_option_id === +oid)
                                                    .map(r => r.attribute_option_value_id)
                                                    .forEach(ovid => this.childProductByAttributes(this.productDetails, { ...this.selectedAttributes, [oid]: ovid }).quantity === 0 
                                                        && document.querySelector(`#attr-${oid}-${ovid}`).classList.add('outofstock') )
                })
            }
        }
    }
}
</script>