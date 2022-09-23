<template>
    <section v-if="loaded" class="section-viewed">
        <template v-if="viewed && viewed.length > 0">
            <div class="h4 fw-bold mb-3">{{ $t("Recently viewed") }}</div>
            <div v-for="(rows, index) in chunkedViewed" :key="index" class="row">
                <div v-for="item in rows" :key="item.id" :class="`col-12 col-md-4 mb-4`">
                    <div class="section-viewed__info d-flex">
                        <router-link :to="`/${translation(item, 'slug', $i18n.locale)}`">
                            <img :src="`/storage/${storeConfig.small_image_size}/${item.images[0].src}`" :width="storeConfig.small_image_size" :height="storeConfig.small_image_size" :alt="translation(item, 'name', $i18n.locale)" class="img-loading">
                        </router-link>
                        <div class="text ms-3">
                            <h3 class="h6"><router-link :to="`/${translation(item, 'slug', $i18n.locale)}`" class="text-decoration-none">{{ translation(item, 'name', $i18n.locale) }}</router-link></h3>
                            <display-rating :rating="item.rating" :total="item.total_reviews"></display-rating>
                            <div class="d-flex align-items-center">
                                <div class="fw-bold">
                                    <product-display-price :product-price="productPrice(item, 1)"></product-display-price>
                                </div>
                                <template v-if="+item.has_attributes === 0">
                                    <svg v-if="item.quantity > 0" @click.stop="addToCart(item)" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle-fill text-success ms-2 cursor-pointer" viewBox="0 0 16 16">
                                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3v-3z"/>
                                    </svg>
                                    <svg v-else @click.prevent="showModal = true, picked = item.id" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-send-plus text-warning ms-2 cursor-pointer" viewBox="0 0 16 16">
                                        <path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855a.75.75 0 0 0-.124 1.329l4.995 3.178 1.531 2.406a.5.5 0 0 0 .844-.536L6.637 10.07l7.494-7.494-1.895 4.738a.5.5 0 1 0 .928.372l2.8-7Zm-2.54 1.183L5.93 9.363 1.591 6.602l11.833-4.733Z"/>
                                        <path d="M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Zm-3.5-2a.5.5 0 0 0-.5.5v1h-1a.5.5 0 0 0 0 1h1v1a.5.5 0 0 0 1 0v-1h1a.5.5 0 0 0 0-1h-1v-1a.5.5 0 0 0-.5-.5Z"/>
                                    </svg>
                                </template>
                                <router-link v-else :to="`/${translation(item, 'slug', $i18n.locale)}`">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-grid-3x3-gap text-primary ms-2 cursor-pointer" viewBox="0 0 16 16">
                                        <path d="M4 2v2H2V2h2zm1 12v-2a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1zm0-5V7a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1zm0-5V2a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1zm5 10v-2a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1zm0-5V7a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1zm0-5V2a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1zM9 2v2H7V2h2zm5 0v2h-2V2h2zM4 7v2H2V7h2zm5 0v2H7V7h2zm5 0h-2v2h2V7zM4 12v2H2v-2h2zm5 0v2H7v-2h2zm5 0v2h-2v-2h2zM12 1a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1h-2zm-1 6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1V7zm1 4a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1h-2z"/>
                                    </svg>
                                </router-link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </section>
    <product-restock-modal :product-id="this.picked" :show-modal="showModal" @updateModalStatus="updateModalStatus"></product-restock-modal>
</template>

<script>
import DisplayRating from '@theme/storefront/templates/product/DisplayRating'
import ProductDisplayPrice from '@theme/storefront/templates/product/DisplayPrice'
import ProductRestockModal from '@theme/storefront/templates/product/Restock'
import { mapGetters, mapState } from 'vuex'
export default {
    data: () => ({
        showModal: false,
        picked: undefined,
        loaded: false,
        itemPerRow: 3,
        viewed: [],
    }),
    components: {  DisplayRating, ProductDisplayPrice, ProductRestockModal },
    mounted() {
        axios.get(`/api/v1/storefront/cart/viewed`).then(res => {
            this.viewed = res.data
            this.loaded = true
        })
    },
    methods: {
        addToCart(item) {
            const productName = this.translation(item, 'name', this.$i18n.locale)
            this.$store.dispatch('addProduct2Cart', { id: item.id, cart_quantity: 1 }).then(() => {
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
        }
    },
    computed: {
        ...mapGetters(['translation', 'productPrice']),
        ...mapState({
            storeConfig: state => state.setting.storeConfig
        }),
        chunkedViewed() {
            return _.chunk(this.viewed, this.itemPerRow)
        }
    },
}
</script>