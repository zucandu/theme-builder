<script setup>
import { ref, onMounted } from 'vue';
import { useOrderStore } from '@/stores/order';
import { useRedirect } from '@/composables/useRedirect';

const orderStore = useOrderStore();
const { redirectTo } = useRedirect();

const orders = ref(null);
const loaded = ref(false);

// 
onMounted(async () => {
    const res = await orderStore.retrieveCustomerOrders();
    orders.value = res.orders;
    loaded.value = true;
});

const moveTo = (ref) => {
    redirectTo(`/account/order/${ref}`);
};

</script>

<template>
    <div>
        <div class="flex-1 flex flex-col min-h-full w-full rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
            <div class="px-6 py-6 space-y-8">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-0">
                    {{ $t('Recent Orders') }}
                </h1>
                <div v-if="loaded" class="overflow-x-auto">
					<template v-if="orders.length === 0">
						<p class="text-sm text-gray-600 dark:text-gray-300">
							{{ $t("You haven't ordered any products yet.") }}
						</p>
					</template>

					<table v-else class="min-w-full text-sm border border-gray-200 dark:border-gray-800 rounded-lg overflow-hidden mt-4">
						<thead class="bg-gray-50 dark:bg-gray-800">
							<tr class="text-gray-700 dark:text-gray-200">
								<th class="px-4 py-3 text-left font-semibold whitespace-nowrap">
									<span class="inline sm:hidden">{{ $t('Orders') }}</span>
									<span class="hidden sm:inline">{{ $t('ID') }}</span>
								</th>
								<th class="px-4 py-3 text-left font-semibold whitespace-nowrap hidden sm:table-cell">{{ $t('Products') }}</th>
								<th class="px-4 py-3 text-left font-semibold whitespace-nowrap">{{ $t('Total') }}</th>
								<th class="px-4 py-3 text-left font-semibold whitespace-nowrap">{{ $t('Status') }}</th>
								<th class="px-4 py-3 text-left font-semibold whitespace-nowrap hidden md:table-cell">{{ $t('Payment/Shipping') }}</th>
								<th class="px-4 py-3 text-right font-semibold whitespace-nowrap">{{ $t('Date') }}</th>
							</tr>
						</thead>

						<tbody class="divide-y divide-gray-100 dark:divide-gray-800">
							<tr
								v-for="order in orders"
								:key="order.id"
								@click.stop="moveTo(order.reference)"
								class="hover:bg-gray-50/70 dark:hover:bg-gray-800/50 cursor-pointer align-top"
							>
								<!-- ID + subtext (mobile-only) -->
								<td class="px-4 py-3">
									<div class="text-gray-900 dark:text-gray-100 font-medium">#{{ order.id }}</div>

									<!-- Subtext chỉ hiện trên màn nhỏ: Products + Payment/Shipping -->
									<div class="mt-1 space-y-0.5 sm:hidden">
										<div class="text-xs text-gray-500">
											{{ $t('Products') }}: {{ order.items.length }}
										</div>
										<div class="text-xs">
											<span class="text-blue-600 dark:text-blue-400 truncate block">
												{{ $t(order.payment_method) }}
											</span>
											<span class="text-amber-600 dark:text-amber-400 truncate block">
												{{ order.shipping_method }}
											</span>
										</div>
									</div>
								</td>

								<!-- Products (ẩn trên xs) -->
								<td class="px-4 py-3 text-gray-700 dark:text-gray-300 whitespace-nowrap hidden sm:table-cell">
									{{ order.items.length }}
								</td>

								<!-- Total -->
								<td class="px-4 py-3 text-gray-900 dark:text-gray-100 whitespace-nowrap">
									<PriceByCurrencyCode :price="+order.order_total" :currency-code="order.currency" />
								</td>

								<!-- Status (badge màu nhẹ) -->
								<td class="px-4 py-3 text-gray-700 dark:text-gray-300 whitespace-nowrap">
									{{ order.status_text }}
								</td>

								<!-- Payment/Shipping (ẩn tới md) -->
								<td class="px-4 py-3 hidden md:table-cell">
									<div class="max-w-[18rem]">
										<p class="text-blue-600 dark:text-blue-400 truncate">{{ $t(order.payment_method) }}</p>
										<p class="text-amber-600 dark:text-amber-400 truncate">{{ order.shipping_method }}</p>
									</div>
								</td>

								<!-- Date -->
								<td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300 whitespace-nowrap">
									{{ order.purchased_at }}
								</td>
							</tr>
						</tbody>
					</table>
				</div>

				<!-- Loading spinner -->
				<div v-else class="flex items-center justify-center py-12">
					<span class="animate-spin rounded-full h-8 w-8 border-4 border-gray-200 border-t-blue-500"></span>
				</div>

            </div>
        
            <!-- Used to hook into the account order list. -->
            <template v-for="(component, index) in $pluginStorefrontHooks['account_order_list']" :key="index">
                <component :is="component" :orders="orders" :loaded="loaded"></component>
            </template>
            
        </div>
    </div>
</template>