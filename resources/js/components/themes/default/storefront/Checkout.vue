<script setup>
import CheckoutCreateAccount from '@theme/storefront/components/checkout/CreateAccount.vue'
import CheckoutForm from '@theme/storefront/components/checkout/Form.vue'
import CheckoutAddress from '@theme/storefront/components/checkout/Address.vue'
import CheckoutOrderReview from '@theme/storefront/components/checkout/OrderReview.vue'

import { ref, onMounted, markRaw, computed } from 'vue';
import { useRedirect } from '@/composables/useRedirect';

import { useAuthCustomerStore } from '@/stores/auth/customer';
import { useCartStore } from '@/stores/cart';
import { useOrderStore } from '@/stores/order';

const { redirectTo } = useRedirect();

const cartStore = useCartStore();
const authCustomerStore = useAuthCustomerStore();
const orderStore = useOrderStore();

// Check the 
const checkoutComponent = ref(null);
const checkoutParams = ref({});

const steps = [
    { id: 1, name: 'Account', component: CheckoutCreateAccount },
    { id: 2, name: 'Shipping & Billing', component: CheckoutAddress },
    { id: 3, name: 'Payment & Review', component: CheckoutForm },
];

const currentStep = computed(() => {
    if (!checkoutComponent.value) return 1;
    // Map components to steps. 
    // note: markRaw wraps the component, so strict equality check might fail if not careful, 
    // but usually checking the type or name works. 
    // Safest is to check against the imported objects since markRaw returns the object itself extended.
    if (checkoutComponent.value === CheckoutCreateAccount) return 1;
    if (checkoutComponent.value === CheckoutAddress) return 2;
    if (checkoutComponent.value === CheckoutForm) return 3;
    return 1;
});

const retrieveCheckoutStep = () => {
    if(authCustomerStore.isLoggedIn) {
        if(authCustomerStore.customerAddressTotal === 0) {
            checkoutComponent.value = markRaw(CheckoutAddress);
        } else {
            checkoutComponent.value = markRaw(CheckoutForm);
        }
    } else {
        checkoutComponent.value = markRaw(CheckoutCreateAccount);
    }

    // Return home page if order is not ready
    if(!orderStore.readyToCheckout) {
		if(cartStore.numberOfItems > 0) {
			redirectTo("/cart");
		} else {
			redirectTo("/");
		}
    }
}

const updateCheckoutStep = (obj)  => {
    checkoutComponent.value = markRaw(obj.step);
    checkoutParams.value = { ...checkoutParams.value, ...obj.params };
}

// Check if the checkout step
onMounted(() => {
    retrieveCheckoutStep();
});

</script>

<template>
    <div class="bg-gray-50 min-h-screen pb-12">
        <div v-if="orderStore.readyToCheckout" class="container mx-auto px-4 max-w-7xl">
            
            <!-- Header & Stepper -->
            <div class="py-10">
                <div class="flex flex-col items-center justify-center space-y-6">
                    <h2 class="text-3xl font-extrabold tracking-tight text-gray-900">{{ $t('Secure Checkout') }}</h2>
                    
                    <!-- Stepper -->
                    <div class="w-full max-w-3xl">
                        <div class="flex items-center justify-between relative">
                            <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1 bg-gray-200 -z-10 rounded-full"></div>
                            <div 
                                class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-blue-600 -z-10 rounded-full transition-all duration-500 ease-in-out"
                                :style="{ width: `${((currentStep - 1) / (steps.length - 1)) * 100}%` }"
                            ></div>

                            <div v-for="step in steps" :key="step.id" class="flex flex-col items-center group">
                                <div 
                                    class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm border-4 transition-all duration-300"
                                    :class="[
                                        currentStep > step.id ? 'bg-blue-600 border-blue-600 text-white' : 
                                        currentStep === step.id ? 'bg-white border-blue-600 text-blue-600' : 
                                        'bg-white border-gray-200 text-gray-400'
                                    ]"
                                >
                                    <span v-if="currentStep > step.id">✓</span>
                                    <span v-else>{{ step.id }}</span>
                                </div>
                                <span 
                                    class="mt-2 text-sm font-medium transition-colors duration-300"
                                    :class="currentStep >= step.id ? 'text-gray-900' : 'text-gray-400'"
                                >
                                    {{ $t(step.name) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div :class="`grid grid-cols-1 lg:grid-cols-12 gap-8 ${authCustomerStore.isLoggedIn ? `checkout-auth-yes` : `checkout-auth-no`}`">
                <!-- Left Column: Main Content -->
                <div class="lg:col-span-8">
                    <component :is="checkoutComponent" @updateCheckoutStep="updateCheckoutStep" :params="checkoutParams"></component>
                </div>

                <!-- Right Column: Order Summary (Sticky) -->
                <div class="lg:col-span-4">
                    <div class="sticky top-6 space-y-6">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <CheckoutOrderReview 
                                :items="cartStore.getItems" 
                                :subtotal="+cartStore.total"
                                :shipping-cost="+orderStore.checkoutShippingCost"
                                :tax-amount="+orderStore.checkoutTaxAmount"
                                :total="+orderStore.checkoutParams.total"
                                :selections="orderStore.checkoutSelections" 
                            />
                        </div>
                
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                             <div class="checkout-payment-gateway-container">
                                <div v-if="Object.keys(orderStore.checkoutSelections.payment).length === 0 || Object.keys(orderStore.checkoutSelections.shipping).length === 0" class="bg-yellow-50 text-yellow-800 p-4 rounded-lg flex items-start gap-3 text-sm">
                                    <svg class="w-5 h-5 shrink-0 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ $t('Please select shipping and payment method to complete your order.') }}
                                </div>
                                <!-- This ID used to render the payment gateway -->
                                <div id="render-payment-gateway" class="mt-4"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>