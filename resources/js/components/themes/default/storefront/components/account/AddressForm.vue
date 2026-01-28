<script setup>
import { ref, onMounted, computed } from 'vue';

import AddressAutocomplete from '@theme/storefront/components/checkout/AddressAutocomplete.vue'

import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';

import { useToast } from 'vue-toastification';
import { useSettingsStore } from '@/stores/settings';
import { useAuthCustomerStore } from '@/stores/auth/customer';
import { useRedirect } from '@/composables/useRedirect';

const route = useRoute();
const { t } = useI18n();
const toast = useToast();
const settingsStore = useSettingsStore();
const authCustomerStore = useAuthCustomerStore();
const { redirectTo } = useRedirect();

// Set variables
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
    default_billing_address_id: undefined
});
const isShippingAddress = ref(false);
const isBillingAddress = ref(false);
const loadedCountry = ref(false);
onMounted(async () => {
    
    // Get countries
    await settingsStore.fetchCountries();
    loadedCountry.value = true;

    const currentAddress = authCustomerStore.customerInfo.addresses?.find(addr => +addr.id === +route.params.id);
	if(currentAddress) {
		for (const [k, v] of Object.entries(currentAddress)) {
			if (k in formdata.value) {
				formdata.value[k] = v
			}
		}
	}

    // Set billing/shipping
    isShippingAddress.value = +authCustomerStore.customerInfo.default_shipping_address_id === +route.params.id;
    isBillingAddress.value = +authCustomerStore.customerInfo.default_billing_address_id === +route.params.id;

    // Set country and zone from customer info
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
        formdata.value.country_id = settingsStore.countries.find(country => country.id > 0).id
    }
});

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

    const country = settingsStore.getCountryById(formdata.value.country_id);
    formdata.value = { ...formdata.value, ...{
        country_code: country.iso_code_2,
        country_name: country.name
    }};
    
    // Get zone id
    if(country.zones.length > 0) {
        const zone = country.zones.find(z => +z.id === +formdata.value.zone_id);
        formdata.value = { ...formdata.value, ...{
            zone_code: zone.code,
            zone_name: zone.name
        }};
    } else {
        // Reset zone id and code
        formdata.value = { ...formdata.value, ...{
            zone_code: undefined,
            zone_id: undefined
        }};
    }

    if(+route.params.id) {
        await authCustomerStore.updateCustomerAddress(formdata.value).then(() => {
            toast.success(t("Address has been updated!"));
            redirectTo('/account/address-book');
        })
        .catch(error => {
            toast.error(t(error.response.data.message));
        });
    } else {
        await authCustomerStore.createCustomerAddress(formdata.value).then(() => {
            toast.success(t("You have just add a new address!"));
            redirectTo('/account/address-book');
        })
        .catch(error => {
            toast.error(t(error.response.data.message));
        });
    }
}

const radarQuery = ref('');
const onAddressSelected = (a) => {
	formdata.value.address_line_1 = a.address_line_1 || a.raw?.addressLabel || '';
    formdata.value.address_line_2 = a.address_line_2 || a.raw?.dependentLocality || '';
    formdata.value.city = a.city || '';
    formdata.value.postcode = a.postalCode || '';

    // Country
    formdata.value.country_code = a.countryCode || undefined;

    const country = settingsStore.getCountryByCode(a.countryCode);
    if (country) {
        formdata.value.country_id = country.id;
        formdata.value.country_name = country.name;

        // Zone / State
        if (country.zones && country.zones.length > 0) {
            // try match by code trước, không được thì match name
            const zone =
                country.zones.find(z => z.code === a.stateCode) ||
                country.zones.find(z => z.name === a.state);

            if (zone) {
                formdata.value.zone_id = zone.id;
                formdata.value.zone_code = zone.code;
                formdata.value.zone_name = zone.name;
            } else {
                // Không match được zone thì clear
                formdata.value.zone_id = undefined;
                formdata.value.zone_code = undefined;
                formdata.value.zone_name = undefined;
            }
        } else {
            // Country không có zone
            formdata.value.zone_id = undefined;
            formdata.value.zone_code = undefined;
            formdata.value.zone_name = undefined;
        }
    }
}

