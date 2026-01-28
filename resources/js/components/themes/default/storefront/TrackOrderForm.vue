<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useToast } from 'vue-toastification';
import { useOrderStore } from '@/stores/order';
import { useRedirect } from '@/composables/useRedirect';

const router = useRouter();
const { t } = useI18n();
const toast = useToast();
const orderStore = useOrderStore();
const { redirectTo } = useRedirect();

const formdata = ref({
    order_id: undefined,
    email: undefined,
    grecaptcha_token: undefined,
});

const trackNow = async () => {
    
    try {

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

        const verified = await orderStore.verify(formdata.value);
        redirectTo(`/track-order/${verified.ref}`);
    } catch (error) {
        toast.error(t(error.response?.data?.message || "An error occurred while sending your message."));
    }
}
</script>

<template>
    <div>
        <MetaTags />
        <div class="flex min-h-full flex-1 flex-col justify-center px-6 py-12 lg:px-8">
            <div class="sm:mx-auto sm:w-full sm:max-w-sm">
                <h2 class="text-center text-2xl/9 font-bold tracking-tight text-gray-900">{{ $t('Track Your Order') }}</h2>
            </div>
            <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
                <form @submit.prevent="trackNow" class="space-y-6">
					<div class="max-w-sm">
						<div class="relative">
							<input v-model="formdata.order_id" id="order-id" type="text" :placeholder="$t('Order ID')" class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required />

							<label for="order-id" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not(:placeholder-shown)]:top-[13px] peer-[&:not(:placeholder-shown)]:text-xs">
								{{ $t('Order ID') }}
							</label>
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
                    <button type="submit" class="w-full flex justify-center items-center rounded-lg bg-gray-800 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-800 transition-colors duration-200 cursor-pointer mb-2">{{ $t('Track Now') }}</button>
                </form>
            </div>
        </div>
    </div>
</template>