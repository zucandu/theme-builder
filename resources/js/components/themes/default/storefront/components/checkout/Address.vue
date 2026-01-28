<script setup>
import CheckoutForm from '@theme/storefront/components/checkout/Form.vue'
import AddressAutocomplete from '@theme/storefront/components/checkout/AddressAutocomplete.vue'
import { ref, onMounted, computed, defineProps, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useToast } from 'vue-toastification';
import { useSettingsStore } from '@/stores/settings';
import { useAuthCustomerStore } from '@/stores/auth/customer';

const { t } = useI18n();
const toast = useToast();
const settingsStore = useSettingsStore();
const authCustomerStore = useAuthCustomerStore();

const props = defineProps({
    params: {
        type: Object,
        required: true
    }
});

// Emit function for emitting events
const emit = defineEmits(["updateCheckoutStep"]);
const updateCheckoutComponent = () => {
    emit('updateCheckoutStep', { step: CheckoutForm });
}


const formdata = ref({
    id: undefined,
    company: undefined,
    name: undefined,
    country_id: undefined,
    country_code: undefined,
    country_name: undefined,
    zone_id: undefined,
    zone_code: undefined,
    zone_name: undefined,
    postcode: undefined,
    address_line_1: undefined,
    address_line_2: undefined,
    city: undefined,
    phone: undefined,
    default_shipping_address_id: undefined,
    default_billing_address_id: undefined,
    edit_address_type: undefined,
});
const isNewAddress = ref(false);
const editAddressType = ref();

const loadedCountry = ref(false);
onMounted(async () => {

    // Get countries
    await settingsStore.fetchCountries();
    loadedCountry.value = true;

    // Fill address edit data
    if(props.params) {
        Object.keys(props.params).map(k => formdata.value[k] = props.params[k])
        editAddressType.value = formdata.value.edit_address_type;
		enterAddressManually.value = true;
    }

    // Set default country when empty
	const country = settingsStore.getCountryByCode(formdata.value.country_code);
	if(country) {
		formdata.value = { ...formdata.value, country_id: country.id }
		if(country.zones.length > 0) {
			const zone = country.zones.find(z => z.code === formdata.value.zone_code);
			formdata.value = { ...formdata.value, zone_id: zone.id };
		}
	}

    // Set default country when empty
    if(!formdata.value.country_id) {
        formdata.value.country_id = settingsStore.countries.find(country => country.id > 0).id;
    }

    // Add new address
    if(authCustomerStore.customerAddressTotal === 0) {
        isNewAddress.value = true;
    }

});

const editAddress = (address) => {
	
	enterAddressManually.value = true;
	
    // Uncheck the new address checkbox
    if(isNewAddress.value)  isNewAddress.value = false

    Object.keys(address).map(k => formdata.value[k] = address[k])

    // Assign country id
    const country = settingsStore.getCountryByCode(formdata.value.country_code)
    if(country) {
        formdata.value = { ...formdata.value, country_id: country.id }

        // Assign zone id
        if(country.zones.length > 0) {
            const zone = country.zones.find(z => z.code === formdata.value.zone_code);
            formdata.value = { ...formdata.value, zone_id: zone.id };
        }
    }
}

// Computed property for regions
const regions = computed(() => {
    const regionsList = settingsStore.getZonesByCountryId(formdata.value.country_id);
    if (regionsList.length > 0 && regionsList.find(r => r.id === formdata.value.zone_id) === undefined) {
        formdata.value.zone_id = regionsList.find(r => r.id > 0)?.id;
        formdata.value.zone_name = undefined;
    }
    return regionsList;
});

