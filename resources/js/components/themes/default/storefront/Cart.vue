<script setup>
import ShippingEstimator from '@theme/storefront/components/cart/ShippingEstimator.vue'
import ProductCard from '@theme/storefront/components/product/ProductCard.vue'
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useToast } from 'vue-toastification';
import { useHelpers } from '@/composables/useHelpers';

import { useAuthCustomerStore } from '@/stores/auth/customer';
import { useProductStore } from '@/stores/catalog/product';
import { useCartStore } from '@/stores/cart';

const { t, locale } = useI18n();
const toast = useToast();
const productStore = useProductStore();
const cartStore = useCartStore();
const { translateItemField, translateItemObj } = useHelpers();

const setProductQtyInput = async (product, val) => {
    const formdata = { 
        ...product, 
        ...{ 
            id: product.id, 
            cart_quantity: val
        }
    };
    await cartStore.updateQuantity(formdata)
        .catch(error => {
            toast.error(t(error.response.data.message));
        })
}

const incrementQty = (item) => {
	setProductQtyInput(item, item.qty + 1);
};

const decrementQty = (item) => {
    if (item.qty > 1) {
        setProductQtyInput(item, item.qty - 1);
    }
};

const removeProduct = async (id) => {
    await cartStore.removeProduct(id)
}

const loadingShipping = ref(true);
const shippingCost = ref(null);
const handleShippingMethods = (obj) => {

    if (!obj || !Array.isArray(obj)) {
        return;
    }
	
    const allCosts = [];

    obj.forEach(item => {
        if (Array.isArray(item.methods)) {
            item.methods.forEach(m => {
                if (typeof m.cost === 'number') {
                    allCosts.push(m.cost);
                }
            });
        }
    });

    if (allCosts.length > 0) {
        shippingCost.value = Math.min(...allCosts);
    } else {
        shippingCost.value = 0;
    }
	loadingShipping.value = false;
};

const totalWithShipping = computed(() => {
    return shippingCost.value + cartStore.total;
});

const isShow = ref(false);
const toggleShippingEstimator = () => {
    isShow.value = !isShow.value;
};

// Get viewed products
const viewedProducts = ref([]);
onMounted(() => {
	const viewedIds = JSON.parse(localStorage.getItem(`viewedProducts`)) || [];
	if(viewedIds.length > 0) {
		productStore.fetchProductsByIds(viewedIds).then(products => viewedProducts.value = products);
	}
});

</script>

