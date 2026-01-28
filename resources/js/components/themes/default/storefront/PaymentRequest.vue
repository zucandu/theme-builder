<script setup>
import { ref, onMounted, watch } from 'vue';

import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useToast } from 'vue-toastification';

const route = useRoute();
const { t } = useI18n();
const toast = useToast();

import { useHelpers } from '@/composables/useHelpers';
const { formatCurrency } = useHelpers();

import { useAuthCustomerStore } from '@/stores/auth/customer';
const authCustomerStore = useAuthCustomerStore();

import { useOrderStore } from '@/stores/order';
const orderStore = useOrderStore();

import { useAvailablePaymentMethods } from '@/composables/useAvailablePaymentMethods';
const { availablePaymentMethods } = useAvailablePaymentMethods();

const orderId = ref();
const amount = ref();
const note = ref();
const orderPayment = ref();
const isPaid = ref(false);
const currency = ref('USD');
const customerNote = ref();

const order = ref({});
const shippingAddress = ref({});
const billingAddress = ref({});

onMounted(async() => {
    try {
        const res = await orderStore.getPaymentRequest(route.params.token);
        const pr = res.data.payment_request;
        orderId.value = pr.order_id;
        amount.value = formatCurrency(pr.amount);
        note.value = pr.note;
        isPaid.value = pr.status === `paid`;
        orderPayment.value = pr.payment_method;
        currency.value = pr.currency;

        // Get order details
        await orderStore.fetchOrderDetailsByRef(pr.reference);
        order.value = orderStore.retrieveOrder;

        // Set shipping and billing address
        shippingAddress.value = order.value.addresses.find(addr => addr.address_type === `shipping`);
        billingAddress.value = order.value.addresses.find(addr => addr.address_type === `billing`) || shippingAddress.value;

        const paymentName = orderPayment.value.toLowerCase();
        availablePaymentMethods[paymentName].jsPayment.setParams({
            payment_request_id: orderId.value,
            payment_request_token: route.params.token,
            payment_request_note: customerNote.value,
            currency: currency.value,
            total: amount.value,
            subtotal: amount.value,
            tax: "0.00",
            shippingcost: "0.00",
            discount: "0.00",
            paymentmethod: {
                id: orderPayment.value
            },
            profile: {
                firstname: order.value.firstname,
                lastname: order.value.lastname,
                email: order.value.email
            },
            billing: {
                company: billingAddress.value?.company || "",
                name: billingAddress.value?.name || "",
                address_line_1: billingAddress.value?.address_line_1 || "",
                address_line_2: billingAddress.value?.address_line_2 || "",
                city: billingAddress.value?.city || "",
                zone_code: billingAddress.value?.zone_name || "",
                postcode: billingAddress.value?.postcode || "",
                country_code: billingAddress.value?.country_code,
                phone: billingAddress.value?.phone || ""
            },
            shipping: {
                company: shippingAddress.value?.company || "",
                name: shippingAddress.value?.name || "",
                address_line_1: shippingAddress.value?.address_line_1 || "",
                address_line_2: shippingAddress.value?.address_line_2 || "",
                city: shippingAddress.value?.city || "",
                zone_name: shippingAddress.value?.zone_name || "",
                postcode: shippingAddress.value?.postcode || "",
                country_code: shippingAddress.value?.country_code,
                phone: shippingAddress.value?.phone || ""
            },
            items: [
                {
                    qty: 1,
                    finalprice: amount.value,
                    sku: "PAYMENT-REQUEST",
                    translations: [
                        { locale: "en", name: `Payment Request: ${route.params.token}` }
                    ]
                }
            ],

            language: "en"
        });
        availablePaymentMethods[paymentName].jsPayment.loadScript();
    } catch (error) {
        toast.error(t(error.response.data.message));
    }
});

watch(
    () => orderStore.orderRef,
    (v) => {
        if (v) {
            isPaid.value = true;
        }
    }
);

</script>

<template>
    <div class="min-h-[640px] flex items-center justify-center justify-center px-6 py-12 lg:px-8">
        <div class="w-full max-w-xl">
            <!-- Card -->
            <div class="bg-white shadow-lg rounded-2xl overflow-hidden border border-slate-200">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h1 class="text-xl font-semibold">{{ $t(`Make a Payment`) }}</h1>
                    <p class="text-sm text-slate-500 mt-1">{{ $t(`Complete the form below to pay this request securely.`) }}</p>
                </div>

                <!-- Main content -->
                <div class="px-6 pb-6 pt-2 space-y-6">
                    
					<div class="mb-3 flex gap-3 rounded-lg bg-sky-50 border border-sky-200 px-4 py-3 text-sm text-sky-700">
						<div class="text-sky-600 text-xl">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
								<path d="M4.5 3.75a3 3 0 0 0-3 3v.75h21v-.75a3 3 0 0 0-3-3h-15Z" />
								<path fill-rule="evenodd" d="M22.5 9.75h-21v7.5a3 3 0 0 0 3 3h15a3 3 0 0 0 3-3v-7.5Zm-18 3.75a.75.75 0 0 1 .75-.75h6a.75.75 0 0 1 0 1.5h-6a.75.75 0 0 1-.75-.75Zm.75 2.25a.75.75 0 0 0 0 1.5h3a.75.75 0 0 0 0-1.5h-3Z" clip-rule="evenodd" />
							</svg>
						</div>
						<div>
							<div class="font-semibold mb-1">{{ $t(`You're making a secure payment`) }}</div>
							<div>{{ $t(`Review your payment details below and proceed when ready.`) }}</div>
						</div>
					</div>
					
                    <!-- Payment summary -->
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 flex flex-col gap-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-500">{{ $t(`Amount to pay`) }}</span>
                            <span class="text-lg font-semibold">
								<PriceDisplay :price="amount" />
                            </span>
                        </div>

                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500">{{ $t(`Order`) }}</span>
                            <span class="font-medium">ID-{{ orderId }}</span>
                        </div>

                        <div class="text-xs text-slate-500 pt-2 border-t border-dashed border-slate-200">
                            {{ note }}
                        </div>
                    </div>

                    <div v-if="!isPaid">
                        <!-- Customer info -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t(`Note (optional)`) }}</label>
                                <textarea
                                    v-model="customerNote"
                                    rows="2"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 resize-none"
                                    :placeholder="$t(`Add a note for the recipient (optional)`)"
                                ></textarea>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <h2 class="text-sm font-semibold text-slate-700">{{ $t(`Payment method`) }}</h2>
                            <div id="render-payment-gateway"></div>
                        </div>
                    </div>
                    <div v-else class="rounded-xl border border-green-200 bg-green-50 p-6">
                        <div class="flex items-start gap-3">
                            <!-- Icon -->
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-green-800">{{ $t(`Payment Completed`) }}</h2>
                                <p class="text-sm text-green-700">{{ $t(`Thank you! Your payment has been successfully processed.`) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
