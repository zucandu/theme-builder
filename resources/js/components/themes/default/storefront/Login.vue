<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useToast } from 'vue-toastification';
import { useRoute } from "vue-router";

import { useAuthCustomerStore } from '@/stores/auth/customer';
import { useRedirect } from '@/composables/useRedirect';

const { t } = useI18n();
const toast = useToast();
const authCustomerStore = useAuthCustomerStore();
const route = useRoute();
const { redirectTo } = useRedirect();

const formdata = ref({
    email: undefined,
    password: undefined
});

// Redirect when customer logged
onMounted(() => {
    if(authCustomerStore.isLoggedIn) {
        redirectTo('/account');
    }
});

const handleLogin = async () => {
    await authCustomerStore.loginCustomer(formdata.value)
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
        })
}
</script>

<template>
    <div>
        <MetaTags />
        <div class="flex min-h-full flex-1 flex-col justify-center px-6 py-12 lg:px-8">
            <div class="sm:mx-auto sm:w-full sm:max-w-sm">
                <h2 class="text-center text-2xl/9 font-bold tracking-tight text-gray-900">{{ $t('Sign in to your account') }}</h2>
            </div>
            <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
                <form @submit.prevent="handleLogin" class="space-y-6">
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
                    <div class="text-center">
                        <button type="submit" class="w-full flex justify-center items-center rounded-lg bg-gray-800 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-800 transition-colors duration-200 cursor-pointer mb-2">{{ $t('Sign in') }}</button>
						<LocalizedLink to="/forgot-password" class="text-gray-500 hover:underline text-sm">{{ $t('Forgot password?') }}</LocalizedLink>
                    </div>
					<div class="relative pt-2">
						<div class="absolute inset-0 flex items-center" aria-hidden="true">
							<span class="w-full border-t border-gray-200"></span>
						</div>
						<div class="relative flex justify-center">
							<span class="bg-white px-3 text-xs text-gray-400">{{ $t('or') }}</span>
						</div>
					</div>
					<div>
						<LocalizedLink
							to="/register"
							class="inline-flex w-full items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-800 hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-800 transition-colors duration-200"
						>
							{{ $t('Create an account') }}
						</LocalizedLink>
					</div>
                </form>
            </div>
        </div>
    </div>
</template>