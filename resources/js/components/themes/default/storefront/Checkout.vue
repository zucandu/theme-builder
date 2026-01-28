<script setup>
import CheckoutCreateAccount from '@theme/storefront/components/checkout/CreateAccount.vue'
import CheckoutForm from '@theme/storefront/components/checkout/Form.vue'
import CheckoutAddress from '@theme/storefront/components/checkout/Address.vue'
import CheckoutOrderReview from '@theme/storefront/components/checkout/OrderReview.vue'

import { ref, onMounted, markRaw } from 'vue';
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
    <div>
        <div v-if="orderStore.readyToCheckout" class="container mx-auto px-4">
             <div class="sm:mx-auto sm:w-full sm:max-w-sm">
                <h2 class="text-center text-2xl font-bold tracking-tight text-gray-900 font-bold uppercase tracking-widest">{{ $t('Secure Checkout') }}</h2>
            </div>
            <div :class="`w-full lg:w-10/12 mx-auto py-6 ${authCustomerStore.isLoggedIn ? `checkout-auth-yes` : `checkout-auth-no`}`">
                <div class="grid grid-cols-12 lg:space-x-12">
                    <div class="left-column lg:col-span-7 col-span-12">
                        <component :is="checkoutComponent" @updateCheckoutStep="updateCheckoutStep" :params="checkoutParams"></component>
                    </div>
                    <div class="right-column lg:col-span-5 col-span-12">
                        <div class="sticky top-[100px] pt-3 lg:pt-0">
                            <div class="flex flex-col bg-white rounded-lg lg:px-4">
							
                                <CheckoutOrderReview 
									:items="cartStore.getItems" 
									:subtotal="+cartStore.total"
									:shipping-cost="+orderStore.checkoutShippingCost"
									:tax-amount="+orderStore.checkoutTaxAmount"
									:total="+orderStore.checkoutParams.total"
									:selections="orderStore.checkoutSelections" 
								/>
					
                                <div class="checkout-payment-gateway-container mb-3 lg:mb-0">
                                    <div v-if="Object.keys(orderStore.checkoutSelections.payment).length === 0 || Object.keys(orderStore.checkoutSelections.shipping).length === 0" class="bg-yellow-100 text-yellow-700 p-3 rounded-md">
                                        {{ $t('Please select shipping and payment method.') }}
                                    </div>
                                    <!-- This ID used to render the payment gateway -->
                                    <div id="render-payment-gateway"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>