<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useOrderStore } from '@/stores/order';
import { useAuthCustomerStore } from '@/stores/auth/customer';
import { useCartStore } from '@/stores/cart';
import { useRedirect } from '@/composables/useRedirect';

const route = useRoute();
const orderStore = useOrderStore();
const authCustomerStore = useAuthCustomerStore();
const cartStore = useCartStore();
const { redirectTo } = useRedirect();

const loaded = ref(false);
const order = ref({});
const shippingAddress = ref({});
const billingAddress = ref({});
onMounted(async () => {

    // Get order details
    await orderStore.fetchOrderDetailsByRef(route.params.ref);
    order.value = orderStore.retrieveOrder;

    // Set shipping and billing address
    shippingAddress.value = order.value.addresses.find(addr => addr.address_type === `shipping`)
    billingAddress.value = order.value.addresses.find(addr => addr.address_type === `billing`) || shippingAddress.value

    // Reset cart items
    cartStore.reset();

    loaded.value = true;
});

const upgrading = ref(false);
const upgradeGuestToAccount = async () => {
    await authCustomerStore.upgradeGuestToAccount();
    upgrading.value = true;
}

</script>
<template>
    <div>
        <div v-if="loaded" class="flex min-h-full flex-1 flex-col justify-center px-4 sm:px-6 py-8 sm:py-12 lg:px-8">
             <div class="text-center flex flex-col gap-3">
                <h2 class="text-center text-2xl sm:text-3xl font-bold tracking-tight text-gray-900">
                    {{ $t('Thank you for your order!') }}
                </h2>
                <p class="text-sm sm:text-base">
                    {{ $t('The order confirmation email with details of your order and a link to track its progress has been sent to your email address.') }}
                </p>
                <div class="text-green-600 font-bold text-base sm:text-lg">
                    {{ $t('YOUR ORDER # IS:') }} {{ order.id }}
                </div>
            </div>

            <!-- Main grid: stack on mobile -->
            <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Addresses -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="font-bold mb-2">{{ $t('Shipping Address') }}</p>
                        <DisplayAddress :address="shippingAddress" />
                        <br>
                        <p class="font-bold">{{ $t('Shipping Method') }}</p>
                        <p class="text-sm sm:text-base break-words">{{ $t(order.shipping_method) }}</p>
                    </div>
                    <div>
                        <p class="font-bold mb-2">{{ $t('Billing Address') }}</p>
                        <DisplayAddress :address="billingAddress" />
                        <br>
                        <p class="font-bold">{{ $t('Payment Method') }}</p>
                        <p class="text-sm sm:text-base break-words">{{ $t(order.payment_method) }}</p>
                    </div>
                </div>

                <!-- Table + actions (make container scrollable on mobile) -->
				<div>
					<div class="overflow-x-auto">
						<table class="min-w-[720px] w-full table-auto border-collapse border border-gray-300 mt-4">
							<thead>
								<tr class="bg-gray-200">
									<th class="px-4 py-2 text-left text-xs sm:text-sm font-medium text-gray-700 border-b text-left">
										{{ $t('Name') }}
									</th>
									<th class="px-4 py-2 text-left text-xs sm:text-sm font-medium text-gray-700 border-b text-center">
										{{ $t('Quantity') }}
									</th>
									<th class="px-4 py-2 text-left text-xs sm:text-sm font-medium text-gray-700 border-b text-center">
										{{ $t('Unit') }}
									</th>
									<th class="px-4 py-2 text-left text-xs sm:text-sm font-medium text-gray-700 border-b text-right">
										{{ $t('Total') }}
									</th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="item in order.items" :key="item.id" class="odd:bg-white even:bg-gray-50">
									<td class="px-4 py-2 align-top">
										<div class="mb-2">
											<span class="font-medium">{{ item.name }}</span>
											<span v-if="item.sku" class="ms-2 text-gray-500 text-xs">#{{ item.sku }}</span>
										</div>
										
										<!-- Used to hook into the account product title -->
										<template v-for="(component, index) in $pluginStorefrontHooks['account_product_title']" :key="index">
											<component :is="component" :product="item"></component>
										</template>
									</td>
									<td class="text-center px-4 py-2 whitespace-nowrap">{{ item.qty }}</td>
									<td class="text-center px-4 py-2 whitespace-nowrap">
										<PriceByCurrencyCode :price="+item.price" :currency-code="order.currency" />
									</td>
									<td class="text-end px-4 py-2 whitespace-nowrap">
										<PriceByCurrencyCode :price="+(item.price*item.qty)" :currency-code="order.currency" />
									</td>
								</tr>
							</tbody>
							<tfoot class="h6 text-sm">
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
									<td class="py-2 text-right font-semibold" colspan="3">{{ $t('Total:') }}</td>
									<td class="px-4 py-2 text-right font-semibold">
										<PriceByCurrencyCode :price="+order.order_total" :currency-code="order.currency" />
									</td>
								</tr>
							</tfoot>
						</table>
					</div>
					
					<!-- Actions -->
					<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center mt-4">
						<div>
							<LocalizedLink to="/" class="block text-center bg-blue-600 text-white py-2.5 px-4 rounded-md shadow-sm hover:bg-blue-700">
								{{ $t('Continue Shopping') }}
							</LocalizedLink>
						</div>
						<div class="flex flex-col sm:flex-row sm:justify-end sm:space-x-4 gap-3 items-center">
							<LocalizedLink to="/account/order/list" class="w-full sm:w-auto text-center bg-blue-600 text-white py-2.5 px-4 rounded-md shadow-sm hover:bg-blue-700">
								{{ $t('My Account') }}
							</LocalizedLink>
							<LocalizedLink to="/logout" class="text-blue-600 hover:underline">
								{{ $t('Logout') }}
							</LocalizedLink>
						</div>
					</div>
				</div>
            </div>

            <!-- Status block full on mobile -->
            <div class="w-full lg:w-2/4 overflow-x-auto">
                <div class="text-lg sm:text-xl mt-6 mb-3 font-semibold">{{ $t('Status History & Comments') }}</div>
                <table v-if="order.comments && order.comments.length > 0" class="min-w-[560px] w-full border border-gray-300 text-sm text-left">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border-b border-gray-300 px-4 py-2">{{ $t('Date') }}</th>
                            <th class="border-b border-gray-300 px-4 py-2">{{ $t('Status') }}</th>
                            <th class="border-b border-gray-300 px-4 py-2">{{ $t('Note') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="comment in order.comments" :key="comment.id" :class="`${comment.admin !== `customer` ? `no-print` : `printable`}`" class="odd:bg-white even:bg-gray-50">
                            <td class="border-b border-gray-300 px-4 py-2 whitespace-nowrap">{{ comment.created_at }}</td>
                            <td class="border-b border-gray-300 px-4 py-2 whitespace-nowrap">{{ comment.status_text }}</td>
                            <td class="border-b border-gray-300 px-4 py-2">{{ comment.note }}</td>
                        </tr>
                    </tbody>
                </table>
                <p v-else class="text-sm text-gray-600">{{ $t('There is no any comment at this time') }}</p>
            </div>

             <div v-if="!authCustomerStore.isRegisteredCustomer" class="flex flex-col text-center gap-4 mt-6 sm:mt-8">
                <p class="text-sm sm:text-base">
                    {{ $t('To receive more offers and promotional emails, we suggest you create an account by clicking the "Create an account" button below. You will receive an email containing the password.') }}
                </p>
                <div>
                    <button
                        :disabled="upgrading"
                        class="w-full sm:w-auto px-6 py-3 text-white text-base sm:text-lg font-medium bg-blue-600 hover:bg-blue-700 disabled:bg-blue-300 rounded-lg transition-colors"
                        @click.stop="upgradeGuestToAccount"
                    >
                        {{ $t('Create an account') }}
                    </button>
                </div>
            </div>
        </div>

        <div v-else class="py-12 flex items-center justify-center">
            <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-blue-500"></div>
        </div>
    </div>
</template>
