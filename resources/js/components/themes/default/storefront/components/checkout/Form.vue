<script setup>
import CheckoutAddress from '@theme/storefront/components/checkout/Address.vue'
import CheckoutCartContent from '@theme/storefront/components/checkout/CartContent.vue'
import { ref, onMounted, watch } from 'vue';

import { useOrderStore } from '@/stores/order';
import { useCartStore } from '@/stores/cart';
import { useAuthCustomerStore } from '@/stores/auth/customer';
import { useProductStore } from '@/stores/catalog/product';
import { useSettingsStore } from '@/stores/settings'
import { useRedirect } from '@/composables/useRedirect';

const orderStore = useOrderStore();
const cartStore = useCartStore();
const customerStore = useAuthCustomerStore();
const productStore = useProductStore();
const settingsStore = useSettingsStore();
const { redirectTo } = useRedirect();

const isCheckoutReady = ref(false);
onMounted(async () => {
    await orderStore.initializeCheckout(orderStore.checkoutParams);
    formdata.value = { ...formdata.value, ...orderStore.checkoutSelections }
    isCheckoutReady.value = true;
});

// Set formdata
const formdata = ref({
    shipping: {},
    payment: {},
    comments: {}
});

// Emit function for emitting events
const emit = defineEmits(["updateCheckoutStep"]);

function editAddress(address, addressType) {
    emit('updateCheckoutStep', { step: CheckoutAddress, params: { ...address, edit_address_type: addressType }})
}

// Checkout selections for shipping/payment
watch(
    () => formdata.value,
    ({ payment, shipping, comment }) => {
        if (payment) orderStore.setCheckoutSelections({ payment: payment });
        if (shipping) orderStore.setCheckoutSelections({ shipping: shipping });
        if (comment) orderStore.setCheckoutSelections({ comments: comment });
        orderStore.connectPaymentGateway();
    },
    { deep: true }
);

// Redirect to the checkout success
watch(
    () => orderStore.orderRef,
    (v) => {
        if (v) {
            redirectTo(`/checkout-success/${v}`);
        }
    }
);

const acc = ref({
    address: true,
    savings: false,
	note: false,
});

function toggleAcc(key) {
    acc.value[key] = !acc.value[key];
}
</script>

