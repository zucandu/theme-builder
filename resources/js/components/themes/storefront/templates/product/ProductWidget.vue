<template>
    <div v-for="item in products" :key="item.id" class="product-widget px-3">
        <div class="product-widget__inner">
            <div class="inner__img mb-3">
                <router-link :to="`/${translation(item, 'slug', $i18n.locale)}`" class="text-decoration-none">
                    <img :src="`/storage/${storeConfig.medium_image_size}/${item.images[0].src}`" :width="storeConfig.medium_image_size" :height="storeConfig.medium_image_size" :alt="translation(item, 'name', $i18n.locale)" class="img-fluid">
                </router-link>
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
                    <button @click="pickpickItemRestock(item)" class="btn btn-info btn-sm text-white w-100" type="button">
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

<script>
import DisplayRating from '@theme/storefront/templates/product/DisplayRating'
import ProductDisplayPrice from '@theme/storefront/templates/product/DisplayPrice'
import { mapState, mapGetters } from 'vuex'
export default {
    data: () => ({
        cartQty: {}
    }),
    components: { ProductDisplayPrice, DisplayRating },
    props: ['products'],
    emits: ['pickItemRestock'],
    created() {
        this.products.map(p => this.cartQty[p.id] = 1)
    },
    methods: {
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
        pickpickItemRestock(item) {
            this.$emit('pickItemRestock', { id: item.id, name: this.translation(item, 'name', this.$i18n.locale) })
        }
    },
    computed: {
        ...mapGetters(['productPrice', 'translation']),
        ...mapState({
            storeConfig: state => state.setting.storeConfig,
        })
    }
}
</script>