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
    savings: true,
	note: true,
});

function toggleAcc(key) {
    acc.value[key] = !acc.value[key];
}
</script>

<template>
    <div class="space-y-6">
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

        <!-- Address Summary Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 bg-gray-50 border-b border-gray-100 flex justify-between items-center cursor-pointer" @click="toggleAcc('address')">
                <h3 class="font-bold text-gray-900">{{ $t('Shipping & Payment Address') }}</h3>
                <svg class="w-5 h-5 text-gray-500 transition-transform" :class="acc.address ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </div>
            
            <div v-show="acc.address" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="relative group">
                        <div class="flex items-center justify-between mb-2">
                             <div class="font-semibold text-gray-700">{{ $t('Shipping Address') }}</div>
                             <button v-if="customerStore.customerAddressTotal > 1" class="text-sm font-medium text-blue-600 hover:text-blue-800" @click.stop="editAddress(orderStore.checkoutParams.shipping, 'shipping')">
                                {{ $t('Change') }}
                            </button>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 text-sm text-gray-600">
                             <DisplayAddress :address="orderStore.checkoutParams.shipping" />
                        </div>
                    </div>

                     <div class="relative group">
                        <div class="flex items-center justify-between mb-2">
                             <div class="font-semibold text-gray-700">{{ $t('Billing Address') }}</div>
                             <button v-if="customerStore.customerAddressTotal > 1" class="text-sm font-medium text-blue-600 hover:text-blue-800" @click.stop="editAddress(orderStore.checkoutParams.billing, 'billing')">
                                {{ $t('Change') }}
                            </button>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 text-sm text-gray-600">
                             <DisplayAddress :address="orderStore.checkoutParams.billing" />
                        </div>
                    </div>
                </div>
                 <div class="mt-4 pt-4 border-t border-gray-100" v-if="customerStore.customerAddressTotal === 1">
                    <button class="text-sm text-gray-500 underline hover:text-gray-800" @click.stop="editAddress(orderStore.checkoutParams.shipping)">
                         {{ $t('Edit address') }}
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Shipping Method -->
         <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 border-b border-gray-100">
                <h3 class="font-bold text-gray-900 text-lg">{{ $t('Shipping Method') }}</h3>
            </div>
            <div class="p-6">
                <div v-if="isCheckoutReady">
                    <div v-for="shipping in orderStore.checkoutShippingMethods" :key="shipping.code" class="space-y-4">
                        <!-- <div class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">{{ shipping.name }}</div> -->
                        
                        <div class="grid grid-cols-1 gap-4">
                             <template v-for="method in shipping.methods" :key="method.id">
                                 <label 
                                    class="relative flex items-start p-4 cursor-pointer rounded-xl border transition-all duration-200 hover:shadow-md"
                                    :class="formdata.shipping?.id === method.id ? 'border-blue-600 bg-blue-50 ring-1 ring-blue-600' : 'border-gray-200 bg-white hover:border-blue-300'"
                                >
									<input v-model="formdata.shipping" class="sr-only" type="radio" :value="method">
                                    
                                    <div class="flex items-center h-5">
                                        <div class="w-4 h-4 rounded-full border flex items-center justify-center transition-colors"
                                             :class="formdata.shipping?.id === method.id ? 'border-blue-600' : 'border-gray-300'">
                                            <div class="w-2 h-2 rounded-full bg-blue-600" v-show="formdata.shipping?.id === method.id"></div>
                                        </div>
                                    </div>

                                    <div class="ml-4 flex-1">
                                        <div class="flex items-center justify-between">
                                            <span class="block text-sm font-medium text-gray-900">{{ $t(method.title) }}</span>
                                            <span class="block text-sm font-bold text-gray-900">
                                                <PriceDisplay :price="+productStore.priceFormat(method.cost)" />
                                            </span>
                                        </div>
                                        <div v-if="method.description" class="mt-1 text-xs text-gray-500">{{ method.description }}</div>
                                    </div>
                                    
                                     <div v-if="method.error" class="absolute inset-0 bg-white/50 flex items-center justify-center text-red-600 font-bold">
                                        {{ $t('Unavailable') }}
                                    </div>
                                </label>
                             </template>
                        </div>
                    </div>
                     <div v-if="!orderStore.checkoutShippingMethods?.length" class="text-gray-500 text-center py-4">
                        {{ $t('No shipping methods available.') }}
                    </div>
                </div>
                <div v-else class="space-y-4 animate-pulse">
                     <div class="h-16 bg-gray-100 rounded-xl"></div>
                     <div class="h-16 bg-gray-100 rounded-xl"></div>
                </div>
            </div>
        </div>

        <!-- Savings & Rewards -->
         <div v-if="orderStore.checkoutDiscountModules && orderStore.checkoutDiscountModules.length" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
             <div class="p-4 bg-gray-50 border-b border-gray-100 flex justify-between items-center cursor-pointer" @click="toggleAcc('savings')">
                <h3 class="font-bold text-gray-900">{{ $t('Savings & Rewards') }}</h3>
                 <svg class="w-5 h-5 text-gray-500 transition-transform" :class="acc.savings ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </div>
            <div v-show="acc.savings" class="p-6 space-y-4">
                 <template v-for="(component, index) in $pluginStorefrontHooks['checkout_promotion']" :key="index">
                    <component :is="component"></component>
                </template>
            </div>
         </div>

        <!-- Payment Method -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 border-b border-gray-100">
                <h3 class="font-bold text-gray-900 text-lg">{{ $t('Payment Method') }}</h3>
            </div>
             <div class="p-6">
                <div v-if="isCheckoutReady">
                    <div class="grid grid-cols-1 gap-4">
                        <template v-for="payment in orderStore.checkoutPaymentMethods" :key="payment.id">
                            <label 
                                class="relative flex items-center p-4 cursor-pointer rounded-xl border transition-all duration-200 hover:shadow-md"
                                :class="formdata.payment?.id === payment.id ? 'border-blue-600 bg-blue-50 ring-1 ring-blue-600' : 'border-gray-200 bg-white hover:border-blue-300'"
                            >
                                <input v-model="formdata.payment" class="sr-only" type="radio" :value="payment">
                                
                                 <div class="flex items-center h-5">
                                    <div class="w-4 h-4 rounded-full border flex items-center justify-center transition-colors"
                                            :class="formdata.payment?.id === payment.id ? 'border-blue-600' : 'border-gray-300'">
                                        <div class="w-2 h-2 rounded-full bg-blue-600" v-show="formdata.payment?.id === payment.id"></div>
                                    </div>
                                </div>

                                <div class="ml-4 flex-1 flex items-center justify-between">
                                    <span class="block text-sm font-medium text-gray-900">{{ $t(payment.module) }}</span>
                                    <img v-if="payment.image" :src="`/storage/payments/${payment.image}`" :alt="payment.module" class="h-8 object-contain">
                                </div>
                            </label>
                        </template>
                         <div v-if="!orderStore.checkoutPaymentMethods?.length" class="text-gray-500 text-center py-4">
                            {{ $t('No payment methods available.') }}
                        </div>
                    </div>
                </div>
                 <div v-else class="space-y-4 animate-pulse">
                     <div class="h-16 bg-gray-100 rounded-xl"></div>
                </div>
            </div>
        </div>

        <!-- Comments -->
         <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 bg-gray-50 border-b border-gray-100 flex justify-between items-center cursor-pointer" @click="toggleAcc('note')">
                 <h3 class="font-bold text-gray-900">{{ $t('Order Note') }}</h3>
                  <svg class="w-5 h-5 text-gray-500 transition-transform" :class="acc.note ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </div>
            <div v-show="acc.note" class="p-6">
                 <textarea v-model="formdata.comments.customer_note" 
                            class="w-full border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 p-3 text-sm" 
                            rows="3" 
                            :placeholder="$t('Add special instructions for your order...')">
                </textarea>
            </div>
        </div>

    </div>
</template>