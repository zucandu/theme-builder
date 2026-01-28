<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';

import { useI18n } from 'vue-i18n';
import { useToast } from 'vue-toastification';

import { useOrderStore } from '@/stores/order';
import { useRedirect } from '@/composables/useRedirect';

const route = useRoute();
const toast = useToast();
const { t } = useI18n();

const orderStore = useOrderStore();
const { redirectTo } = useRedirect();

const loaded = ref(false);
const order = ref({});
const orderTrackingInfo = ref({});
const shippingAddress = ref({});
const billingAddress = ref({});
onMounted(async () => {

    // Get order details
    await orderStore.fetchOrderDetailsByRef(route.params.ref);
    order.value = orderStore.retrieveOrder;

    // Set shipping and billing address
    shippingAddress.value = order.value.addresses.find(addr => addr.address_type === `shipping`)
    billingAddress.value = order.value.addresses.find(addr => addr.address_type === `billing`) || shippingAddress.value

    // Get the tracking information
    orderTrackingInfo.value = await orderStore.fetchTrackingDetailsByRef(route.params.ref)

    loaded.value = true;
});

function printInvoice() {
    window.print();
}

const cancelOrder = async () => {
	await orderStore.cancelOrder(order.value);
	await orderStore.fetchOrderDetailsByRef(route.params.ref);
    order.value = orderStore.retrieveOrder;
	toast.success(t(`Your order has been successfully canceled`));
}

</script>

