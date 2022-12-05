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
            <div v-if="item.quantity < 0" class="inner__cart d-flex w-100 bottom-0 mt-3">
                <button @click="pickpickItemRestock(item)" class="btn btn-info btn-sm text-white w-100" type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-send-plus" viewBox="0 0 16 16">
                        <path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855a.75.75 0 0 0-.124 1.329l4.995 3.178 1.531 2.406a.5.5 0 0 0 .844-.536L6.637 10.07l7.494-7.494-1.895 4.738a.5.5 0 1 0 .928.372l2.8-7Zm-2.54 1.183L5.93 9.363 1.591 6.602l11.833-4.733Z"/>
                        <path d="M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Zm-3.5-2a.5.5 0 0 0-.5.5v1h-1a.5.5 0 0 0 0 1h1v1a.5.5 0 0 0 1 0v-1h1a.5.5 0 0 0 0-1h-1v-1a.5.5 0 0 0-.5-.5Z"/>
                    </svg>
                    {{ $t('Notify') }}
                </button>
            </div>
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