<template>
    <div>
		<div class="block lg:hidden mb-5 lg:mb-0">
			<CheckoutCartContent 
				:items="cartStore.getItems" 
				:subtotal="+cartStore.total"
				:shipping-cost="+orderStore.checkoutShippingCost"
				:tax-amount="+orderStore.checkoutTaxAmount"
				:total="+orderStore.checkoutParams.total"
				:selections="orderStore.checkoutSelections" 
			/>
		</div>
        <div class="mb-4 bg-gray-100 lg:bg-white py-2 px-4 lg:p-0 rounded">
			<button
				type="button"
				class="w-full flex items-center justify-between gap-3 text-left"
				@click="toggleAcc('address')"
				:aria-expanded="acc.address"
			>
				<span class="text-lg font-bold uppercase tracking-widest">{{ $t('Shipping & Payment Address') }}</span>
				<svg
					class="lg:hidden w-5 h-5 shrink-0 transition-transform duration-200"
					:class="acc.address ? 'rotate-180' : ''"
					viewBox="0 0 20 20"
					fill="currentColor"
					aria-hidden="true"
				>
					<path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.24a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08z" clip-rule="evenodd" />
				</svg>
			</button>
		</div>
        <div :class="`hidden lg:block ${acc.address ? `!block` : ''}`">
            <div class="grid grid-cols-2 gap-5 rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div>
                    <div class="font-bold flex items-center mb-2">{{ $t('Shipping Address') }}</div>
                    <DisplayAddress :address="orderStore.checkoutParams.shipping" />
                    <button v-if="customerStore.customerAddressTotal > 1" class="text-sm text-gray-800 underline p-0 mb-3 lg:mb-0 cursor-pointer" @click.stop="editAddress(orderStore.checkoutParams.shipping, 'shipping')">
                        {{ $t('Edit Address') }}
                    </button>
                </div>
                <div>
                    <div class="font-bold flex items-center mb-2">{{ $t('Billing Address') }}</div>
                    <DisplayAddress :address="orderStore.checkoutParams.billing" />
                    <button v-if="customerStore.customerAddressTotal > 1" class="text-sm text-gray-800 underline p-0 mb-3 lg:mb-0 cursor-pointer" @click.stop="editAddress(orderStore.checkoutParams.billing, 'billing')">
                        {{ $t('Edit Address') }}
                    </button>
                </div>
            </div>
            <button v-if="customerStore.customerAddressTotal === 1" class="text-sm text-gray-800 underline p-0 cursor-pointer" @click.stop="editAddress(orderStore.checkoutParams.shipping)">
                {{ $t('Edit your current address or add a new?') }}
            </button>
			
			<div v-if="isCheckoutReady" class="mt-5">
				<div v-for="shipping in orderStore.checkoutShippingMethods" :key="shipping.code" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
					<div class="text-lg font-semibold flex items-center">{{ $t('Shipping Methods') }}</div>
					<div class="flex justify-between items-center mb-3">
						<div v-if="shipping.image">
							<img :src="`/storage/shippings/${shipping.image}`" :alt="shipping.name" class="w-12 h-auto">
						</div>
					</div>
					<template v-if="shipping.methods.length > 0">
						<div v-for="method in shipping.methods" :key="method.id" class="grid grid-cols-12 gap-4 items-center mb-2">
							<template v-if="method.error !== true">
								<div class="col-span-8">
									<div class="flex items-center">
										<input v-model="formdata.shipping" class="form-radio text-blue-500 border-gray-300 focus:ring-3 focus:ring-blue-300 focus:ring-opacity-50" type="radio" :value="method" :id="`shipping-method-${method.id}`">
										<label class="ml-2 text-gray-800" :for="`shipping-method-${method.id}`">
											{{ $t(method.title) }}
										</label>
									</div>
								</div>
								<div class="col-span-4 text-right">
									<PriceDisplay :price="+productStore.priceFormat(method.cost)" />
								</div>
							</template>
							<template v-else>
								<div class="col-span-12 text-red-600">
									{{ $t(method.title) }}
								</div>
							</template>
						</div>
					</template>
					<p v-else class="text-red-600">
						{{ $t("This shipping method is not currently available for your order.") }}
					</p>
				</div>
			</div>
			<div v-else class="space-y-5 mt-5">
				<div class="h-6 bg-gray-300 rounded w-2/3"></div>
				<div class="h-4 bg-gray-300 rounded w-1/2"></div>
			</div>
			
        </div>
        <div class="flex flex-col mt-5 gap-5">
            <div v-if="orderStore.checkoutDiscountModules" class="flex flex-col">
                <div class="block lg:hidden text-xl font-bold uppercase tracking-widest bg-gray-100 lg:bg-white py-2 px-4 lg:p-0 rounded">
					<button
						type="button"
						class="w-full flex items-center justify-between gap-3 text-left"
						@click="toggleAcc('savings')"
						:aria-expanded="acc.savings"
					>
						<span class="text-lg font-bold uppercase tracking-widest">{{ $t('Savings & Rewards') }}</span>
						<svg
							class="lg:hidden w-5 h-5 shrink-0 transition-transform duration-200"
							:class="acc.savings ? 'rotate-180' : ''"
							viewBox="0 0 20 20"
							fill="currentColor"
							aria-hidden="true"
						>
							<path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.24a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08z" clip-rule="evenodd" />
						</svg>
					</button>
				</div>
                <div v-if="isCheckoutReady" :class="`space-y-5 hidden lg:block ${acc.savings ? `!block` : ''}`">
                    <!-- Used to hook into the checkout promotion. -->
                    <template v-for="(component, index) in $pluginStorefrontHooks['checkout_promotion']" :key="index">
                        <component :is="component"></component>
                    </template>
                </div>
            </div>

        <!--    <div class="flex flex-col mt-4 gap-4">
                <div class="text-xl font-semibold mb-3">{{ $t('Payment Methods') }}</div>
                <div v-if="isCheckoutReady">
                    <div v-for="payment in orderStore.checkoutPaymentMethods" :key="payment.id" class="mb-3 p-4 border rounded-md">
                        <div class="flex items-center justify-between">
                            <label class="flex items-center space-x-2">
                                <input v-model="formdata.payment" type="radio" :value="payment" :id="`payment-method-${payment.id}`" class="form-radio">
                                <span>{{ $t(payment.module) }}</span>
                            </label>
                            <img v-if="payment.image" :src="`/storage/payments/${payment.image}`" alt="payment.module" class="h-12">
                        </div>
                    </div>
                </div>
                <div v-else class="space-y-4">
					<div class="h-6 bg-gray-300 rounded w-2/3"></div>
					<div class="h-4 bg-gray-300 rounded w-1/2"></div>
				</div>
            </div> -->
            <div class="flex flex-col">
				<div class="hidden lg:block text-lg font-semibold mb-2">{{ $t('Special Instructions or Comments About Your Order') }}</div>
				<div class="block lg:hidden text-xl mb-4 font-bold uppercase tracking-widest bg-gray-100 lg:bg-white py-2 px-4 lg:p-0 rounded">
					<button
						type="button"
						class="w-full flex items-center justify-between gap-3 text-left"
						@click="toggleAcc('note')"
						:aria-expanded="acc.note"
					>
						<span class="text-lg font-bold uppercase tracking-widest">{{ $t('Special Instructions or Comments About Your Order') }}</span>
						<svg
							class="lg:hidden w-5 h-5 shrink-0 transition-transform duration-200 block lg:hidden"
							:class="acc.note ? 'rotate-180' : ''"
							viewBox="0 0 20 20"
							fill="currentColor"
							aria-hidden="true"
						>
							<path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.24a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08z" clip-rule="evenodd" />
						</svg>
					</button>
				</div>
                <div :class="`hidden lg:block ${acc.note ? `!block` : ''}`">
                    <textarea v-model="formdata.comments.customer_note" 
                            class="w-full border border-gray-300 rounded shadow-xs focus:ring-blue-300 focus:border-blue-300 p-3" 
                            rows="2" 
                            :placeholder="$t('Write something...')">
                    </textarea>
                </div>
            </div>
        </div>
    </div>
</template>