<template>
    <div>
        <MetaTags />
        <div class="flex-1 flex flex-col min-h-full rounded overflow-hidden w-full lg:border">
            <div class="lg:px-6 py-4 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-3 border-b border-gray-200 dark:border-gray-700">
					<div class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">
						{{ $t("Order ID#") }} {{ order.id }}
					</div>
					<div class="mt-1 sm:mt-0 text-sm text-gray-500 dark:text-gray-400">
						{{ order.purchased_at }}
					</div>
				</div>
                <div v-if="!loaded" class="animate-spin rounded-full h-8 w-8 border-t-4 border-blue-500"></div>
                <div v-else>
                    <div class="w-full">
						<div
							class="flex items-start gap-3 bg-blue-50 border-l-4 border-blue-500 text-blue-700 dark:bg-blue-900/30 dark:text-blue-200 p-4 rounded-lg"
							role="alert"
						>
							<svg xmlns="http://www.w3.org/2000/svg"
								 class="w-6 h-6 flex-shrink-0 mt-0.5"
								 fill="currentColor"
								 viewBox="0 0 16 16">
								<path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5v-7zm1.294 7.456A1.999 1.999 0 0 1 4.732 11h5.536a2.01 2.01 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456zM12 10a2 2 0 0 1 1.732 1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12v4zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
							</svg>
							<div>
								<p class="text-sm font-medium">{{ orderTrackingInfo?.trackdetails.date }}</p>
								<p class="text-sm">{{ orderTrackingInfo?.trackdetails.message }}</p>
							</div>
						</div>
					</div>
					<div class="mt-4 overflow-x-auto">
						<table class="w-full border-separate border-spacing-0 lg:table">
							<thead class="hidden lg:table-header-group bg-gray-50 dark:bg-gray-800 sticky top-0 z-10">
								<tr class="border-b border-gray-200 dark:border-gray-700">
									<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
										{{ $t('Name') }}
									</th>
									<th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
										{{ $t('Quantity') }}
									</th>
									<th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
										{{ $t('Unit') }}
									</th>
									<th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
										{{ $t('Total') }}
									</th>
								</tr>
							</thead>

							<tbody class="lg:table-row-group">
								<tr
									v-for="item in order.items"
									:key="item.id"
									class="block rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 mb-3 lg:mb-0 lg:table-row lg:border-0 lg:bg-transparent"
								>
									<td class="block px-4 py-3 lg:table-cell lg:align-top lg:bg-transparent">
										<div class="mb-1 text-xs font-medium text-gray-500 lg:hidden">
											{{ $t('Name') }}
										</div>

										<div class="text-sm font-medium text-gray-900 dark:text-gray-100">
											{{ item.name }}
											<span v-if="item.sku" class="ml-2 text-xs font-normal text-gray-500">#{{ item.sku }}</span>
										</div>

										<div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
											<template
												v-for="(component, index) in $pluginStorefrontHooks['account_product_title']"
												:key="index"
											>
												<component :is="component" :product="item" />
											</template>
										</div>
									</td>

									<td class="block px-4 py-3 lg:table-cell lg:text-center lg:align-top">
										<div class="mb-1 text-xs font-medium text-gray-500 lg:hidden min-w-16 inline-block">
											{{ $t('Quantity') }}
										</div>
										<span class="inline-block text-sm font-medium text-gray-900 dark:text-gray-100">
											{{ item.qty }}
										</span>
									</td>
									
									<td class="block px-4 py-3 lg:table-cell lg:text-center lg:align-top">
										<div class="mb-1 text-xs font-medium text-gray-500 lg:hidden min-w-16 inline-block">
											{{ $t('Unit') }}
										</div>
										<span class="inline-block text-sm">
											<PriceByCurrencyCode :price="+item.price" :currency-code="order.currency" />
										</span>
									</td>
									
									<td class="block px-4 py-3 lg:table-cell lg:text-right lg:align-top">
										<div class="mb-1 text-xs font-medium text-gray-500 lg:hidden min-w-16 inline-block">
											{{ $t('Total') }}
										</div>
										<span class="inline-block text-sm font-semibold">
											<PriceByCurrencyCode :price="+(item.price * item.qty)" :currency-code="order.currency" />
										</span>
									</td>
								</tr>
							</tbody>

							<tfoot class="block rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4 lg:p-0 lg:rounded-none lg:border-0 lg:bg-transparent lg:table-footer-group">
								<tr class="block lg:table-row">
									<td class="block px-4 py-2 text-right text-sm text-gray-600 dark:text-gray-300 lg:table-cell" colspan="3">
										<span class="hidden lg:inline">{{ $t('Subtotal:') }}</span>
									</td>
									<td class="block px-4 py-2 text-right text-sm font-medium lg:table-cell">
										<span class="lg:hidden inline-block max-w-48">{{ $t('Subtotal:') }}</span>
										<PriceByCurrencyCode :price="+order.subtotal" :currency-code="order.currency" class="lg:min-w-full min-w-20 inline-block" />
									</td>
								</tr>

								<tr class="block lg:table-row">
									<td class="block px-4 py-2 text-right text-sm text-gray-600 dark:text-gray-300 lg:table-cell" colspan="3">
										<span class="hidden lg:inline">{{ $t('Shipping') }} ({{ order.shipping_method }}):</span>
									</td>
									<td class="block px-4 py-2 text-right text-sm font-medium lg:table-cell">
										<div class="flex flex-row items-center justify-between lg:block">
											<span class="lg:hidden inline-block max-w-48">{{ $t('Shipping') }} ({{ order.shipping_method }}):</span>
											<PriceByCurrencyCode :price="+order.shipping_amount" :currency-code="order.currency" class="lg:min-w-full min-w-20 inline-block" />
										</div>
									</td>
								</tr>

								<tr
									v-if="zucConfig.product_price_with_tax === 'n' && order.order_tax > 0"
									class="block lg:table-row"
								>
									<td class="block px-4 py-2 text-right text-sm text-gray-600 dark:text-gray-300 lg:table-cell" colspan="3">
										<span class="hidden lg:inline">{{ $t('Tax:') }}</span>
									</td>
									<td class="block px-4 py-2 text-right text-sm font-medium lg:table-cell">
										<span class="lg:hidden inline-block max-w-48">{{ $t('Tax:') }}</span>
										<PriceByCurrencyCode :price="+order.order_tax" :currency-code="order.currency" class="lg:min-w-full min-w-20 inline-block" />
									</td>
								</tr>

								<template v-if="order.discounts && order.discounts.length > 0">
									<tr
										v-for="discount in order.discounts"
										:key="discount.id"
										class="block lg:table-row"
									>
										<td class="block px-4 py-2 text-right text-sm text-gray-600 dark:text-gray-300 lg:table-cell" colspan="3">
											<span class="hidden lg:inline">{{ $t(discount.name) }}</span>
										</td>
										<td class="block px-4 py-2 text-right text-sm font-medium lg:table-cell">
											<span class="lg:hidden inline-block max-w-48">{{ $t(discount.name) }}</span>
											<div class="lg:min-w-full min-w-20 inline-block">
												<span>-</span>
												<PriceByCurrencyCode :price="+discount.amount" :currency-code="order.currency" />
											</div>
										</td>
									</tr>
								</template>

								<tr class="block border-t border-gray-200 dark:border-gray-700 mt-2 pt-2 lg:mt-0 lg:pt-0 lg:border-0 lg:table-row">
									<td class="block px-4 py-3 text-right text-sm font-semibold text-gray-900 dark:text-gray-100 lg:table-cell" colspan="3">
										<span class="hidden lg:inline">{{ $t('Total:') }}</span>
									</td>
									<td class="block px-4 py-3 text-right text-sm font-semibold lg:table-cell">
										<span class="lg:hidden inline-block max-w-48">{{ $t('Total:') }}</span>
										<PriceByCurrencyCode :price="+order.order_total" :currency-code="order.currency" class="lg:min-w-full min-w-20 inline-block" />
									</td>
								</tr>
							</tfoot>
						</table>
					</div>

                    <div v-if="order.returns && order.returns.length > 0" class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-2 mt-4 rounded-md no-print">
                        <ul class="list-none m-0">
                            <li v-for="item in order.returns" :key="item.product_id"  class="capitalize leading-8">
                                {{ item.resolution }} <strong>{{ item.qty }}x {{ item.name }}</strong> {{ $t('with reason') }} {{ item.reason }} #{{ item.id }}
                            </li>
                        </ul>
                    </div>

                    <!-- Used to hook into the account order details -->
                    <template v-for="(component, index) in $pluginStorefrontHooks['account_order_details']" :key="index">
                        <component :is="component"></component>
                    </template>

                    <!-- Delivery & Shipping -->
					<section class="mt-8">
						<h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white mb-6">
							{{ $t('Delivery & Shipping') }}
						</h2>

						<div class="grid gap-8 lg:grid-cols-2">
							<!-- Delivery -->
							<div>
								<div class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-3">
									{{ $t('Delivery Address') }}
								</div>
								<div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
									<DisplayAddress :address="shippingAddress" />
								</div>

								<div class="mt-5 mb-2 text-sm font-medium text-gray-500 uppercase tracking-wide">
									{{ $t('Shipping Method') }}
								</div>
								<p class="text-base text-gray-900 dark:text-gray-100 font-medium">
									{{ order.shipping_method }}
								</p>
							</div>

							<!-- Billing -->
							<div>
								<div class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-3">
									{{ $t('Billing Address') }}
								</div>
								<div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
									<DisplayAddress :address="billingAddress" />
								</div>

								<div class="mt-5 mb-2 text-sm font-medium text-gray-500 uppercase tracking-wide">
									{{ $t('Payment Method') }}
								</div>
								<p class="text-base text-gray-900 dark:text-gray-100 font-medium">
									{{ order.payment_method }}
								</p>
							</div>
						</div>
					</section>

					<!-- Status History -->
					<section class="mt-10">
						<h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white mb-6">
							{{ $t('Status History & Comments') }}
						</h2>

						<div v-if="order.comments && order.comments.length > 0" class="overflow-x-auto">
							<table class="min-w-full text-sm border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
								<thead class="hidden lg:table-header-group bg-gray-100 dark:bg-gray-800">
									<tr>
										<th class="px-5 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
											{{ $t('Date') }}
										</th>
										<th class="px-5 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
											{{ $t('Status') }}
										</th>
										<th class="px-5 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
											{{ $t('Note') }}
										</th>
									</tr>
								</thead>
								<tbody class="divide-y divide-gray-200 dark:divide-gray-700">
									<tr
										v-for="comment in order.comments"
										:key="comment.id"
										:class="`${comment.admin !== 'customer' ? 'no-print' : 'printable'}`"
										class="block lg:table-row lg:odd:bg-white lg:even:bg-gray-50 dark:lg:odd:bg-gray-900 dark:lg:even:bg-gray-800 mb-4 lg:mb-0"
									>
										<!-- Date -->
										<td class="block px-5 py-3 lg:table-cell">
											<span class="lg:hidden text-xs font-medium text-gray-500 block mb-1">{{ $t('Date') }}</span>
											<span class="text-gray-900 dark:text-gray-100">{{ comment.created_at }}</span>
										</td>
										<!-- Status -->
										<td class="block px-5 py-3 lg:table-cell">
											<span class="lg:hidden text-xs font-medium text-gray-500 block mb-1">{{ $t('Status') }}</span>
											<span class="font-medium text-gray-800 dark:text-gray-200">{{ comment.status_text }}</span>
										</td>
										<!-- Note -->
										<td class="block px-5 py-3 lg:table-cell">
											<span class="lg:hidden text-xs font-medium text-gray-500 block mb-1">{{ $t('Note') }}</span>
											<span class="text-gray-700 dark:text-gray-300">{{ comment.note }}</span>
										</td>
									</tr>
								</tbody>
							</table>
						</div>

						<p v-else class="text-gray-500 dark:text-gray-400 italic">
							{{ $t('There is no any comment at this time') }}
						</p>
					</section>
                    <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 items-center gap-4">
						<div class="order-2 lg:order-1">
							<LocalizedLink
								to="/account/order/list"
								class="inline-flex items-center gap-2 text-sm font-medium
									   w-full sm:w-auto justify-center
									   rounded-lg border border-blue-500 text-blue-600 hover:bg-blue-50
									   focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2
									   px-4 py-2
									   dark:border-blue-400 dark:text-blue-300 dark:hover:bg-blue-950/30 dark:focus-visible:ring-offset-gray-900"
								aria-label="Back to Order List"
							>
								<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
								</svg>
								{{ $t('Back to Order List') }}
							</LocalizedLink>
						</div>
						<div class="order-1 lg:order-2 lg:justify-self-end">
							<div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
								<button
									v-if="[1, 2].includes(+order.status)"
									@click.stop="cancelOrder"
									type="button"
									class="cursor-pointer inline-flex items-center gap-2 text-sm font-medium
										   rounded-lg border border-blue-500 text-blue-600 hover:bg-blue-50
										   focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2
										   px-4 py-2
										   dark:border-blue-400 dark:text-blue-300 dark:hover:bg-blue-950/30 dark:focus-visible:ring-offset-gray-900"
								>
									<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
										<path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6 4.125 2.25 2.25m0 0 2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
									</svg>
									{{ $t('Cancel order') }}
								</button>
								<LocalizedLink
									:to="`/return-exchange/${order.reference}`"
									class="inline-flex items-center gap-2 text-sm font-medium
										   rounded-lg border border-blue-500 text-blue-600 hover:bg-blue-50
										   focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2
										   px-4 py-2
										   dark:border-blue-400 dark:text-blue-300 dark:hover:bg-blue-950/30 dark:focus-visible:ring-offset-gray-900"
								>
									<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
										<path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
									</svg>
									{{ $t('Need return or exchange?') }}
								</LocalizedLink>

								<button
									@click.stop="printInvoice"
									type="button"
									class="cursor-pointer inline-flex items-center gap-2 text-sm font-medium
										   rounded-lg bg-blue-600 text-white hover:bg-blue-700
										   focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2
										   px-4 py-2
										   dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus-visible:ring-offset-gray-900"
								>
									<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
										<path d="M6 7V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v3h1a3 3 0 0 1 3 3v4a1 1 0 0 1-1 1h-2v5a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1v-5H4a1 1 0 0 1-1-1v-4a3 3 0 0 1 3-3h0Zm2 0h8V4H8v3Zm-1 12h10v-4H7v4Z"/>
									</svg>
									{{ $t('Print Invoice') }}
								</button>
							</div>
						</div>
					</div>
                </div>
            </div>
        </div>
    </div>
</template>
