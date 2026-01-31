<script setup>
import CheckoutForm from '@theme/storefront/components/checkout/Form.vue'
import { ref, onMounted, computed, watch } from 'vue';
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
            Object.keys(formdata.value).map(k => formdata.value[k] = undefined)
            if(!formdata.value.country_id) {
                formdata.value.country_id = settingsStore.countries.find(country => country.id > 0).id;
            }
        }
    }
);

</script>

<template>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="space-y-8" v-if="authCustomerStore.customerAddressTotal > 0">
            <div class="flex items-center justify-between">
                 <h3 class="text-xl font-bold text-gray-900">{{ $t('Your Addresses') }}</h3>
            </div>
           
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div v-for="address in authCustomerStore.customerAddresses" :key="address.id" 
                    class="relative group rounded-xl border-2 p-5 flex flex-col items-start transition-all duration-200 cursor-pointer bg-white hover:border-blue-200 hover:shadow-md"
                    :class="formdata.id === address.id ? 'border-blue-600 bg-blue-50/50 ring-1 ring-blue-600' : 'border-gray-200'"
                    @click="editAddress(address)"
                >
                    <div class="absolute top-4 right-4">
                        <div v-if="formdata.id === address.id" class="w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                    </div>

                    <div class="flex-1 w-full">
                        <DisplayAddress :address="address" class="text-gray-600 text-sm leading-relaxed" />
                    </div>
                    
                    <div class="mt-4 w-full pt-4 border-t border-gray-100 flex items-center justify-between">
                         <span v-if="address.id === authCustomerStore.customerShippingAddress.id" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $t('Default') }}
                        </span>
                        <button @click.stop="editAddress(address)" class="text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors">
                            {{ $t('Edit this address') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Title -->
        <div v-if="authCustomerStore.customerAddressTotal > 0" class="relative my-10">
            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                <div class="w-full border-t border-gray-200"></div>
            </div>
            <div class="relative flex justify-center">
                <span class="bg-white px-3 text-base font-semibold text-gray-500">{{ $t('Or') }}</span>
            </div>
        </div>

        <div>
            <div class="mb-6">
                 <h3 v-if="!editAddressType" class="text-xl font-bold text-gray-900">{{ $t('Add New Address') }}</h3>
                 <h3 v-else class="text-xl font-bold text-gray-900">{{ $t('Update') }} {{ editAddressType }} {{ $t('Address') }}</h3>
            </div>
           
            <form @submit.prevent="handleAddress()" class="space-y-6">
                <!-- Checkbox Section -->
                <div v-if="authCustomerStore.customerAddressTotal > 0">
                    <label class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                        <input 
                            v-model="isNewAddress" 
                            type="checkbox" 
                            :value="false" 
                            class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        />
                        <span class="ml-3 text-gray-700 font-medium">
                            {{ $t('Create a new address for your account') }}
                        </span>
                    </label>
                </div>

                <!-- Input Rows -->
                <div class="grid grid-cols-1 md:grid-cols-6 gap-6">
                    <!-- Full Name -->
                    <div class="md:col-span-3">
						<div class="relative">
                            <input v-model="formdata.name" id="name" type="text" placeholder=" " class="peer block w-full rounded-lg border-gray-300 px-4 pb-2.5 pt-5 text-base text-gray-900 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 appearance-none bg-gray-50 border" required />
                            <label for="name" class="absolute left-4 top-4 z-10 origin-[0] -translate-y-2.5 scale-75 transform text-sm text-gray-500 duration-150 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-2.5 peer-focus:scale-75 peer-focus:text-blue-600">
                                {{ $t('Full name') }}
                            </label>
						</div>
                    </div>
					
					<!-- Phone -->
                    <div class="md:col-span-3">
                        <div class="relative">
                            <input v-model="formdata.phone" id="phone" type="text" placeholder=" " class="peer block w-full rounded-lg border-gray-300 px-4 pb-2.5 pt-5 text-base text-gray-900 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 appearance-none bg-gray-50 border" required />
                            <label for="phone" class="absolute left-4 top-4 z-10 origin-[0] -translate-y-2.5 scale-75 transform text-sm text-gray-500 duration-150 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-2.5 peer-focus:scale-75 peer-focus:text-blue-600">
                                {{ $t('Phone') }}
                            </label>
						</div>
                    </div>

                    <!-- Company -->
                    <div class="md:col-span-6">
                         <div class="relative">
                            <input v-model="formdata.company" id="company" type="text" placeholder=" " class="peer block w-full rounded-lg border-gray-300 px-4 pb-2.5 pt-5 text-base text-gray-900 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 appearance-none bg-gray-50 border" />
                            <label for="company" class="absolute left-4 top-4 z-10 origin-[0] -translate-y-2.5 scale-75 transform text-sm text-gray-500 duration-150 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-2.5 peer-focus:scale-75 peer-focus:text-blue-600">
                                {{ $t('Company') }} <span class="text-gray-400 font-normal">({{ $t('Optional') }})</span>
                            </label>
						</div>
                    </div>
					
					<!-- Address Line 1 -->
                    <div class="md:col-span-4">
                        <div class="relative">
                            <input v-model="formdata.address_line_1" id="address-line-1" type="text" placeholder=" " class="peer block w-full rounded-lg border-gray-300 px-4 pb-2.5 pt-5 text-base text-gray-900 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 appearance-none bg-gray-50 border" required />
                            <label for="address-line-1" class="absolute left-4 top-4 z-10 origin-[0] -translate-y-2.5 scale-75 transform text-sm text-gray-500 duration-150 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-2.5 peer-focus:scale-75 peer-focus:text-blue-600">
                                {{ $t('Street address') }}
                            </label>
                        </div>
                    </div>

                    <!-- Address Line 2 -->
                    <div class="md:col-span-2">
                         <div class="relative">
                            <input v-model="formdata.address_line_2" id="address-line-2" type="text" placeholder=" " class="peer block w-full rounded-lg border-gray-300 px-4 pb-2.5 pt-5 text-base text-gray-900 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 appearance-none bg-gray-50 border" />
                            <label for="address-line-2" class="absolute left-4 top-4 z-10 origin-[0] -translate-y-2.5 scale-75 transform text-sm text-gray-500 duration-150 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-2.5 peer-focus:scale-75 peer-focus:text-blue-600">
                                {{ $t('Apt/Suite') }} <span class="text-gray-400 font-normal">({{ $t('Optional') }})</span>
                            </label>
                        </div>
                    </div>

                    <!-- City -->
                    <div class="md:col-span-2">
                        <div class="relative">
                            <input v-model="formdata.city" id="city" type="text" placeholder=" " class="peer block w-full rounded-lg border-gray-300 px-4 pb-2.5 pt-5 text-base text-gray-900 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 appearance-none bg-gray-50 border" required />
                            <label for="city" class="absolute left-4 top-4 z-10 origin-[0] -translate-y-2.5 scale-75 transform text-sm text-gray-500 duration-150 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-2.5 peer-focus:scale-75 peer-focus:text-blue-600">
                                {{ $t('City') }}
                            </label>
                        </div>
                    </div>

                    <!-- Country -->
                    <div class="md:col-span-2">
                        <div v-if="loadedCountry" class="relative">
                            <select v-model="formdata.country_id" id="country" class="peer block w-full rounded-lg border-gray-300 px-4 pb-2.5 pt-5 text-base text-gray-900 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 appearance-none bg-gray-50 border" required>
                                <option v-for="(country, index) in settingsStore.countries" :key="index" :value="country.id">
                                    {{ country.name }}
                                </option>
                            </select>
                            <label for="country" class="absolute left-4 top-4 z-10 origin-[0] -translate-y-2.5 scale-75 transform text-sm text-gray-500 duration-150 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-2.5 peer-focus:scale-75 peer-focus:text-blue-600">
                                {{ $t('Country') }}
                            </label>
                             <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" fill-rule="evenodd"></path></svg>
                            </div>
                        </div>
                        <div v-else class="h-14 w-full rounded-lg bg-gray-100 animate-pulse"></div>
                    </div>

                    <!-- Region -->
                    <div class="md:col-span-2">
                        <template v-if="loadedCountry">
                            <div v-if="regions.length > 0" class="relative">
                                <select v-model="formdata.zone_id" id="zone-id" required class="peer block w-full rounded-lg border-gray-300 px-4 pb-2.5 pt-5 text-base text-gray-900 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 appearance-none bg-gray-50 border">
                                    <option v-for="(region, index) in regions" :key="index" :value="region.id">
                                        {{ region.name }}
                                    </option>
                                </select>
                                <label for="zone-id" class="absolute left-4 top-4 z-10 origin-[0] -translate-y-2.5 scale-75 transform text-sm text-gray-500 duration-150 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-2.5 peer-focus:scale-75 peer-focus:text-blue-600">
                                    {{ $t('Region') }}
                                </label>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                    <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" fill-rule="evenodd"></path></svg>
                                </div>
                            </div>
                            <div v-else class="relative">
                                <input v-model="formdata.zone_name" id="zone-name" type="text" placeholder=" " class="peer block w-full rounded-lg border-gray-300 px-4 pb-2.5 pt-5 text-base text-gray-900 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 appearance-none bg-gray-50 border" required />
                                <label for="zone-name" class="absolute left-4 top-4 z-10 origin-[0] -translate-y-2.5 scale-75 transform text-sm text-gray-500 duration-150 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-2.5 peer-focus:scale-75 peer-focus:text-blue-600">
                                    {{ $t('Region') }}
                                </label>
                            </div>
                        </template>
                         <div v-else class="h-14 w-full rounded-lg bg-gray-100 animate-pulse"></div>
                    </div>
                </div>
                 <!-- Postcode Row (Separate if needed or included above) -> Added to grid above? No, missed it. Adding here. -->
                 <div class="grid grid-cols-1 md:grid-cols-6 gap-6">
                     <div class="md:col-span-2">
                        <div class="relative">
                            <input v-model="formdata.postcode" id="postcode" type="text" placeholder=" " class="peer block w-full rounded-lg border-gray-300 px-4 pb-2.5 pt-5 text-base text-gray-900 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 appearance-none bg-gray-50 border" required />
                            <label for="postcode" class="absolute left-4 top-4 z-10 origin-[0] -translate-y-2.5 scale-75 transform text-sm text-gray-500 duration-150 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-2.5 peer-focus:scale-75 peer-focus:text-blue-600">
                                {{ $t('Zip/Postcode') }}
                            </label>
                        </div>
                    </div>
                 </div>


                <!-- Checkboxes for Default Address -->
				<div v-if="authCustomerStore.customerAddressTotal > 0 && isNewAddress && !editAddressType" class="space-y-3 bg-gray-50 p-4 rounded-lg border border-gray-100">
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input v-model="formdata.default_shipping_address_id" type="checkbox" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" />
                        <span class="text-sm text-gray-700">{{ $t('Set as default shipping address') }}</span>
                    </label>
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input v-model="formdata.default_billing_address_id" type="checkbox" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" />
                        <span class="text-sm text-gray-700">{{ $t('Set as default billing address') }}</span>
                    </label>
				</div>

                <!-- Buttons -->
                <div class="flex flex-col-reverse md:flex-row justify-between items-center gap-4 pt-6 mt-6 border-t border-gray-100">
                    <button 
                        v-if="authCustomerStore.customerAddressTotal > 0" 
                        type="button" 
                        @click.stop="updateCheckoutComponent" 
                        class="text-sm font-semibold text-gray-600 hover:text-gray-900 transition-colors"
                    >
                         {{ $t('Cancel') }}
                    </button>
                    <button 
                        type="submit" 
                        class="w-full md:w-auto px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 focus:ring-4 focus:ring-blue-500/30 transform active:scale-95"
                    >
                         {{ $t('Use this address') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>