</script>
<template>
    <div>
        <form @submit.prevent="handleAddress()">
            <div class="grid grid-cols-12 gap-4 md:gap-5">
                <!-- Full name -->
                <div class="col-span-12 md:col-span-7">
					<div class="w-full">
						<div class="relative">
							<input v-model="formdata.name" id="name" type="text" :placeholder="$t('Full name')" class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required />

							<label for="name" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not(:placeholder-shown)]:top-[13px] peer-[&:not(:placeholder-shown)]:text-xs">
								{{ $t('Full name') }}
							</label>
						</div>
					</div>
                </div>

                <!-- Company (optional) -->
                <div class="col-span-12 md:col-span-5">
					<div class="w-full">
						<div class="relative">
							<input v-model="formdata.company" id="company" type="text" :placeholder="$t('Company')" class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" />

							<label for="company" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not(:placeholder-shown)]:top-[13px] peer-[&:not(:placeholder-shown)]:text-xs">
								{{ $t('Company') }} <span class="text-gray-400">(optional)</span>
							</label>
						</div>
					</div>
                </div>
				
				<div class="col-span-12">
					<AddressAutocomplete v-model="radarQuery" @selected="onAddressSelected" />
				</div>

                <!-- Street address -->
                <div class="col-span-12 md:col-span-7">
					<div class="w-full">
						<div class="relative">
							<input v-model="formdata.address_line_1" id="address-line-1" type="text" :placeholder="$t('Street address')" class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required />

							<label for="address-line-1" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not(:placeholder-shown)]:top-[13px] peer-[&:not(:placeholder-shown)]:text-xs">
								{{ $t('Street address') }}
							</label>
						</div>
					</div>
                </div>

                <!-- Apt/Suite (optional) -->
                <div class="col-span-12 md:col-span-5">
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
                <div class="col-span-12 md:col-span-7">
					<div class="w-full">
						<div class="relative">
							<input v-model="formdata.city" id="city" type="text" :placeholder="$t('City')" class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required />

							<label for="city" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not(:placeholder-shown)]:top-[13px] peer-[&:not(:placeholder-shown)]:text-xs">
								{{ $t('City') }}
							</label>
						</div>
					</div>
                </div>

                <!-- Phone -->
                <div class="col-span-12 md:col-span-5">
					<div class="w-full">
						<div class="relative">
							<input v-model="formdata.phone" id="phone" type="text" :placeholder="$t('Phone')" class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required />

							<label for="phone" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not(:placeholder-shown)]:top-[13px] peer-[&:not(:placeholder-shown)]:text-xs">
								{{ $t('Phone') }}
							</label>
						</div>
					</div>
                </div>

                <!-- Country -->
                <div class="col-span-12 md:col-span-4">
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
                <div class="col-span-12 md:col-span-4">
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

                <!-- Zip/Postcode -->
                <div class="col-span-12 md:col-span-4">
					<div class="w-full">
						<div class="relative">
							<input v-model="formdata.postcode" id="postcode" type="text" :placeholder="$t('Zip/Postcode')" class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required />

							<label for="postcode" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not(:placeholder-shown)]:top-[13px] peer-[&:not(:placeholder-shown)]:text-xs">
								{{ $t('Zip/Postcode') }}
							</label>
						</div>
					</div>
                </div>

                <!-- Defaults -->
                <template v-if="+authCustomerStore.customerAddressTotal > 0">
                    <div v-if="!isShippingAddress" class="col-span-12">
                        <label class="inline-flex items-center gap-3">
                            <input
                                v-model="formdata.default_shipping_address_id"
                                id="default-shipping-address-id"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                            />
                            <span class="text-sm text-gray-900 dark:text-gray-100">
                                {{ $t('Set as default shipping address') }}
                            </span>
                        </label>
                    </div>

                    <div v-if="!isBillingAddress" class="col-span-12">
                        <label class="inline-flex items-center gap-3">
                            <input
                                v-model="formdata.default_billing_address_id"
                                id="default-billing-address-id"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                            />
                            <span class="text-sm text-gray-900 dark:text-gray-100">
                                {{ $t('Set as default billing address') }}
                            </span>
                        </label>
                    </div>
                </template>

                <!-- Actions -->
                <div class="col-span-12 flex flex-wrap items-center gap-4 pt-2">
                    <button
                        type="submit"
                        class="flex justify-center items-center rounded-lg bg-gray-800 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-800 transition-colors duration-200 cursor-pointer"
                    >
                        <template v-if="route.params.id">{{ $t('Update') }}</template>
                        <template v-else>{{ $t('Add now') }}</template>
                    </button>

                    <LocalizedLink
                        to="/account/address-book"
                        class="text-sm font-medium text-gray-800 hover:text-gray-600 hover:underline"
                    >
                        {{ $t('Back to address book') }}
                    </LocalizedLink>
                </div>
            </div>
        </form>
    </div>
</template>
