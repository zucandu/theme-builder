<template>
    <div class="row justify-content-center" v-if="Object.keys(order).length > 0">
        <div class="col-12 col-lg-10">
            <div class="row mb-5">
                <div class="col-12 text-center">
                    <h1>{{ $t("Thank you for your order!")}}</h1>
                    <p>{{ $t('The order confirmation email with details of your order and a link to track its progress has been sent to your email address.') }}</p>
                    <div class="text-success fw-bold">{{ $t('YOUR ORDER # IS:') }} {{ order.id }}</div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="fw-bold">{{ $t('Shipping Address') }}</p>
                            <display-address :address="deliveryAddress"></display-address>
                        </div>
                        <div class="col-md-6">
                            <p class="fw-bold">{{ $t('Shipping Method') }}</p>
                            <p>{{ $t(order.shipping_method) }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="fw-bold">{{ $t('Billing Address') }}</p>
                            <display-address :address="billingAddress"></display-address>
                        </div>
                        <div class="col-md-6">
                            <p class="fw-bold">{{ $t('Payment Method') }}</p>
                            <p>{{ $t(order.payment_method) }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <table class="table table-striped hover">
                        <tbody>
                            <tr v-for="item in order.items" :key="item.id">
                                <td>
                                    <img v-if="item.images && item.images.length > 0" :src="`/storage/${storeConfig.small_image_size}/${item.images[0].src}`" :width="storeConfig.small_image_size" :height="storeConfig.small_image_size" :alt="item.name" class="img-thumbnail">
                                    <img v-else src="/storage/store/no-image.png" :width="storeConfig.small_image_size" :height="storeConfig.small_image_size" :alt="item.name" class="img-thumbnail">
                                </td>
                                <td>
                                    <div class="w300px">{{ item.name }}</div>
                                    <div class="text-gray-500">{{ $t('Qty') }}: {{ item.qty }}</div>

                                    <!-- Hook product title. -->
                                    <template v-for="(component, index) in $pluginStorefrontHooks['checkout_success_product_title']" :key="index">
                                        <component :is="component" :product="item"></component>
                                    </template>
                                </td>
                                <td class="text-end">
                                    <display-price-with-currency :price="item.price*item.qty" :currency="order.currency"></display-price-with-currency>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="h6">
                            <tr>
                                <td colspan="2" class="text-end border-0">{{ $t('Subtotal:') }}</td>
                                <td class="text-end border-0">
                                    <display-price-with-currency :price="order.subtotal" :currency="order.currency"></display-price-with-currency>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="text-end border-0">{{ $t('Shipping') }} ({{order.shipping_method}}):</td>
                                <td class="text-end border-0">
                                    <display-price-with-currency :price="order.shipping_amount" :currency="order.currency"></display-price-with-currency>
                                </td>
                            </tr>
                            <tr v-if="storeConfig.product_price_with_tax === 'n' && order.order_tax > 0">
                                <td colspan="2" class="text-end border-0">{{ $t(order.order_tax_name) }}</td>
                                <td class="text-end border-0">
                                    <display-price-with-currency :price="order.order_tax" :currency="order.currency"></display-price-with-currency>
                                </td>
                            </tr>
                            <template v-if="order.discounts && order.discounts.length > 0">
                                <tr v-for="discount in order.discounts" :key="discount.id">
                                    <td colspan="2" class="text-end border-0">{{ $t(discount.name)}}</td>
                                    <td class="text-end border-0">
                                        <span>-</span>
                                        <display-price-with-currency :price="discount.amount" :currency="order.currency"></display-price-with-currency>
                                    </td>
                                </tr>
                            </template>
                            <tr>
                                <td colspan="2" class="text-end border-0">{{ $t('Total:') }}</td>
                                <td class="text-end border-0">
                                    <display-price-with-currency :price="order.order_total" :currency="order.currency"></display-price-with-currency>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                    <div class="row">
                        <div class="col-md-4">
                            <router-link class="btn btn-primary" to="/">{{ $t('Continue Shopping') }}</router-link>
                        </div>
                        <div class="col-md-8 text-end">
                            <router-link to="/account/orders" class="btn btn-primary">{{ $t('My Account') }}</router-link>
                            <router-link to="/logout" class="btn btn-link ms-3">{{ $t('Logout') }}</router-link>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-lg-5 mt-3">
                <div class="col-12 text-center" v-if="profile.is_guest === 1">
                    <p>{{ $t('To receive more offers and promotional emails, we suggest you create an account by clicking the "Create an account" button below. You will receive an email containing the password.') }}</p>
                    <button :disabled="converting" class="btn btn-warning btn-lg" @click.stop="convertGuest2NormalAccount">{{ $t('Create an account') }}</button>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import DisplayPriceWithCurrency from '@theme/storefront/templates/currency/DisplayPriceWithCurrency'
import DisplayAddress from '@theme/storefront/templates/account/DisplayAddress'
import ProductDisplayPrice from '@theme/storefront/templates/product/DisplayPrice'
import { mapState, mapGetters } from 'vuex'
export default {
    data: () => ({
        converting: false,
    }),
    components: { DisplayAddress, DisplayPriceWithCurrency, ProductDisplayPrice },
    created() {

        // Set title
        document.title = this.$t(`Thank you for your order #${this.$route.params.ref}`)

        // load order from ref
        this.$store.dispatch('orderDetailsByRef', this.$route.params.ref).then(() => {
            if(this.profile.is_guest !== 1) {
                this.$store.dispatch('account')
            }
            this.$store.commit('cartReset')
        }).catch(error => {
            this.$store.commit('setAlert', {
                'color': 'danger', 
                'message': this.$t(error.response.data.message)
            })
            this.$router.push('/')
        })

    },
    methods: {
        convertGuest2NormalAccount() {
            this.converting = true
            this.$store.dispatch('convertGuest2NormalAccount').then(() => {
                this.$store.commit('setAlert', {
                    'color': 'success', 
                    'message': this.$t("We have emailed you a temporary password. Please check your email and change it as soon as possible.")
                })
            })
        }
    },
    computed: {
        ...mapGetters(['productPrice', 'displayPrice']),
        ...mapState({
            order: state => state.order.orderFromDb,
            storeConfig: state => state.setting.storeConfig,
            profile: state => state.customer.profile,
        }),
        deliveryAddress() {
            return this.order.addresses.find(address => address.address_type === 'shipping')
        },
        billingAddress() {
            const address = this.order.addresses.find(address => address.address_type === 'billing')
            if(address === undefined) {
                return this.order.addresses.find(address => address.address_type === 'shipping')
            }
            return address
        }
    }
}
</script>