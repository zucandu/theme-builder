<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useToast } from 'vue-toastification';
import { useAuthCustomerStore } from '@/stores/auth/customer';
import { useRedirect } from '@/composables/useRedirect';

const toast = useToast();
const { t } = useI18n();
const authCustomerStore = useAuthCustomerStore();
const { redirectTo } = useRedirect();

const formdata = ref({
    email: undefined,
    grecaptcha_token: undefined
});

// Form submitted
const resetPassword = async () => {

    if(zucConfig.recaptcha_site_key) {
        grecaptcha.ready(function() {
            grecaptcha.execute(zucConfig.recaptcha_site_key, { action: 'submit' }).then(function(token) {
                formdata.grecaptcha_token = token
            })
        })
        while(formdata.grecaptcha_token === undefined) {
            await new Promise(r => setTimeout(r, 100))
        }
    }

    await authCustomerStore.resetCustomerPassword(formdata.value)
        .then(() => {
            toast.success(t("We have e-mailed your password reset link!")); 
            redirectTo('/login');

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
                <h2 class="text-center text-2xl/9 font-bold tracking-tight text-gray-900">{{ $t('Password Forgotten') }}</h2>
            </div>
            <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
                <form @submit.prevent="resetPassword">
					<div class="max-w-sm">
						<div class="relative">
							<input v-model="formdata.email" id="email" type="email" :placeholder="$t('Email address')" class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" />

							<label for="email" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not(:placeholder-shown)]:top-[13px] peer-[&:not(:placeholder-shown)]:text-xs">
								{{ $t('Email address') }}
							</label>
						</div>
					</div>
                    <button type="submit" class="mt-2 w-full flex justify-center items-center rounded-lg bg-gray-800 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-800 transition-colors duration-200 cursor-pointer mb-2">{{ $t('Reset') }}</button>
                </form>
            </div>
        </div>
    </div>
</template>