const handleAddress = async () => {
     // set country name
    const country = settingsStore.getCountryById(formdata.value.country_id);
    formdata.value = { ...formdata.value, ...{
        country_code: country.iso_code_2,
        country_name: country.name
    }};

    if(country.zones.length > 0) {
        const zone = country.zones.find(z => z.id === formdata.value.zone_id);
        if(zone) {
            formdata.value = { ...formdata.value, ...{
                zone_code: zone.code,
                zone_name: zone.name
            }}
        } else {
            formdata.value = { ...formdata.value, ...{
                zone_code: undefined,
                zone_id: undefined
            }}
        }
    }

    // Assign default address
    if(editAddressType.value === 'shipping') {
        formdata.value.default_shipping_address_id = 1
    } else if(editAddressType.value === 'billing') {
        formdata.value.default_billing_address_id = 1
    }

    // Create or update
    if(!isNewAddress.value) {
        formdata.value = { ...formdata.value, id: formdata.value.id}
        await authCustomerStore.updateCustomerAddress(formdata.value)
            .then(() => {
                toast.success(t("Updated address!"));
            })
            .catch(error => {
                toast.error(t(error.response.data.message));
            })
    } else {
        await authCustomerStore.createCustomerAddress(formdata.value)
            .then(() => {
                toast.success(t("Your new address has been added."));
            })
            .catch(error => {
                toast.error(t(error.response.data.message));
            })
    }

    // Turn on the checkout form
    emit('updateCheckoutStep', { step: CheckoutForm });
}

watch(
    () => isNewAddress.value,
    (v) => {
        if (v) {
			enterAddressManually.value = false;
			
            Object.keys(formdata.value).map(k => formdata.value[k] = undefined)
            if(!formdata.value.country_id) {
                formdata.value.country_id = settingsStore.countries.find(country => country.id > 0).id;
            }
        }
    }
);

const enterAddressManually = ref(false);
const radarQuery = ref('');
const onAddressSelected = (a) => {
	
	enterAddressManually.value = true;
	
    formdata.value.address_line_1 = a.address_line_1 || formdata.value.address_line_1;
    formdata.value.city = a.city || formdata.value.city;
    formdata.value.postcode = a.postalCode || formdata.value.postcode;

    // Map country
    if (a.countryCode) {
        const matchCountry = settingsStore.countries.find(
            c => (c.iso_code_2 || '').toUpperCase() === a.countryCode.toUpperCase()
        );
        if (matchCountry) {
            formdata.value.country_id = matchCountry.id;
        }
    }

    // Map zone state
    const country = settingsStore.getCountryById(formdata.value.country_id);
    if (country && Array.isArray(country.zones) && country.zones.length > 0) {
        const stCode = (a.stateCode || '').toUpperCase();
        const stName = (a.state || '').toUpperCase();

        const zByCode = country.zones.find(z => (z.code || '').toUpperCase() === stCode);
        const zByName = zByCode || country.zones.find(z => (z.name || '').toUpperCase() === stName);
        if (zByName) {
            formdata.value.zone_id = zByName.id;
            formdata.value.zone_name = undefined;
        } else {
            formdata.value.zone_id = undefined;
            formdata.value.zone_name = a.state || a.stateCode || '';
        }
    } else {
        formdata.value.zone_id = undefined;
        formdata.value.zone_name = a.state || a.stateCode || '';
    }
	
};

</script>

