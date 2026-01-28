<script setup>
import { ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useToast } from 'vue-toastification';
import { useAuthCustomerStore } from '@/stores/auth/customer';
import { useRedirect } from '@/composables/useRedirect';

const toast = useToast();
const { t } = useI18n();
const authCustomerStore = useAuthCustomerStore();
const { redirectTo } = useRedirect();
const route = useRoute();

const formdata = ref({
    password: undefined,
    password_confirmation: undefined,
    token: undefined
});

// Reactive state to check if passwords match
const isPasswordMismatch = computed(() => 
    formdata.value.password && 
    formdata.value.password_confirmation && 
    formdata.value.password !== formdata.value.password_confirmation
);

const updatePassword = async() => {
    await authCustomerStore.updateCustomerPassword({ ...formdata.value, token: route.params.token})
        .then(() => {
            toast.success(t("Your password has been reset. Please login and happy shopping!")); 
            redirectTo('/login');
        })
        .catch(error => {
            toast.error(t(error.response.data.message));
        })
    
}

const showPwd1 = ref(false);
const showPwd2 = ref(false);

const togglePwd1 = () => { showPwd1.value = !showPwd1.value; };
const togglePwd2 = () => { showPwd2.value = !showPwd2.value; };

</script>

<template>
    <div class="flex min-h-full flex-1 flex-col justify-center px-6 py-12 lg:px-8">
		<div class="sm:mx-auto sm:w-full sm:max-w-sm">
			<h2 class="text-center text-2xl/9 font-bold tracking-tight text-gray-900">{{ $t('Updated Your Password') }}</h2>
		</div>
		<div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
			<form @submit.prevent="updatePassword" class="space-y-6">
				<!-- Password -->
				<div class="max-w-sm">
					<div class="relative">
						<input
							v-model="formdata.password"
							id="password"
							:type="showPwd1 ? 'text' : 'password'"
							:placeholder="$t('Password')"
							autocomplete="new-password"
							class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 pr-12 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
						/>
						<label
							for="password"
							class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out
								   peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm
								   peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700
								   peer-[&:not(:placeholder-shown)]:top-[13px] peer-[&:not(:placeholder-shown)]:text-xs">
							{{ $t('Password') }}
						</label>

						<!-- Eye button -->
						<button
							type="button"
							:aria-label="showPwd1 ? $t('Hide password') : $t('Show password')"
							class="absolute right-2 top-1/2 -translate-y-1/2 h-8 w-8 rounded-md flex items-center justify-center
								   text-gray-500 hover:text-gray-700 focus:outline-none cursor-pointer"
							@click="togglePwd1"
						>
							<svg v-if="!showPwd1" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
									  d="M2.25 12s3.75-7.5 9.75-7.5S21.75 12 21.75 12s-3.75 7.5-9.75 7.5S2.25 12 2.25 12z"/>
								<circle cx="12" cy="12" r="3" stroke-width="1.6" />
							</svg>
							<svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
									  d="M3 3l18 18M6.2 6.2C3.9 7.9 2.25 12 2.25 12s3.75 7.5 9.75 7.5c2.1 0 3.99-.57 5.6-1.5M13.5 13.5A3 3 0 0 1 10.5 10.5"/>
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
									  d="M16.5 7.9c-1.22-1.07-2.77-1.9-4.5-1.9"/>
							</svg>
						</button>
					</div>
				</div>
				<!-- Password confirm -->
				<div class="max-w-sm">
					<div class="relative">
						<input
							v-model="formdata.password_confirmation"
							id="password-confirm"
							:type="showPwd2 ? 'text' : 'password'"
							:placeholder="$t('Password confirm')"
							autocomplete="new-password"
							class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 pr-12 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
						/>
						<label
							for="password-confirm"
							class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out
								   peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm
								   peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700
								   peer-[&:not(:placeholder-shown)]:top-[13px] peer-[&:not(:placeholder-shown)]:text-xs">
							{{ $t('Confirmation') }}
						</label>

						<!-- Eye button -->
						<button
							type="button"
							:aria-label="showPwd2 ? $t('Hide password') : $t('Show password')"
							class="absolute right-2 top-1/2 -translate-y-1/2 h-8 w-8 rounded-md flex items-center justify-center
								   text-gray-500 hover:text-gray-700 focus:outline-none cursor-pointer"
							@click="togglePwd2"
						>
							<svg v-if="!showPwd2" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
									  d="M2.25 12s3.75-7.5 9.75-7.5S21.75 12 21.75 12s-3.75 7.5-9.75 7.5S2.25 12 2.25 12z"/>
								<circle cx="12" cy="12" r="3" stroke-width="1.6" />
							</svg>
							<svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
									  d="M3 3l18 18M6.2 6.2C3.9 7.9 2.25 12 2.25 12s3.75 7.5 9.75 7.5c2.1 0 3.99-.57 5.6-1.5M13.5 13.5A3 3 0 0 1 10.5 10.5"/>
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
									  d="M16.5 7.9c-1.22-1.07-2.77-1.9-4.5-1.9"/>
							</svg>
						</button>
					</div>

					<div v-if="isPasswordMismatch" class="text-red-500">
						{{ $t('Passwords do not match!') }}
					</div>
				</div>
				<div class="grid grid-cols-2 gap-4 mt-6 items-center">
					<div>
						<LocalizedLink to="/login" class="text-blue-600 hover:underline">{{ $t('Back to login') }}</LocalizedLink>
					</div>
					<div class="text-right">
						<button type="submit" class="cursor-pointer flex w-full justify-center bg-indigo-600 px-3 py-1.5 text-sm/6 font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">{{ $t('Update') }}</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</template>