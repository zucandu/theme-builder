<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useToast } from 'vue-toastification';
import { useAuthCustomerStore } from '@/stores/auth/customer';
import { useRedirect } from '@/composables/useRedirect';

const router = useRouter();
const { t } = useI18n();
const toast = useToast();
const authCustomerStore = useAuthCustomerStore();
const { redirectTo } = useRedirect();

const formdata = ref({
    password: undefined,
    passwordconfirm: undefined,
});

// Reactive state to check if passwords match
const isPasswordMismatch = computed(() => 
    formdata.value.password && 
    formdata.value.passwordconfirm && 
    formdata.value.password !== formdata.value.passwordconfirm
);

const updatePassword = async () => {
    await authCustomerStore.updateAccountPassword(formdata.value)
        .then(() => {
            toast.success(t('Updated!'));
            
            // Reset the outer formdata
            formdata.value = {
                password: undefined,
                passwordconfirm: undefined,
            };
        })
        .catch(error => {
            toast.error(t(error.response.data.message));
        });

}

const showPwd1 = ref(false);
const showPwd2 = ref(false);

const togglePwd1 = () => { showPwd1.value = !showPwd1.value; };
const togglePwd2 = () => { showPwd2.value = !showPwd2.value; };
</script>
<template>
    <div>
        <div class="flex-1 flex flex-col min-h-full w-full rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
            <div class="px-6 py-6 space-y-6">
			
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $t('Update Password') }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        {{ $t('Use a strong password you don’t use elsewhere.') }}
                    </p>
                </div>

                <form @submit.prevent="updatePassword()" class="space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
						<!-- Password -->
						<div class="w-full">
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
						<!--
						<div class="w-full">
							<div class="relative">
								<input v-model="formdata.passwordconfirm" id="passwordconfirm" type="password" :placeholder="$t('Confirm Password')" class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required/>

								<label for="passwordconfirm" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not(:placeholder-shown)]:top-[13px] peer-[&:not(:placeholder-shown)]:text-xs">
									{{ $t('Confirm Password') }}
								</label>
							</div>
							<div v-if="isPasswordMismatch" class="mt-2 text-xs font-medium text-red-600 dark:text-red-400">
								{{ $t('Passwords do not match!') }}
							</div>
						</div>
						-->
						<!-- Password confirm -->
						<div class="w-full">
							<div class="relative">
								<input
									v-model="formdata.passwordconfirm"
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
                    </div>

                    <div class="rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800 p-4">
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200 mb-2">
                            {{ $t('Password tips') }}
                        </p>
                        <ul class="list-disc pl-5 space-y-1 text-sm text-gray-600 dark:text-gray-300">
                            <li>{{ $t('At least 8 characters.') }}</li>
                            <li>{{ $t('Use a mix of upper/lowercase letters and numbers.') }}</li>
                            <li>{{ $t('Avoid common words or personal info.') }}</li>
                        </ul>
                    </div>

                    <div>
                        <button
                            :disabled="isPasswordMismatch"
                            type="submit"
                            class="w-full flex justify-center items-center rounded-lg bg-gray-800 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-800 transition-colors duration-200 cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed"
                            :aria-disabled="isPasswordMismatch ? 'true' : 'false'"
                        >
                            {{ $t('Update') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
