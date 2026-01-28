<script setup>
import CheckoutForm from '@theme/storefront/components/checkout/Form.vue'
import AddressAutocomplete from '@theme/storefront/components/checkout/AddressAutocomplete.vue'
import { ref, onMounted, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useToast } from 'vue-toastification';
import { useSettingsStore } from '@/stores/settings';
import { useAuthCustomerStore } from '@/stores/auth/customer';

const toast = useToast();
const { t, locale } = useI18n();

const settingsStore = useSettingsStore();
const authCustomerStore = useAuthCustomerStore();

// Emit function for emitting events
const emit = defineEmits(["updateCheckoutStep"]);

const formdata = ref({
    firstname: undefined,
    lastname: undefined,
    company: undefined,
    address_line_1: undefined,
    address_line_2: undefined,
    city: undefined,
    phone: undefined,
    country_id: undefined,
    zone_id: undefined,
    zone_name: undefined,
    postcode: undefined,
    option: 'guest',
    password: undefined,
    passwordconfirm: undefined,
    grecaptcha_token: undefined,
    newsletter: false,
    meta: {}
});
const loadedCountry = ref(false);
onMounted(async () => {

    // Get countries
    await settingsStore.fetchCountries();
    loadedCountry.value = true;

    // Set default country when empty
    formdata.value.country_id ||= settingsStore.countries.find(country => country.id > 0)?.id;

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

const handleShippingAddress = async () => {

    if(zucConfig.recaptcha_site_key) {
        grecaptcha.ready(function() {
            grecaptcha.execute(zucConfig.recaptcha_site_key, { action: 'submit' }).then(function(token) {
                formdata.value.grecaptcha_token = token
            })
        })
        while(formdata.value.grecaptcha_token === undefined) {
            await new Promise(r => setTimeout(r, 100))
        }
    }

    // Set guest
    formdata.value = { ...formdata.value, is_guest: formdata.value.option === 'guest' ? 1 : 0 }

    await authCustomerStore.registerCustomer(formdata.value)
        .then(async () => {

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

            //
            // Set full name
            formdata.value = { ...formdata.value, name: `${formdata.value.firstname} ${formdata.value.lastname}` };

            await authCustomerStore.createCustomerAddress(formdata.value)
                .then(async () => {
                    await authCustomerStore.fetchCustomerInfo();
                    emit('updateCheckoutStep', { step: CheckoutForm });
                }).catch(error => {
                    toast.error(t(error.response.data.message));
                })

        })
        .catch(error => {
            toast.error(t(error.response.data.message));
        });


}

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

const emailAvailable = ref(false);
watch(
    () => formdata.value.email,
    async (nv, ov) => {
		
		// Autofill
		if(!ov && nv.length > 5) {
			enterAddressManually.value = true;
		}
		
        try {
            emailAvailable.value = await authCustomerStore.checkEmailAvailability({ email: formdata.value.email });
        } catch (err) {
            emailAvailable.value = false;
        }
    },
    { immediate: false }
)

function updateMetaForm(obj) {
    formdata.value.meta = { ...formdata.meta, ...obj }
}

</script>

<template>
    <form @submit.prevent="handleShippingAddress" class="space-y-4">
        <div class="text-lg font-semibold mb-4 font-bold uppercase tracking-widest">{{ t('Shipping Address') }}</div>
        <div class="mb-4 hidden">
            <div>
                <label class="block text-sm font-medium text-gray-700">{{ t('Checkout Options') }}</label>
            </div>
            <div class="flex items-center space-x-4 mt-2">
                <div class="flex items-center">
                    <input v-model="formdata.option" value="account" type="radio" id="checkout-register-account" class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                    <label for="checkout-register-account" class="ml-2 text-sm text-gray-700">{{ t('Register Account') }}</label>
                </div>
                <div class="flex items-center">
                    <input v-model="formdata.option" value="guest" type="radio" id="checkout-guest" class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                    <label for="checkout-guest" class="ml-2 text-sm text-gray-700">{{ t('Guest Checkout') }}</label>
                </div>
            </div>
        </div>
		
		<div class="w-full">
			<div class="relative">
				<input 
					v-model="formdata.email" 
					id="email" 
					type="email" 
					:placeholder="$t('Email address')" 
					class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" 
					autocomplete="off"
					required 
				/>

				<label for="email" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not(:placeholder-shown)]:top-[13px] peer-[&:not(:placeholder-shown)]:text-xs">
					{{ $t('Email address') }}
				</label>
			</div>
			<div class="text-sm text-gray-400 pt-1 px-3">
				<template v-if="!emailAvailable">
					{{ $t(`Become a dFd Member to get dFd Member Benefits`) }}. <LocalizedLink to="/login?redirect=/checkout" class="text-gray-900 underline hover:text-gray-700">{{ $t('Log in') }}</LocalizedLink> {{ $t(`or`) }} <LocalizedLink to="/register?redirect=/checkout" class="text-gray-900 underline hover:text-gray-700">{{ $t('sign up now') }}</LocalizedLink>
				</template>
				<template v-else>
					{{ $t(`It looks like you're a Member`) }}. <LocalizedLink to="/login?redirect=/checkout" class="text-gray-900 underline hover:text-gray-700">{{ $t('Log in') }}</LocalizedLink> {{ $t(`for dFd Members Benefits`) }}.
				</template>
			</div>
		</div>
		
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
			<div class="w-full">
				<div class="relative">
					<input v-model="formdata.firstname" id="firstname" type="text" :placeholder="$t('First Name')" class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required />

					<label for="firstname" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not(:placeholder-shown)]:top-[13px] peer-[&:not(:placeholder-shown)]:text-xs">
						{{ $t('First Name') }}
					</label>
				</div>
			</div>
			<div class="w-full">
				<div class="relative">
					<input v-model="formdata.lastname" id="lastname" type="text" :placeholder="$t('Last Name')" class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required />

					<label for="lastname" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not(:placeholder-shown)]:top-[13px] peer-[&:not(:placeholder-shown)]:text-xs">
						{{ $t('Last Name') }}
					</label>
				</div>
			</div>
        </div>
		
		<div v-if="!enterAddressManually">
			<AddressAutocomplete v-model="radarQuery" @selected="onAddressSelected" />
		</div>
		
		<div v-if="!enterAddressManually">
			<button @click.prevent="enterAddressManually = true" class="text-gray-900 underline cursor-pointer hover:text-gray-700">{{ $t('Enter address manually') }}</button>
		</div>
		
		<template v-if="enterAddressManually">
			<div class="grid grid-cols-1 md:grid-cols-12 gap-4">
				<div class="col-span-12 md:col-span-3">
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
				<div class="col-span-12 md:col-span-6">
					<div class="w-full">
						<div class="relative">
							<input v-model="formdata.address_line_1" id="address-line-1" type="text" :placeholder="$t('Street address')" class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required />

							<label for="address-line-1" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not(:placeholder-shown)]:top-[13px] peer-[&:not(:placeholder-shown)]:text-xs">
								{{ $t('Street address') }}
							</label>
						</div>
					</div>
				</div>
				<div class="col-span-12 md:col-span-3">
					<div class="w-full">
						<div class="relative">
							<input v-model="formdata.address_line_2" id="address-line-2" type="text" :placeholder="$t('Apt/Suite')" class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" />

							<label for="address-line-2" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not(:placeholder-shown)]:top-[13px] peer-[&:not(:placeholder-shown)]:text-xs">
								{{ $t('Apt/Suite') }} <span class="text-gray-400">(optional)</span>
							</label>
						</div>
					</div>
				</div>
			</div>
			<div class="grid grid-cols-1 md:grid-cols-12 gap-4">
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
				<div class="col-span-12 md:col-span-4">
					<div class="w-full">
						<div class="relative">
							<input v-model="formdata.city" id="city" type="text" :placeholder="$t('City')" class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required />

							<label for="city" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not(:placeholder-shown)]:top-[13px] peer-[&:not(:placeholder-shown)]:text-xs">
								{{ $t('City') }}
							</label>
						</div>
					</div>
				</div>
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
			</div>
		</template>
		
        <div class="w-full">
			<div class="relative">
				<input v-model="formdata.phone" id="phone" type="text" :placeholder="$t('Phone')" class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required />

				<label for="phone" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not(:placeholder-shown)]:top-[13px] peer-[&:not(:placeholder-shown)]:text-xs">
					{{ $t('Phone') }}
				</label>
			</div>
		</div>
		
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4" v-if="formdata.option === 'account'">
            <div class="col-span-12 md:col-span-6 mb-3">
                <div class="w-full">
					<div class="relative">
						<input v-model="formdata.password" id="password" type="password" :placeholder="$t('Password')" class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required/>

						<label for="password" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not(:placeholder-shown)]:top-[13px] peer-[&:not(:placeholder-shown)]:text-xs">
							{{ $t('Password') }}
						</label>
					</div>
				</div>
            </div>
            <div class="col-span-12 md:col-span-6 mb-3">
                <div class="w-full">
					<div class="relative">
						<input v-model="formdata.passwordconfirm" id="passwordconfirm" type="password" :placeholder="$t('Confirm Password')" class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required/>

						<label for="passwordconfirm" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not(:placeholder-shown)]:top-[13px] peer-[&:not(:placeholder-shown)]:text-xs">
							{{ $t('Confirm Password') }}
						</label>
					</div>
					<div v-if="(formdata.password !== formdata.passwordconfirm && formdata.passwordconfirm !== undefined)" class="mt-2 text-xs font-medium text-red-600 dark:text-red-400">
						{{ $t('Passwords do not match!') }}
					</div>
				</div>
            </div>
        </div>
		
        <template v-for="(component, index) in $pluginStorefrontHooks['checkout_create_account']" :key="index">
            <component :is="component" @updateMetaForm="updateMetaForm"></component>
        </template>
        <div class="relative flex gap-x-3">
            <div class="flex h-6 items-center">
                <input v-model="formdata.newsletter" id="newsletter" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600" />
            </div>
            <div class="text-sm/6">
                <label for="newsletter" class="font-medium text-gray-900">{{ $t('Subscribe to Newsletter?') }}</label>
            </div>
        </div>

        <div class="grid grid-cols-1">
            <div class="col-span-12 lg:text-right text-left">
				<button type="submit" class="inline-flex justify-center items-center rounded-lg bg-gray-800 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-800 transition-colors duration-200 cursor-pointer mb-2">{{ $t('Go to the shipping and payment methods') }}</button>
            </div>
        </div>

    </form>
</template>