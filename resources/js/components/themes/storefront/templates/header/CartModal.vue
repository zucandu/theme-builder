<template>
    <section id="z-minicart" class="minicart d-flex justify-content-end cursor-pointer">
        <router-link class="z-minicart__account btn me-3 d-inline d-md-none" to="/account">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-people" viewBox="0 0 16 16">
                <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816zM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275zM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
            </svg>
        </router-link>
        <div class="d-flex">
            <div class="minicart__icon rounded d-flex justify-content-center align-items-center me-sm-2 flex-shrink-0 position-relative">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-handbag" viewBox="0 0 16 16" data-bs-toggle="modal" data-bs-target="#z-cart-modal">
                    <path d="M8 1a2 2 0 0 1 2 2v2H6V3a2 2 0 0 1 2-2zm3 4V3a3 3 0 1 0-6 0v2H3.36a1.5 1.5 0 0 0-1.483 1.277L.85 13.13A2.5 2.5 0 0 0 3.322 16h9.355a2.5 2.5 0 0 0 2.473-2.87l-1.028-6.853A1.5 1.5 0 0 0 12.64 5H11zm-1 1v1.5a.5.5 0 0 0 1 0V6h1.639a.5.5 0 0 1 .494.426l1.028 6.851A1.5 1.5 0 0 1 12.678 15H3.322a1.5 1.5 0 0 1-1.483-1.723l1.028-6.851A.5.5 0 0 1 3.36 6H5v1.5a.5.5 0 1 0 1 0V6h4z"/>
                </svg>
                <a href="javascript:void(0);" class="text-decoration-none text-dark" data-bs-toggle="modal" data-bs-target="#z-cart-modal">
                    <span class="position-absolute badge rounded-circle bg-success d-block d-sm-none minicart__count">
                        {{ items.length }}
                    </span>
                </a>
            </div>
            <div class="minicart__content text-start d-none d-sm-block">
                <div class="h6 mb-0 text-uppercase fw-bold">
                    <a href="javascript:void(0);" class="text-decoration-none text-dark" data-bs-toggle="modal" data-bs-target="#z-cart-modal">
                        {{ $t('Shopping cart') }}
                    </a>
                </div>
                <div class="total-items">
                    <a href="javascript:void(0);" class="text-decoration-none text-dark" data-bs-toggle="modal" data-bs-target="#z-cart-modal">
                        {{ items.length }} {{ $t('item(s)') }} - <display-price :price="cartTotal"></display-price>
                    </a>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="z-cart-modal" tabindex="-1" aria-labelledby="z-cart-modal-label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="z-cart-modal-label">{{ $t('Shopping cart') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div v-if="items && items.length > 0" class="card card-body border-0 p-0">
                            <div class="z-cart-modal__inner">
                                <div v-for="(item, key) in items" :key="item.id" :class="`d-flex ${key > 0 ? 'mt-2 pt-2 border-top' : ''}`">
                                    <div class="p-2 flex-shrink-1 bd-highlight">
                                        <img v-if="item.images && item.images.length > 0" :src="`/storage/${storeConfig.small_image_size}/${item.images[0].src}`" :alt="translation(item, 'name', $i18n.locale)" :width="storeConfig.small_image_size" class="img-thumbnail">
                                        <img v-else :src="`/storage/${storeConfig.small_image_size}/no-image.png`" :alt="translation(item, 'name', $i18n.locale)" :width="storeConfig.small_image_size" class="img-thumbnail">
                                    </div>
                                    <div class="w-100 small">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <router-link :to="`/${translation(item, 'slug', $i18n.locale)}`" v-html="`${item.qty} x ${translation(item, 'name', $i18n.locale)}`"></router-link>
                                            <button class="btn btn-link btn-sm p-0 text-secondary text-decoration-none ms-3" @click.stop="this.$store.dispatch('removeProduct', item.id)">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                                    <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                                    <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                                                </svg>
                                            </button>
                                        </div>
                                        <product-display-price :product-price="productPrice(item, item.qty)"></product-display-price>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent px-0 py-3">
                                <div class="d-flex justify-content-between mb-3">
                                    <div>{{ $t('Subtotal') }}</div>
                                    <div><display-price :price="cartTotal"></display-price></div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-6">
                                        <router-link class="btn btn-outline-secondary d-block rounded-0" to="/cart">{{ $t('View Cart') }}</router-link>
                                    </div>
                                    <div class="col-6">
                                        <router-link class="btn btn-success d-block mb-3 rounded-0 text-white" to="/checkout">{{ $t('Checkout') }}</router-link>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p v-else class="mb-0 p-3 text-secondary">{{ $t('Your cart is currently empty!') }}</p>
                    </div>
                </div>
            </div>
        </div>

    </section>
</template>

<script>
import DisplayPrice from '@theme/storefront/templates/currency/DisplayPrice'
import ProductDisplayPrice from '@theme/storefront/templates/product/DisplayPrice'
import { mapGetters, mapState } from 'vuex'
export default {
    components: {
        DisplayPrice, ProductDisplayPrice
    },
    computed: {
        ...mapGetters(['translation', 'productPrice', 'cartTotal']),
        ...mapState({
            storeConfig: state => state.setting.storeConfig,
            items: state => state.cart.items,
        })
    }
}
</script>