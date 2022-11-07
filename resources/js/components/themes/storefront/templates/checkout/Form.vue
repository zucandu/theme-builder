<template>
    <div>
        <div class="row">
            <div class="col-12">
                <div class="h5 mb-3">{{ $t('Shipping & Payment Address') }}</div>
                <div class="row" v-if="orderParams.profile && orderParams.profile.addresses && orderParams.profile.addresses.length > 0">
                    <div class="col-6">
                        <div class="h6">{{ $t('Shipping Address') }}</div>
                        <display-address :address="orderParams.shipping"></display-address>
                        <button v-if="orderParams.profile.addresses.length > 1" class="btn btn-sm btn-link p-0 mb-3 mb-lg-0" @click.stop="editAddress(orderParams.shipping, 'shipping')">{{ $t('Edit Address') }}</button>
                    </div>
                    <div class="col-6">
                        <div class="h6">{{ $t('Billing Address') }}</div>
                        <display-address :address="orderParams.billing"></display-address>
                        <button v-if="orderParams.profile.addresses.length > 1" class="btn btn-sm btn-link p-0 mb-3 mb-lg-0" @click.stop="editAddress(orderParams.billing, 'billing')">{{ $t('Edit Address') }}</button>
                    </div>
                    <div  v-if="orderParams.profile.addresses.length === 1">
                        <button class="btn btn-sm btn-link p-0" @click.stop="editAddress(orderParams.shipping)">{{ $t('Edit your current address or add a new?') }}</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-5">
            <div class="col-12">
                <div class="h5 mb-3">{{ $t('Shipping Methods') }}</div>
                <transition name="fade">
                    <div v-if="initLoaded">
                        <div class="mb-3 card card-body" v-for="shipping in orderShippingMethods" :key="shipping.code">
                            <div class="d-flex justify-content-between mb-3">
                                <div class="fw-bold">{{ $t(shipping.name) }}</div>
                                <div v-if="shipping.image"><img :src="`/storage/shippings/${shipping.image}`" :alt="shipping.name" width="50"></div>
                            </div>
                            <template v-if="shipping.methods.length > 0">
                                <div class="row" v-for="method in shipping.methods" :key="method.id">
                                    <template v-if="method.error !== true">
                                        <div class="col-8">
                                            <div class="form-check">
                                                <input v-model="formdata.shipping" class="form-check-input" type="radio" :value="method" :id="`shipping-method-${method.id}`">
                                                <label class="form-check-label" :for="`shipping-method-${method.id}`">{{ $t(method.title) }}</label>
                                            </div>
                                        </div>
                                        <div class="col-4 shipping-cost text-end">
                                            <display-price :price="displayPrice(method.cost, 1)"></display-price>
                                        </div>
                                    </template>
                                    <template v-else>
                                        <div class="col-12 text-danger">{{ $t(method.title) }}</div>
                                    </template>
                                </div>
                            </template>
                            <p v-else class="text-danger">{{ $t("This shipping method is not currently unavailable for your order.") }}</p>
                        </div>
                    </div>
                </transition>
                <div v-if="!initLoaded" class="card card-body mb-4">
                    <div class="bg-gray-200 py-3 px-5 mb-2 col-lg-6"></div>
                    <div class="d-flex justify-content-between">
                        <div class="bg-gray-200 py-2 px-5"></div>
                        <div class="bg-gray-200 py-2 px-5"></div>
                    </div>
                </div>
            </div>
            <div v-if="orderPromotions.length > 0" class="col-12">
                <div class="h5 mb-3">{{ $t('Promotions') }}</div>
                <transition name="fade">
                    <div v-if="initLoaded">
                        <div class="mb-3 card card-body" v-for="discount in orderPromotions" :key="discount.id">
                            <div class="h6">{{ $t(discount.module)}}</div>
                            <div v-for="(dfield, dindex) in discount.fields" :key="dindex">
                                <template v-if="dfield.input === 'text'">
                                    <div class="mb-3">
                                        <label class="form-label">{{ $t(dfield.label)}}</label>
                                        <input type="text" class="form-control" @input="setFormPromotionData(discount.id,  dfield.name, $event.target.value)">
                                    </div>
                                </template>
                                <template v-else-if="dfield.input === 'readonly'">
                                    <div>{{ $t(dfield.label)}}: {{dfield.value}}</div>
                                </template>
                            </div>
                            <button class="btn btn-primary" type="button" @click.stop="applyDiscount">{{ $t('Submit') }}</button>
                        </div>
                    </div>
                </transition>
                <div v-if="!initLoaded" class="card card-body mb-4">
                    <div class="bg-gray-200 py-3 px-5 mb-2 col-lg-6"></div>
                    <div class="d-flex justify-content-between">
                        <div class="bg-gray-200 py-2 px-5"></div>
                        <div class="bg-gray-200 py-2 px-5"></div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="h5 mb-3">{{ $t('Payment Methods') }}</div>
                <transition name="fade">
                    <div v-if="initLoaded">
                        <div class="mb-3 card card-body" v-for="payment in orderPaymentMethods" :key="payment.id">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="form-check">
                                    <input v-model="formdata.payment" class="form-check-input" type="radio" :value="payment" :id="`payment-method-${payment.id}`">
                                    <label class="form-check-label" :for="`payment-method-${payment.id}`">{{ $t(payment.module) }}</label>
                                </div>
                                <img v-if="payment.image" :src="`/storage/payments/${payment.image}`" width="150" height="150" class="img-fluid" :alt="payment.module">
                            </div>
                        </div>
                    </div>
                </transition>
                <div v-if="!initLoaded" class="card card-body mb-4">
                    <div class="bg-gray-200 py-3 px-5 mb-2 col-lg-6"></div>
                    <div class="d-flex justify-content-between">
                        <div class="bg-gray-200 py-2 px-5"></div>
                        <div class="bg-gray-200 py-2 px-5"></div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="h5 mb-3">{{ $t('Special Instructions or Comments About Your Order') }}</div>
                <transition name="fade">
                    <div v-if="initLoaded">
                        <div class="mb-3 card card-body">
                            <textarea v-model="formdata.comments.customer_note" class="form-control" rows="3" :placeholder="$t(`Write something...`)"></textarea>
                        </div>
                    </div>
                </transition>
                <div v-if="!initLoaded" class="card card-body mb-4">
                    <div class="bg-gray-200 py-3 px-5 mb-2 col-lg-6"></div>
                    <div class="d-flex justify-content-between">
                        <div class="bg-gray-200 py-2 px-5 w-100"><br><br><br><br></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import DisplayPrice from '@theme/storefront/templates/currency/DisplayPrice'
