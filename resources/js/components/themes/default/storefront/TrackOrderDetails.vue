<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useToast } from 'vue-toastification';
import { useOrderStore } from '@/stores/order';

const route = useRoute();
const { t } = useI18n();
const toast = useToast();
const orderStore = useOrderStore();

// Make `loaded` reactive
const order = ref({});
const orderTrackingInfo = ref({});
const shippingAddress = ref({});
const billingAddress = ref({});
const loaded = ref(false);
onMounted(async () => {
    await orderStore.fetchOrderDetailsByRef(route.params.ref)

    // Set order
    order.value = orderStore.retrieveOrder

    // Set shipping and billing address
    shippingAddress.value = order.value.addresses.find(addr => addr.address_type === `shipping`)
    billingAddress.value = order.value.addresses.find(addr => addr.address_type === `billing`) || shippingAddress.value

    // Get the tracking information
    orderTrackingInfo.value = await orderStore.fetchTrackingDetailsByRef(route.params.ref)

    // Update loaded
    loaded.value = true
})

function printInvoice() {
    window.print();
}
</script>

<template>
    <div>
        <MetaTags />
        <div class="flex min-h-full flex-1 flex-col justify-center px-6 py-12 lg:px-8">
            <div v-if="loaded" class="w-9/12 mx-auto">
                <div class="rounded overflow-hidden w-full border p-4">
                    <h4 class="mb-4 text-xl">{{ $t('Order ID') }}# {{ order.id }}</h4>
                    <div class="max-w-4xl mx-auto mt-4">
                        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4" role="alert">
                            <div class="flex items-start">
                                <div>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-truck mr-3 mt-1" viewBox="0 0 16 16">
                                        <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5v-7zm1.294 7.456A1.999 1.999 0 0 1 4.732 11h5.536a2.01 2.01 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456zM12 10a2 2 0 0 1 1.732 1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12v4zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                                    </svg>
                                </div>
                                <div>{{ orderTrackingInfo?.trackdetails.date }}: {{ orderTrackingInfo?.trackdetails.message }}</div>
                            </div>
                        </div>
                    </div>
                    <table class="min-w-full table-auto border-collapse border border-gray-300 mt-4">
                        <thead>
                            <tr class="bg-gray-200">
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 border-b text-left">{{ $t('Name') }}</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 border-b text-center">{{ $t('Quantity') }}</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 border-b text-center">{{ $t('Unit') }}</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 border-b text-right">{{ $t('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in order.items" :key="item.id">
                                <td class="px-4 py-2">
                                    <div class="mb-2">
                                        <span>{{ item.name }}</span>
                                        <span v-if="item.sku" class="ms-2">#{{ item.sku }}</span>
                                    </div>
                                    
                                    <!-- Used to hook into the account product title -->
                                    <template v-for="(component, index) in $pluginStorefrontHooks['account_product_title']" :key="index">
                                        <component :is="component" :product="item"></component>
                                    </template>
                                </td>
                                <td class="text-center px-4 py-2">{{ item.qty }}</td>
                                <td class="text-center px-4 py-2">
                                    <PriceByCurrencyCode :price="+item.price" :currency-code="order.currency" />
                                </td>
                                <td class="text-end px-4 py-2">
                                    <PriceByCurrencyCode :price="+(item.price*item.qty)" :currency-code="order.currency" />
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="h6">
                            <tr>
                                <td class="py-2 text-right" colspan="3">{{ $t('Subtotal:') }}</td>
                                <td class="px-4 py-2 text-right">
                                    <PriceByCurrencyCode :price="+order.subtotal" :currency-code="order.currency" />
                                </td>
                            </tr>
                            <tr>
                                <td class="py-2 text-right" colspan="3">{{ $t('Shipping') }} ({{order.shipping_method}}):</td>
                                <td class="px-4 py-2 text-right">
                                    <PriceByCurrencyCode :price="+order.shipping_amount" :currency-code="order.currency" />
                                </td>
                            </tr>
                            <tr v-if="zucConfig.product_price_with_tax === 'n' && order.order_tax > 0">
                                <td class="py-2 text-right" colspan="3">{{ $t('Tax:') }}</td>
                                <td class="px-4 py-2 text-right">
                                    <PriceByCurrencyCode :price="+order.order_tax" :currency-code="order.currency" />
                                </td>
                            </tr>
                            <template v-if="order.discounts && order.discounts.length > 0">
                                <tr v-for="discount in order.discounts" :key="discount.id">
                                    <td class="py-2 text-right" colspan="3">{{ $t(discount.name) }}</td>
                                    <td class="px-4 py-2 text-right">
                                        <span>-</span>
                                        <PriceByCurrencyCode :price="+discount.amount" :currency-code="order.currency" />
                                    </td>
                                </tr>
                            </template>
                            <tr>
                                <td class="py-2 text-right" colspan="3">{{ $t('Total:') }}</td>
                                <td class="px-4 py-2 text-right">
                                    <PriceByCurrencyCode :price="+order.order_total" :currency-code="order.currency" />
                                </td>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="mt-4 text-xl">{{ $t('Delivery & Shipping') }}</div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="mb-3 mt-2 text-lg">{{ $t('Delivery Address') }}</div>
                            <DisplayAddress :address="shippingAddress" />
                            <div class="h6 mt-3 mb-3">{{ $t('Shipping Method') }}</div>
                            <p>{{order.shipping_method}}</p>
                        </div>
                        <div>
                            <div class="mb-3 mt-2 text-lg">{{ $t('Billing Address') }}</div>
                            <DisplayAddress :address="billingAddress" />
                            <div class="h6 mt-3 mb-3">{{ $t('Payment Method') }}</div>
                            <p>{{order.payment_method}}</p>
                        </div>
                    </div>

                    <div class="text-xl mt-4 mb-3">{{ $t('Status History & Comments') }}</div>
                    <table v-if="order.comments && order.comments.length > 0" class="min-w-full border border-gray-300 text-sm text-left">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border-b border-gray-300 px-4 py-2">{{ $t('Date') }}</th>
                                <th class="border-b border-gray-300 px-4 py-2">{{ $t('Status') }}</th>
                                <th class="border-b border-gray-300 px-4 py-2">{{ $t('Note') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="comment in order.comments" :key="comment.id" :class="`${comment.admin !== `customer` ? `no-print` : `printable`}`" class="odd:bg-white even:bg-gray-50">
                                <td class="border-b border-gray-300 px-4 py-2">{{ comment.created_at }}</td>
                                <td class="border-b border-gray-300 px-4 py-2">{{ comment.status_text }}</td>
                                <td class="border-b border-gray-300 px-4 py-2">{{ comment.note }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <p v-else>{{ $t('There is no any comment at this time') }}</p>

                    <div class="grid grid-cols-2 gap-4 mt-6">
                        <div>
                            <LocalizedLink to="/track-order/form" class="rounded-md bg-indigo-600 px-3 py-2 text-sm/6 font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                {{ $t('Back to Track Order Form') }}
                            </LocalizedLink>
                        </div>
                        <div class="text-right">
                            <a href="#" @click.stop="printInvoice" class="rounded-md bg-indigo-600 px-3 py-2 text-sm/6 font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                {{ $t('Print Invoice') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else>
                <div class="animate-spin rounded-full h-8 w-8 border-t-4 border-blue-500"></div>
            </div>
        </div>
    </div>
</template>