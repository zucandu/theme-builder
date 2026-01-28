<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useToast } from 'vue-toastification';
import { useAuthCustomerStore } from '@/stores/auth/customer';
import { useRedirect } from '@/composables/useRedirect';
import { useRoute } from "vue-router";

const { t } = useI18n();
const toast = useToast();
const route = useRoute();
const authCustomerStore = useAuthCustomerStore();
const { redirectTo } = useRedirect();

// Redirect when customer logged
onMounted(() => {
    if(authCustomerStore.isLoggedIn) {
        redirectTo('/account');
    }
});

const formdata = ref({
    firstname: undefined,
    lastname: undefined,
    email: undefined,
    password: undefined,
    is_guest: 0,
    grecaptcha_token: undefined,
    newsletter: true,
    meta: null
});

const handleRegister = async () => {
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
    await authCustomerStore.registerCustomer(formdata.value)
        .then(async () => {
            await authCustomerStore.fetchCustomerInfo();
            if(route.query?.redirect) {
                 redirectTo(route.query.redirect);
            } else {
                redirectTo('/account');
            }
        })
        .catch(error => {
            toast.error(t(error.response.data.message));
        });
}

function updateMetaForm(obj) {
    formdata.value.meta = { ...formdata.meta, ...obj }
}
</script>

<template>
    <div>
        <div class="flex min-h-full flex-1 flex-col justify-center px-6 py-12 lg:px-8">
            <div class="sm:mx-auto sm:w-full sm:max-w-sm">
                <h2 class="text-center text-2xl/9 font-bold tracking-tight text-gray-900">{{ $t('Create a new account') }}</h2>
            </div>
            <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
                <form @submit.prevent="handleRegister" class="space-y-6">
                    <div class="grid grid-cols-2 gap-4">
						<div class="max-w-sm">
							<div class="relative">
								<input v-model="formdata.firstname" id="firstname" type="text" :placeholder="$t('First name')" class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" />

								<label for="firstname" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not(:placeholder-shown)]:top-[13px] peer-[&:not(:placeholder-shown)]:text-xs">
									{{ $t('First name') }}
								</label>
							</div>
						</div>
						<div class="max-w-sm">
							<div class="relative">
								<input v-model="formdata.lastname" id="lastname" type="text" :placeholder="$t('Last name')" class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" />

								<label for="lastname" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not(:placeholder-shown)]:top-[13px] peer-[&:not(:placeholder-shown)]:text-xs">
									{{ $t('Last name') }}
								</label>
							</div>
						</div>
                    </div>
					<div class="max-w-sm">
						<div class="relative">
							<input v-model="formdata.email" id="email" type="email" :placeholder="$t('Email address')" class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" />

							<label for="email" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not(:placeholder-shown)]:top-[13px] peer-[&:not(:placeholder-shown)]:text-xs">
								{{ $t('Email address') }}
							</label>
						</div>
					</div>
					<div class="max-w-sm">
						<div class="relative">
							<input v-model="formdata.password" id="password" type="password" :placeholder="$t('Password')" class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" />

							<label for="password" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not(:placeholder-shown)]:top-[13px] peer-[&:not(:placeholder-shown)]:text-xs">
								{{ $t('Password') }}
							</label>
						</div>
					</div>

                    <!-- Hook register -->
                    <template v-for="(component, index) in $pluginStorefrontHooks['register']" :key="index">
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
                    <div>
                        <button type="submit" class="w-full flex justify-center items-center rounded-lg bg-gray-800 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-800 transition-colors duration-200 cursor-pointer">
                            {{ $t('Create my account') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