import DisplayAddress from '@theme/storefront/templates/account/DisplayAddress'
import { mapState, mapGetters } from 'vuex'
export default {
    data: () => ({
        formdata: {
            shipping: {},
            payment: {},
            discount: {},
            promotion: {},
            comments: {}
        },
        initLoaded: false
    }),
    components: {
        DisplayAddress, DisplayPrice
    },
    created() {
        this.$store.dispatch('initializingCheckout', this.orderParams).then(() => {
            this.formdata = { ...this.formdata, ...this.formOrderData }
        }).finally(() => this.initLoaded = true)
    },
    methods: {
        applyDiscount() {
            
            this.$store.dispatch('applyDiscount', this.formOrderData.promotion).then(() => {
                this.$store.commit('connectPaymentGateway', this.orderParams)
            }).catch(error => {
                
                this.$store.commit('setAlert', {
                    'color': 'danger', 
                    'message': this.$t(error.response.data.message)
                })

                // reset discount amount
                this.$store.commit('resetOrderDataDiscount')
            })
        },

        setFormPromotionData(module, field, value) {
            const obj = {
                [module]: {
                    [field]: value
                }
            }
            this.$store.commit('setFormPromotionData', obj)
        },

        editAddress(address, edit_address_type = undefined) {
            this.$emit('updateCheckoutStep', { step: 'CheckoutAddress', params: { ...address, edit_address_type: edit_address_type }})
        }
    },
    computed: {
        ...mapGetters(['displayPrice', 'orderParams', 'orderShippingMethods', 'orderPaymentMethods', 'orderPromotions']),
        ...mapState({
            formOrderData: state => state.order.formOrderData,
        })
    },
    watch: {
        'formdata.payment': function(newv, oldv) {

            // orderPaymentMethods method will select the first payment method if no payment method is selected
            // So, this code only should work when old value exists
            if(Object.keys(oldv).length > 0) {
                this.$store.commit('setFormOrderData', { payment: newv } )
                this.$store.commit('connectPaymentGateway', this.orderParams)
            }
        },
        'formdata.shipping': function(v) {
            if(v !== this.formOrderData.shipping) {
                this.$store.commit('setFormOrderData', { shipping: v } )
                this.$store.commit('connectPaymentGateway', this.orderParams)
            }
        },
        'formdata.comments': {
            deep: true,
            handler(v) {
                this.$store.commit('setFormOrderData', { comments: v } )
            },
        },
        orderPaymentMethods(v) {
            if(v && Object.keys(this.formdata.payment).length === 0) {
                this.$store.commit('setFormOrderData', { payment: v[0] })
                this.$store.commit('connectPaymentGateway', this.orderParams)
            }
        }
    }
}
</script>