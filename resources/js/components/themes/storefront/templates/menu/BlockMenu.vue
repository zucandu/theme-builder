<template>
    <section :class="`menu-element menu-element-${item.id} menu-${item.link}-element`">
        <figure v-if="item.extra_data && item.extra_data.image">
            <router-link :class="`nav-link ${ extraClass !== undefined ? extraClass : '' }`" :to="url">
                <img v-if="item.link === 'banner'" :src="`/storage/${item.extra_data.image}`" :alt="translation.title" class="img-fluid">
                <img v-else :src="`/storage/${storeConfig.medium_image_size}/${item.extra_data.image}`" :alt="translation.title" class="img-fluid">
            </router-link>
        </figure>
        <h5 class="menu-element__content">
            <router-link :class="`nav-link ${ extraClass !== undefined ? extraClass : '' }`" :to="url">
                {{ translation.title }}
            </router-link>
        </h5>
        <template v-if="item.rating || item.extra_data.price">
            <display-rating :rating="item.rating" :total="item.total_reviews"></display-rating>
            <product-display-price :product-price="productPrice(item.extra_data, 1)"></product-display-price>
        </template>
    </section>
</template>

<script>
import ProductDisplayPrice from '@theme/storefront/templates/product/DisplayPrice'
import DisplayRating from '@theme/storefront/templates/product/DisplayRating'
import { mapGetters, mapState } from 'vuex'
export default {
    props: ['item', 'extraClass'],
    components: { ProductDisplayPrice, DisplayRating },
    computed: {
        ...mapGetters(['transObj', 'trim', 'productPrice']),
        ...mapState({
            storeConfig: state => state.setting.storeConfig
        }),
        translation() {
            return this.transObj(this.item, this.$i18n.locale)
        },
        url() {
            return ['page', 'product', 'banner'].includes(this.item.link) === false ? `/${this.item.link}/${this.translation.url}` : `/${_.trim(this.translation.url, '/')}`
        }
    },
}
</script>