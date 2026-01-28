<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useSettingsStore } from '@/stores/settings';
import { useCartStore } from '@/stores/cart';
import { useAuthCustomerStore } from '@/stores/auth/customer';
import { useProductStore } from '@/stores/catalog/product';

const settingsStore = useSettingsStore();
const cartStore = useCartStore();
const authCustomerStore = useAuthCustomerStore();
const productStore = useProductStore();

const props = defineProps({
    subtotal: {
        type: Number,
        required: true
    }
});

const picked = ref({});
const formdata = ref({
    country_code: 'US',
    zone_code: 'AL',
    zone_name: undefined,
    postcode: undefined
});

const loadedCountry = ref(false);
onMounted(async () => {
    await settingsStore.fetchCountries();
    loadedCountry.value = true;
	
	// Auto run
	const firstRadio = document.querySelector('.cart-estimate-shipping input[type="radio"]');
    if (firstRadio) {
        firstRadio.click();
    }
	
});

// Computed property for regions
const regions = computed(() => {
    const country = settingsStore.countries.find(country => country.iso_code_2 === formdata.value.country_code);
    return country?.zones || [];
});

const shippingMethods = ref([]);
const calculating = ref(false);
const emit = defineEmits(['update:shippingMethods', 'loading']);
const calculateShippingEstimate = async () => {
    
	// Start when postcode is not null
	if(!formdata.value.postcode) {
		emit('loading', false);
		return;
	};
	
    // Start the calculation process
    calculating.value = true;
	emit('loading', true);
	
	try {
        const shippingData = {
			customer: {
				addresses: [
					{
						country_code: formdata.value.country_code,
						zone_code: formdata.value.zone_code,
						zone_name: formdata.value.zone_name,
						postcode: formdata.value.postcode
					}
				],
				group_pricing_id: authCustomerStore.customerInfo?.group_pricing_id
			},
			products: cartStore.getItems,
			subtotal: cartStore.total
		};
		shippingMethods.value = await cartStore.calculateShippingEstimate(shippingData);
		
		// Emit to parent
		emit('update:shippingMethods', shippingMethods.value);
		
    } catch (error) {
        console.error('Error calculating shipping:', error);
        toast.error(t('Failed to calculate shipping.'));
    } finally {
        emit('loading', false);
		calculating.value = false;
    }
	
}

watch(
    () => picked.value,
    (v) => {
        if (v) {
            formdata.value = {
                country_code: v.country_code,
                zone_code: v.zone_code,
                zone_name: v.zone_name,
                postcode: v.postcode
            }
            calculateShippingEstimate();
        }
    }
);

watch(() => props.subtotal, () => {
    calculateShippingEstimate();
}, { immediate: true });

</script>

<template>
    <div class="cart-estimate-shipping mt-2">
        <div class="border-t border-b py-2 mb-2 bg-gray-100 px-2">{{ $t('Estimate shipping costs') }}</div>
        <div>
            <div v-if="authCustomerStore.customerAddressTotal > 0" class="mb-3">
                <div v-for="address in authCustomerStore.customerAddresses" :key="address.id" class="flex items-center mb-2 text-xs">
                    <input
                        v-model="picked"
                        :value="address"
                        type="radio"
                        :id="`radio-address-${address.id}`"
                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500"
                    />
                    <label
                        :for="`radio-address-${address.id}`"
                        class="ml-2 text-gray-700 cursor-pointer"
                    >
                        {{ address.country_name }}, {{ address.zone_name }}, {{ address.postcode }}
                    </label>
                </div>
            </div>
            <form @submit.prevent="calculateShippingEstimate" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="space-y-2">
                        <label for="country" class="block text-xs font-medium text-gray-700">{{ $t('Country') }}</label>
                        <select 
                            name="country" 
                            id="country" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-xs focus:ring-indigo-500 focus:border-indigo-500 sm:text-xs select-bg appearance-none" 
                            v-model="formdata.country_code"
                        >
                            <option v-for="(country, index) in settingsStore.countries" :value="country.iso_code_2" :key="index">{{ country.name }}</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="region" class="block text-xs font-medium text-gray-700">{{ $t('Region') }}</label>
                        <template v-if="regions && regions.length > 0">
                            <select 
                                name="region" 
                                id="region" 
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-xs focus:ring-indigo-500 focus:border-indigo-500 sm:text-xs select-bg appearance-none" 
                                v-model="formdata.zone_code"
                            >
                                <option v-for="(region, index) in regions" :value="region.code" :key="index">{{ region.name }}</option>
                            </select>
                        </template>
                        <template v-else>
                            <input 
                                v-model="formdata.zone_name" 
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-xs focus:ring-indigo-500 focus:border-indigo-500 sm:text-xs" 
                                id="region" 
                                :placeholder="$t('Enter your region')" 
                                required
                            />
                        </template>
                    </div>

                    <div class="space-y-2">
                        <label for="postcode" class="block text-xs font-medium text-gray-700">{{ $t('Postcode') }}</label>
                        <input 
                            v-model="formdata.postcode" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-xs focus:ring-indigo-500 focus:border-indigo-500 sm:text-xs" 
                            id="postcode" 
                            :placeholder="$t('Enter your zip code')" 
                            required
                        />
                    </div>
                </div>

                <div class="flex justify-end">
                    <button 
                        :disabled="calculating" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-xs font-medium rounded-md shadow-xs text-white bg-gray-800 hover:bg-gray-700 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 disabled:bg-gray-400 disabled:cursor-not-allowed cursor-pointer"
                        type="submit"
                    >
						<svg v-if="calculating" class="mr-3 -ml-1 size-5 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
							<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
							<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
						</svg>
                        {{ $t('Show estimate shipping') }}
                    </button>
                </div>
            </form>
            <template v-if="shippingMethods.length > 0">
                <div v-for="shipping in shippingMethods" :key="shipping.code" class="mt-3">
                    <div class="flex justify-between items-center mb-3">
                        <div v-if="shipping.image">
                            <img :src="`/storage/shippings/${shipping.image}`" :alt="shipping.name" class="w-12 h-auto">
                        </div>
                    </div>
                    <template v-if="shipping.methods.length > 0">
                        <div v-for="method in shipping.methods" :key="method.id" class="grid grid-cols-3 gap-2 text-xs border-b border-gray-200 py-2">
                            <div class="col-span-2">{{ method.title }}</div>
                            <div class="col-span-1 text-right">
                                <template v-if="+method.cost > 0">
                                    <PriceDisplay :price="+productStore.priceFormat(method.cost)" />
                                </template>
                                <template v-else>{{ $t("N/A") }}</template>
                            </div>
                        </div>
                    </template>
                    <p v-else class="text-gray-500 text-xs">{{ $t('No available shipping methods.') }}</p>
                </div>
            </template>
        </div>
    </div>

</template>