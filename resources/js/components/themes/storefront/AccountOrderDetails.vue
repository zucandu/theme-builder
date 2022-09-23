<template>
    <div class="card" v-if="loaded">
        <div class="d-none print-only justify-content-between p-3">
            <img :src="`/storage/${storeConfig.fileuploader_store_logo}`" :width="storeConfig.store_logo_width" :height="storeConfig.store_logo_height" :alt="storeConfig.store_name" @load="imgloaded">
            <address>
                {{ storeConfig.store_name }}<br>
                {{ storeConfig.store_address }}<br>
                {{ storeConfig.store_city }}, {{ storeConfig.store_zone }} {{ storeConfig.store_postcode }}<br>
                {{ storeConfig.store_country }}<br>
                {{ storeConfig.store_phone }}<br>
                {{ storeConfig.store_email }}
            </address>
        </div>
        <div class="card-header fw-bold">{{ $t('Order ID') }}# {{ order.id }}</div>
        <div class="card-body">
            <p v-if="Object.keys(orderTrackingDetails).length > 0" class="fw-bold alert alert-info no-print">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-truck me-1" viewBox="0 0 16 16">
                    <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5v-7zm1.294 7.456A1.999 1.999 0 0 1 4.732 11h5.536a2.01 2.01 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456zM12 10a2 2 0 0 1 1.732 1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12v4zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                </svg>
                {{orderTrackingDetails.trackdetails.date}}: {{orderTrackingDetails.trackdetails.message}}
            </p>

            <table class="table table-striped hover">
                <caption>{{ $t('Order created') }}: {{ order.purchased_at }}</caption>
                <thead>
                    <tr>
                        <th>{{ $t('Name') }}</th>
                        <th>{{ $t('Qty') }}</th>
                        <th>{{ $t('Unit') }}</th>
                        <th class="text-end">{{ $t('Total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in order.items" :key="item.id">
                        <td>
                            <div class="mb-2">{{ item.name }}</div>
                        </td>
                        <td>{{ item.qty }}</td>
                        <td>
                            <display-price-with-currency :price="item.price" :currency="order.currency"></display-price-with-currency>
                        </td>
                        <td class="text-end">
                            <display-price-with-currency :price="item.price*item.qty" :currency="order.currency"></display-price-with-currency>
                        </td>
                    </tr>
                </tbody>
                <tfoot class="h6">
                    <tr>
                        <td colspan="3" class="text-end border-0">{{ $t('Subtotal:') }}</td>
                        <td class="text-end border-0">
                            <display-price-with-currency :price="order.subtotal" :currency="order.currency"></display-price-with-currency>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-end border-0">{{ $t('Shipping') }} ({{order.shipping_method}}):</td>
                        <td class="text-end border-0">
                            <display-price-with-currency :price="order.shipping_amount" :currency="order.currency"></display-price-with-currency>
                        </td>
                    </tr>
                    <tr v-if="storeConfig.product_price_with_tax === 'n' && order.order_tax > 0">
                        <td colspan="3" class="text-end border-0">{{ $t('Tax:') }}</td>
                        <td class="text-end border-0">
                            <display-price-with-currency :price="order.order_tax" :currency="order.currency"></display-price-with-currency>
                        </td>
                    </tr>
                    <template v-if="order.discounts && order.discounts.length > 0">
                        <tr v-for="discount in order.discounts" :key="discount.id">
                            <td colspan="3" class="text-end border-0">{{ $t(discount.name)}}</td>
                            <td class="text-end border-0">
                                <span>-</span>
                                <display-price-with-currency :price="discount.amount" :currency="order.currency"></display-price-with-currency>
                            </td>
                        </tr>
                    </template>
                    <tr>
                        <td colspan="3" class="text-end border-0">{{ $t('Total:') }}</td>
                        <td class="text-end border-0">
                            <display-price-with-currency :price="order.order_total" :currency="order.currency"></display-price-with-currency>
                        </td>
                    </tr>
                </tfoot>
            </table>

            <div class="alert alert-warning no-print" v-if="order.returns && order.returns.length > 0">
                <ul class="mb-0">
                    <li v-for="item in order.returns" :key="item.product_id" class="text-capitalize">
                        {{ item.resolution }} <strong>{{ item.qty }}x {{ item.name }}</strong> {{ $t('with reason') }} {{ item.reason }}
                    </li>
                </ul>
            </div>

            <!-- Used to hook into the account order details -->
            <template v-for="(component, index) in $pluginStorefrontHooks['account_order_details']" :key="index">
                <component :is="component"></component>
            </template>

            <div class="card-title h5 mt-3">{{ $t('Delivery & Shipping') }}</div>
            <div class="row">
                <div class="col-md-6">
                    <div class="h6">{{ $t('Delivery Address') }}</div>
                    <display-address :address="shippingAddress"></display-address>
                    <div class="h6 mt-3">{{ $t('Shipping Method') }}</div>
                    <p>{{order.shipping_method}}</p>
                </div>
                <div class="col-md-6">
                    <div class="h6">{{ $t('Billing Address') }}</div>
                    <display-address :address="billingAddress"></display-address>
                    <div class="h6 mt-3">{{ $t('Payment Method') }}</div>
                    <p>{{ $t(order.payment_method) }}</p>
                </div>
            </div>

            <div class="card-title h5 mt-3 no-print">{{ $t('Status History & Comments') }}</div>
            <table v-if="order.comments && order.comments.length > 0" class="table table-striped hover no-print">
                <thead>
                    <tr>
                        <th>{{ $t('Date') }}</th>
                        <th>{{ $t('Status') }}</th>
                        <th>{{ $t('Note') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="comment in order.comments" :key="comment.id">
                        <td>{{comment.created_at}}</td>
                        <td>{{comment.status_text}}</td>
                        <td>{{comment.note}}</td>
                    </tr>
                </tbody>
            </table>
            <p v-else class="no-print">{{ $t('There is no any comment at this time') }}</p>

            <div class="row">
                <div class="col-6">
                    <router-link to="/account/orders/list" class="btn btn-outline-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
                        </svg>
                        {{ $t('Back to Order List') }}
                    </router-link>
                </div>
                <div class="col-6 text-end">
                    <router-link :to="`/return-exchange/${order.reference}`" class="btn btn-link me-3">{{ $t('Need return or exchange?') }}</router-link>
                    <a href="#" @click.stop="printInvoice" class="btn btn-outline-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer-fill" viewBox="0 0 16 16">
                            <path d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2H5zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1z"/>
                            <path d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2V7zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                        </svg>
                        {{ $t('Print Invoice') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div v-else class="card card-body">
        <div class="rounded bg-gray-200 py-4 w-50 mb-3"></div>
        <div class="rounded bg-gray-200 py-4 w-100 mb-3"></div>
        <div class="rounded bg-gray-200 py-3 w-100 mb-2"></div>
        <div class="rounded bg-gray-200 py-2 w-100 mb-2"></div>
        <div class="rounded bg-gray-200 py-2 w-100 mb-2"></div>
        <div class="row">
            <div class="offset-9 col-3">
                <div class="rounded bg-gray-200 py-2 w-100 mb-2"></div>
                <div class="rounded bg-gray-200 py-2 w-100 mb-2"></div>
                <div class="rounded bg-gray-200 py-2 w-100 mb-2"></div>
            </div>
        </div>
        <div class="row mt-lg-5 mt-3">
            <div class="col-4">
                <div class="rounded bg-gray-200 py-2 w-100 mb-2"></div>
                <div class="rounded bg-gray-200 py-2 w-100 mb-2"></div>
                <div class="rounded bg-gray-200 py-2 w-100 mb-2"></div>
                <div class="rounded bg-gray-200 py-2 w-50 mb-2"></div>
            </div>
            <div class="offset-4 col-4">
                <div class="rounded bg-gray-200 py-2 w-100 mb-2"></div>
                <div class="rounded bg-gray-200 py-2 w-100 mb-2"></div>
                <div class="rounded bg-gray-200 py-2 w-100 mb-2"></div>
                <div class="rounded bg-gray-200 py-2 w-50 mb-2"></div>
            </div>
        </div>
        <div class="rounded bg-gray-200 py-4 w-25 mb-3 mt-3"></div>
        <div class="rounded bg-gray-200 py-3 w-100 mb-2"></div>
        <div class="rounded bg-gray-200 py-2 w-100 mb-2"></div>
        <div class="rounded bg-gray-200 py-2 w-100 mb-2"></div>
    </div>
</template>

<script>
import DisplayPriceWithCurrency from '@theme/storefront/templates/currency/DisplayPriceWithCurrency'
import DisplayAddress from '@theme/storefront/templates/account/DisplayAddress'
import { mapState, mapGetters } from 'vuex'
export default {
    data: () => ({
        loaded: false
    }),
    components: {
        DisplayAddress, DisplayPriceWithCurrency
    },
    created() {
        // load order from ref
        this.$store.dispatch('orderDetailsByRef', this.$route.params.ref).then(() => {
            this.$store.dispatch('orderTrackingDetails', this.order.id).then(() => {
                this.loaded = true
            })
        }).catch(error => {
            this.$store.commit('setAlert', {
                'color': 'danger', 
                'message': this.$t(error.response.data.message)
            })
            this.$router.push('/account/orders/list')
        })
    },
    methods: {
        printInvoice() {
            window.print()
        }
    },
    computed: {
        ...mapGetters(['productPrice', 'displayPrice']),
        ...mapState({
            order: state => state.order.orderFromDb,
            orderTrackingDetails: state => state.order.orderTrackingDetails,
            storeConfig: state => state.setting.storeConfig,
            profile: state => state.customer.profile,
        }),
        orderAddress() {
            if(this.order && this.order.addresses && this.order.addresses.length > 0) {
                return {
                    shipping: this.order.addresses.find(address => address.address_type === 'shipping'),
                    billing: this.order.addresses.find(address => address.address_type === 'billing'),
                }
            }
        },
        shippingAddress() {
            return this.order.addresses.find(addr => addr.address_type === 'shipping')
        },
        billingAddress() {
            const address = this.order.addresses.find(addr => addr.address_type === 'billing')
            if(address === undefined) {
                return this.shippingAddress
            }
            return address
        }
    }
}
</script>