<template>
    <div>
        <div class="space-y-6" v-if="authCustomerStore.customerAddressTotal > 0">
            <div class="text-lg font-semibold mb-3">{{ $t('Your Addresses') }}</div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div v-for="address in authCustomerStore.customerAddresses" :key="address.id" class="rounded-lg shadow-sm p-4 flex flex-col h-full">
                    <DisplayAddress :address="address" class="flex-1" />
                    <div class="mt-4">
                        <button @click.stop="editAddress(address)" class="text-gray-800 hover:underline text-sm cursor-pointer">
                            {{ $t('Use this address for edit') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Horizontal Divider -->
            <div class="w-full border-t border-gray-200 mt-6"></div>
        </div>

        <!-- Section Title -->
        <div v-if="!editAddressType" class="text-lg font-semibold mb-8">
            {{ $t('Create or update address') }}
        </div>
        <div v-else class="text-lg font-semibold my-3">
            {{ $t('Create or update') }} {{ editAddressType }} {{ $t('address') }}
        </div>
        <div>
            <form @submit.prevent="handleAddress()" class="space-y-6">
                <!-- Checkbox Section -->
                <div v-if="authCustomerStore.customerAddressTotal > 0">
                    <div class="mb-4">
                        <div class="flex items-center">
                            <input 
                                v-model="isNewAddress" 
                                id="cb-create-new-address" 
                                type="checkbox" 
                                :value="false" 
                                class="form-checkbox h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-3 focus:ring-blue-500"
                            />
                            <label for="cb-create-new-address" class="ml-2 text-red-600 text-sm">
                                {{ $t('Check the box if you want to create a new address for your account') }}
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Input Rows -->
                <div class="grid grid-cols-1 md:grid-cols-7 gap-4">
                    <!-- Full Name -->
                    <div class="md:col-span-3">
						<div class="w-full">
							<div class="relative">
								<input v-model="formdata.name" id="name" type="text" :placeholder="$t('Full name')" class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required />

								<label for="name" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not(:placeholder-shown)]:top-[13px] peer-[&:not(:placeholder-shown)]:text-xs">
									{{ $t('Full name') }}
								</label>
							</div>
						</div>
                    </div>
					
					<!-- Phone -->
                    <div class="md:col-span-2">
                        <div class="w-full">
							<div class="relative">
								<input v-model="formdata.phone" id="phone" type="text" :placeholder="$t('Phone')" class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required />

								<label for="phone" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not(:placeholder-shown)]:top-[13px] peer-[&:not(:placeholder-shown)]:text-xs">
									{{ $t('Phone') }}
								</label>
							</div>
						</div>
                    </div>

                    <!-- Company -->
                    <div class="md:col-span-2">
                        <div class="w-full">
							<div class="relative">
								<input v-model="formdata.company" id="company" type="text" :placeholder="$t('Company')" class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" />

								<label for="company" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not(:placeholder-shown)]:top-[13px] peer-[&:not(:placeholder-shown)]:text-xs">
									{{ $t('Company') }} <span class="text-gray-400">(optional)</span>
								</label>
							</div>
						</div>
                    </div>
					
					<div v-if="!enterAddressManually" class="md:col-span-7">
						<AddressAutocomplete v-model="radarQuery" @selected="onAddressSelected" />
					</div>
		
					<div v-if="!enterAddressManually" class="md:col-span-7">
						<button @click.prevent="enterAddressManually = true" class="text-gray-900 underline cursor-pointer hover:text-gray-700">{{ $t('Enter address manually') }}</button>
					</div>
					
					<template v-if="enterAddressManually">
						<!-- Address Line 1 -->
						<div class="md:col-span-3">
							<div class="w-full">
								<div class="relative">
									<input v-model="formdata.address_line_1" id="address-line-1" type="text" :placeholder="$t('Street address')" class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required />

									<label for="address-line-1" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not(:placeholder-shown)]:top-[13px] peer-[&:not(:placeholder-shown)]:text-xs">
										{{ $t('Street address') }}
									</label>
								</div>
							</div>
						</div>

						<!-- Address Line 2 -->
						<div class="md:col-span-2">
							<div class="w-full">
								<div class="relative">
									<input v-model="formdata.address_line_2" id="address-line-2" type="text" :placeholder="$t('Apt/Suite')" class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" />

									<label for="address-line-2" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not(:placeholder-shown)]:top-[13px] peer-[&:not(:placeholder-shown)]:text-xs">
										{{ $t('Apt/Suite') }} <span class="text-gray-400">(optional)</span>
									</label>
								</div>
							</div>
						</div>

						<!-- City -->
						<div class="md:col-span-2">
							<div class="w-full">
								<div class="relative">
									<input v-model="formdata.city" id="city" type="text" :placeholder="$t('City')" class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required />

									<label for="city" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not(:placeholder-shown)]:top-[13px] peer-[&:not(:placeholder-shown)]:text-xs">
										{{ $t('City') }}
									</label>
								</div>
							</div>
						</div>

						<!-- Country -->
						<div class="md:col-span-2">
							<div v-if="loadedCountry" class="w-full">
								<div class="relative">
									<select v-model="formdata.country_id" id="country" class="peer block w-full appearance-none rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100 select-bg" required>
										<option v-for="(country, index) in settingsStore.countries" :key="index" :value="country.id">
											{{ country.name }}
										</option>
									</select>
									
									<label for="country" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not([value=''])]:top-[13px] peer-[&:not([value=''])]:text-xs peer-[&:not([value=''])]:text-gray-700">
										{{ $t('Country') }}
									</label>
								</div>
							</div>
							<div v-else class="h-12 w-full rounded-lg bg-gray-100 dark:bg-gray-800"></div>
						</div>

						<!-- Region -->
						<div class="md:col-span-3">
							<template v-if="loadedCountry">
								<div v-if="regions.length > 0" class="w-full">
									<div class="w-full">
										<div class="relative">
											<select v-model="formdata.zone_id" id="zone-id" required class="peer block w-full appearance-none rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100 select-bg">
												<option v-for="(region, index) in regions" :key="index" :value="region.id">
													{{ region.name }}
												</option>
											</select>
											
											<label for="zone-id" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not([value=''])]:top-[13px] peer-[&:not([value=''])]:text-xs peer-[&:not([value=''])]:text-gray-700">
												{{ $t('Region') }}
											</label>
										</div>
									</div>
								</div>
								<div v-else class="w-full">
									<div class="relative">
										<input v-model="formdata.zone_name" id="zone-name" type="text" :placeholder="$t('Region')" class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required />

										<label for="zone-name" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not(:placeholder-shown)]:top-[13px] peer-[&:not(:placeholder-shown)]:text-xs">
											{{ $t('Region') }}
										</label>
									</div>
								</div>
							</template>
							<div v-else class="h-12 w-full rounded-lg bg-gray-100 dark:bg-gray-800"></div>
						</div>

						<!-- Postcode -->
						<div class="md:col-span-2">
							<div class="w-full">
								<div class="relative">
									<input v-model="formdata.postcode" id="postcode" type="text" :placeholder="$t('Zip/Postcode')" class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required />

									<label for="postcode" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not(:placeholder-shown)]:top-[13px] peer-[&:not(:placeholder-shown)]:text-xs">
										{{ $t('Zip/Postcode') }}
									</label>
								</div>
							</div>
						</div>
					</template>
                </div>

                <!-- Checkboxes for Default Address -->
				<template v-if="authCustomerStore.customerAddressTotal > 0">
					<div v-if="isNewAddress && !editAddressType" class="space-y-4">
						<div class="flex items-center">
							<input 
								v-model="formdata.default_shipping_address_id" 
								id="cb-default-shipping-address" 
								type="checkbox" 
								class="form-checkbox h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-3 focus:ring-blue-500"
							/>
							<label for="cb-default-shipping-address" class="ml-2 text-gray-700">{{ $t('Set as default shipping address ') }}</label>
						</div>
						<div class="flex items-center">
							<input 
								v-model="formdata.default_billing_address_id" 
								id="cb-default-billing-address" 
								type="checkbox" 
								class="form-checkbox h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-3 focus:ring-blue-500"
							/>
							<label for="cb-default-billing-address" class="ml-2 text-gray-700">{{ $t('Set as default billing address ') }}</label>
						</div>
					</div>
				</template>

                <!-- Buttons -->
                <div class="flex justify-between items-center">
                    <button 
                        v-if="authCustomerStore.customerAddressTotal > 0" 
                        type="button" 
                        @click.stop="updateCheckoutComponent" 
                        class="text-sm text-gray-800 hover:underline cursor-pointer"
                    >
                        {{ $t('Ignore and continue to checkout') }}
                    </button>
                    <button 
                        type="submit" 
                        class="flex justify-center items-center rounded-lg bg-gray-800 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-800 transition-colors duration-200 cursor-pointer"
                    >
                        {{ $t('Save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>