<template>
	<div class="container mx-auto px-4 py-8 max-w-7xl">
		<template v-if="cartStore.numberOfItems > 0">
			<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
							
				<!-- Left - Cart Items -->
				<div class="lg:col-span-8">
					<div class="flex items-center justify-between mb-6">
						<h1 class="text-3xl font-bold text-gray-900">{{ $t('Shopping Cart') }}</h1>
						<span class="text-gray-500">{{ cartStore.numberOfItems }} {{ $t('items') }}</span>
					</div>
					
					<div v-if="!!cartStore.hasOutOfStock || !!cartStore.hasMaxQty" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 flex items-start">
						<svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
						<div>
							<p class="font-medium">{{ $t('Attention needed') }}</p>
							<p class="text-sm mt-1">{{ $t('Some items in your shopping cart currently do not have enough stock. Please make adjustments before continuing to checkout.') }}</p>
						</div>
					</div>
					
					<div class="space-y-4">
					
						<!-- Product Item -->
						<div v-for="(item, index) in cartStore.items" :key="item.id" class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex gap-6 transition-shadow hover:shadow-md">
						
							<!-- Image -->
							<div class="w-24 h-24 sm:w-32 sm:h-32 flex-shrink-0 bg-gray-50 rounded-lg p-2 border border-gray-100 overflow-hidden">
								<img :src="`${zucConfig.store_url}/storage/${item.images[0].src}`" :alt="translateItemField(item, 'name', $i18n.locale)" class="w-full h-full object-contain mix-blend-multiply" />
							</div>

							<!-- Info -->
							<div class="flex-1 flex flex-col justify-between">
								<div class="flex justify-between items-start gap-4">
									<div>
										<h3 class="font-bold text-lg text-gray-900 leading-snug">
											<LocalizedLink :to="`/product/${translateItemField(item, 'slug', $i18n.locale)}`" class="hover:text-blue-600 transition-colors">
												{{ translateItemField(item, 'name', $i18n.locale) }} 
											</LocalizedLink>
										</h3>
										<p class="text-xs text-gray-500 mt-1 uppercase tracking-wide font-medium">SKU: {{ item.sku }}</p>
										
										<!-- Unit Price Mobile -->
										<div class="block sm:hidden mt-2 font-semibold text-gray-900">
											<PriceConverter :product="item" :qty="item.qty" />
										</div>

										<!-- Warnings -->
										<div class="mt-2 text-sm space-y-1">
											<p v-if="item.qty > item.inventory" class="text-red-600 font-medium flex items-center">
												<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
												{{ $t('Stock available:') }} {{ item.inventory }}
											</p>
											<p v-if="item.max_qty > 0 && item.qty > item.max_qty" class="text-red-600 font-medium">
												{{ $t('Max limit:') }} {{ item.max_qty }}
											</p>
											<p v-if="+item.meta?.is_oversized === 1" class="text-amber-600 flex items-center">
												<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
												Oversized item
											</p>
										</div>
									</div>

									<!-- Unit Price Desktop -->
									<div class="hidden sm:block text-right">
										<div class="text-lg font-bold text-gray-900">
											<PriceConverter :product="item" :qty="item.qty" />
										</div>
										<p class="text-xs text-gray-400 mt-1">{{ $t('each') }}</p>
									</div>
								</div>

								<div class="flex items-center justify-between mt-4">
									<!-- Quantity control -->
									<div class="flex items-center bg-gray-50 rounded-lg border border-gray-200">
										<button @click="decrementQty(item)" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-gray-900 hover:bg-gray-200 rounded-l-lg transition-colors disabled:opacity-50" :disabled="item.qty <= 1">
											<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
										</button>
										<input @input="setProductQtyInput(item, $event.target.value)" :value="item.qty" type="text" class="w-10 text-center bg-transparent text-sm font-semibold text-gray-900 focus:outline-none" />
										<button @click="incrementQty(item)" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-gray-900 hover:bg-gray-200 rounded-r-lg transition-colors">
											<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
										</button>
									</div>

									<!-- Actions -->
									<button @click.stop="removeProduct(item.id)" class="text-sm font-medium text-gray-400 hover:text-red-500 flex items-center transition-colors group px-2 py-1">
										<svg class="w-4 h-4 mr-1.5 group-hover:stroke-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
										<span class="hidden sm:inline">{{ $t('Remove') }}</span>
									</button>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Right - Summary -->
				<div class="lg:col-span-4 lg:sticky lg:top-8">
					<div class="bg-gray-50 rounded-2xl p-6 shadow-sm border border-gray-100">
						<h2 class="text-xl font-bold text-gray-900 mb-6">{{ $t('Order Summary') }}</h2>
						
						<div class="space-y-4 text-sm text-gray-600">
							<div class="flex justify-between">
								<span>{{ $t('Subtotal') }}</span>
								<PriceDisplay :price="cartStore.total" class="font-semibold text-gray-900" />
							</div>
							
							<div class="pt-4 border-t border-gray-200">
								<div class="flex justify-between items-center mb-2">
									<span class="text-gray-900 font-medium">{{ $t('Shipping') }}</span>
									<span v-if="loadingShipping" class="flex items-center text-blue-600 text-xs">
										<svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
										{{ $t('Calculating...') }}
									</span>
									<span v-else class="font-semibold text-gray-900">
										<template v-if="shippingCost !== null && shippingCost !== undefined">
											<PriceDisplay v-if="shippingCost > 0" :price="shippingCost" />
											<span v-else class="text-green-600">{{ $t('Free') }}</span>
										</template>
										<span v-else class="text-gray-400 italic">{{ $t('Not calculated') }}</span>
									</span>
								</div>
								
								<!-- Shipping Estimator Toggle -->
								<div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
									<button @click="toggleShippingEstimator" class="w-full flex justify-between items-center px-4 py-3 text-left text-xs font-medium text-gray-700 bg-gray-50 hover:bg-gray-100 transition-colors">
										<span>{{ $t('Calculate Shipping') }}</span>
										<svg :class="{'rotate-180': isShow}" class="w-4 h-4 transform transition-transform text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
									</button>
									<div v-show="isShow" class="p-4 border-t border-gray-200">
										<ShippingEstimator
											:subtotal="+cartStore.total"
											@update:shippingMethods="handleShippingMethods"
											@loading="loadingShipping = $event"
										/>
									</div>
								</div>
							</div>
						</div>

						<div class="mt-6 pt-6 border-t border-dashed border-gray-300">
							<div class="flex justify-between items-center text-lg font-bold text-gray-900">
								<span>{{ $t('Total') }}</span>
								<PriceDisplay :price="totalWithShipping" />
							</div>
							<p class="text-xs text-gray-500 mt-1 text-right">{{ $t('Tax included. Shipping calculated at checkout.') }}</p>
						</div>

						<LocalizedLink to="/checkout" class="mt-8 w-full block bg-black text-white text-center font-bold py-4 rounded-full shadow-lg hover:bg-gray-800 hover:shadow-xl transform transition-all active:scale-95">
							{{ $t('Proceed to Checkout') }}
						</LocalizedLink>
						
						<div class="mt-4 flex justify-center space-x-2 text-gray-400">
							<!-- Minimal trust badges/icons could go here -->
							<svg class="h-6 w-auto grayscale opacity-50" viewBox="0 0 24 24" fill="currentColor"><!-- Placeholder for card icon --></svg>
						</div>
					</div>
				</div>
			</div>
			
			<!-- Recently Viewed -->
			<div v-if="viewedProducts.length > 0" class="mt-16 pt-16 border-t border-gray-200">
				<h2 class="text-2xl font-bold text-gray-900 mb-8">{{ $t('Recently viewed') }}</h2>
				<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
					<div v-for="(item, index) in viewedProducts" :key="item.id" class="group">
						<ProductCard :product="item" :index="index" />
					</div>
				</div>
			</div>
		</template>

		<!-- Empty State -->
		<div v-else class="min-h-[50vh] flex flex-col items-center justify-center text-center py-12">
			<div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mb-6">
				<svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
			</div>
			<h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $t('Your cart is empty') }}</h2>
			<p class="text-gray-500 mb-8 max-w-sm">{{ $t('Looks like you haven\'t added any items to the cart yet.') }}</p>
			<LocalizedLink to="/" class="px-8 py-3 bg-black text-white rounded-full font-medium hover:bg-gray-800 transition shadow-lg">
				{{ $t('Start Shopping') }}
			</LocalizedLink>
		</div>
	</div>
</template>