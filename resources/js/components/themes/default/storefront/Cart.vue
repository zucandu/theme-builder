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
	<div class="container mx-auto px-4">
		<template v-if="cartStore.numberOfItems > 0">
			<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
							
				<!-- Left - Cart Items -->
				<div class="lg:col-span-2">
					<h2 class="text-2xl font-semibold mb-6">{{ $t('Shopping Cart') }}</h2>
					
					<p v-if="!!cartStore.hasOutOfStock || !!cartStore.hasMaxQty" class="text-red-600">
						{{ $t('Some items in your shopping cart currently do not have enough stock. Please make adjustments before continuing to checkout.') }}
					</p>
					
					<div class="space-y-6">
					
						<!-- Product Item -->
						<div v-for="(item, index) in cartStore.items" :key="item.id" :class="`flex md:flex-col md:flex-row gap-4 ${index > 0 ? `border-t pt-6` : ``}`">
						
							<!-- Image -->
							<img :src="`${zucConfig.store_url}/storage/${item.images[0].src}`" :alt="translateItemField(item, 'name', $i18n.locale)" class="w-24 h-24 md:w-32 md:h-32 object-contain border p-2 rounded-lg" />

							<!-- Info -->
							<div class="flex-1 space-y-2 text-sm">
								<div class="flex justify-between items-start">
									<div>
										<p class="font-semibold text-base">
											<LocalizedLink :to="`/product/${translateItemField(item, 'slug', $i18n.locale)}`" class="underline">
												{{ translateItemField(item, 'name', $i18n.locale) }} 
											</LocalizedLink>
											<span class="text-gray-400 ml-2">({{ item.sku }})</span>
										</p>
										<p class="text-gray-600 text-sm">
											<div class="hidden md:block">
												{{ $t('Unit Price:') }} 
												<PriceConverter :product="item" :qty="1" />
											</div>
											<div class="block md:hidden">
												<PriceConverter :product="item" :qty="item.qty" />
											</div>
										</p>
										<p v-if="item.qty > item.inventory" class="text-sm text-red-600 !mb-1">{{ $t('Current inventory in stock:') }} {{ item.inventory }}</p>
										<p v-if="item.max_qty > 0 && item.qty > item.max_qty" class="text-sm text-red-600 !mb-1">{{ $t('Max quantity:') }} {{ item.max_qty }}</p>
										<p v-if="+item.meta?.is_oversized === 1" class="text-yellow-600">Oversized product, not available for free shipping</p>
										
									</div>
									<div class="font-semibold text-right whitespace-nowrap hidden md:block">
										<PriceConverter :product="item" :qty="item.qty" />
									</div>
								</div>

								<div class="flex items-center gap-4 mt-2">
									<!-- Quantity control -->
									<div class="flex items-center border rounded px-2">
										<button @click="decrementQty(item)" class="text-lg px-2 cursor-pointer">−</button>
										<input @input="setProductQtyInput(item, $event.target.value)" :value="item.qty" type="text" class="w-12 text-center focus:outline-none" />
										<button @click="incrementQty(item)" class="text-lg px-2 cursor-pointer">+</button>
									</div>

									<!-- Delete & Wishlist -->
									<div class="flex gap-2">
										<button @click.stop="removeProduct(item.id)" class="ml-3 p-1 text-xs font-medium cursor-pointer">
											<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
												<path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
												<path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
											</svg>
										</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Right - Summary -->
				<div class="cart-right">
					<div class="border rounded-lg p-6 bg-gray-50">
						<h2 class="text-xl font-semibold mb-4">Summary</h2>
						<div class="flex justify-between mb-2 text-sm">
							<span>Subtotal</span>
							<PriceDisplay :price="cartStore.total" class="inline font-bold" />
						</div>
						<div class="flex justify-between items-start mb-4 text-sm">
							<div>
								<div class="text-gray-700">Estimated Delivery & Handling:</div>
								<button @click="toggleShippingEstimator" class="text-blue-600 text-xs underline hover:text-blue-800 cursor-pointer">
									{{ isShow ? $t('Hide details') : $t('Show details') }}
								</button>
								<div v-show="isShow">
									<ShippingEstimator
										:subtotal="+cartStore.total"
										@update:shippingMethods="handleShippingMethods"
										@loading="loadingShipping = $event"
									/>
								</div>
							</div>
							
							<span class="font-medium text-green-600">
								<svg v-if="loadingShipping" class="size-4 animate-spin text-blue-600 inline mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
								<template v-if="shippingCost !== null && shippingCost !== undefined">
									<template v-if="shippingCost > 0">
										<PriceDisplay :price="shippingCost" class="inline font-bold" />
									</template>
									<template v-else>
										{{ $t(`Free`) }}
									</template>
								</template>
								<template v-else>
									{{ $t(`N/A`) }}
								</template>
							</span>
						</div>
						<div class="flex justify-between text-base font-semibold border-t pt-4">
							<span>Total</span>
							<PriceDisplay :price="totalWithShipping" class="inline font-bold" />
						</div>
						<div class="mt-6 space-y-3">
							<LocalizedLink to="/checkout" class="w-full py-3 bg-black text-white rounded-full text-sm hover:bg-gray-800 transition shadow text-center block">{{ $t('Go to the checkout') }}</LocalizedLink>
						</div>
					</div>
				</div>
			</div>
			
			<div v-if="viewedProducts.length > 0" class="grid grid-cols-6 gap-4 mt-12">
				<div class="text-xl font-bold col-span-6">{{ $t(`Recently viewed`) }}</div>
				<div v-for="(item, index) in viewedProducts" :key="item.id" class="col-span-3 lg:col-span-1 rounded-lg p-4 items-center relative">
					<ProductCard :product="item" :index="index" />
				</div>
			</div>
		</template>
		<div v-else class="text-2xl text-center col-span-3 py-12">{{ $t('Your cart is empty!') }}</div>
	</div>
</template>