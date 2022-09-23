<template>
    <div v-if="loadedOrders" class="card">
        <div class="card-header fw-bold">{{$t("Recent Orders")}}</div>
        <div class="card-body">
            <template v-if="noOrder === true">
                <p>{{$t("You haven't ordered any products yet.")}}</p>
            </template>
            <template v-else>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>{{ $t('ID') }}</th>
                                <th class="text-center">{{ $t('Products') }}</th>
                                <th class="text-center">{{ $t('Total') }}</th>
                                <th class="text-center">{{ $t('Status') }}</th>
                                <th class="text-center">{{ $t('Payment / Shipping') }}</th>
                                <th class="text-center">{{ $t('Date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="order in orders" :key="order.id" @click.stop="$router.push(`/account/orders/details/${order.reference}`)" class="cursor-pointer">
                                <td>{{ order.id }}</td>
                                <td class="text-center">{{order.items.length}}</td>
                                <td class="text-center fw-bold text-primary">
                                    <display-price-with-currency :price="order.order_total" :currency="order.currency"></display-price-with-currency>
                                </td>
                                <td class="text-center">{{order.status_text}}</td>
                                <td class="text-center">
                                    <p class="text-primary">{{ $t(order.payment_method) }}</p>
                                    <p class="text-secondary">{{ order.shipping_method }}</p>
                                </td>
                                <td class="text-center">{{ order.purchased_at }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </div>
    </div>
    <div v-else class="card">
        <div class="card-header fw-bold">{{$t("Recent Orders")}}</div>
        <div class="card-body">
            <loading-content></loading-content>
        </div>
    </div>
    
    <!-- Used to hook into the account order list. -->
    <template v-for="(component, index) in $pluginStorefrontHooks['account_order_list']" :key="index">
        <component :is="component" :orders="orders" :loaded="loadedOrders"></component>
    </template>

</template>

<script>
import DisplayPriceWithCurrency from '@theme/storefront/templates/currency/DisplayPriceWithCurrency'
import LoadingContent from '@theme/storefront/templates/account/LoadingContent'
import { mapGetters, mapState } from 'vuex'
export default {
    data: () => ({
        loadedOrders: false,
        noOrder: false
    }),
    components: {
        DisplayPriceWithCurrency, LoadingContent
    },
    created() {
        this.$store.dispatch('accountOrders').then(() => {
            this.loadedOrders = true
            if(this.orders.length === 0) {
                this.noOrder = true
            }
        })
    },
    computed: {
        ...mapGetters(['displayPrice']),
        ...mapState({
            profile: state => state.customer.profile,
            orders: state => state.customer.orders,
        }),
    }
}
</script>