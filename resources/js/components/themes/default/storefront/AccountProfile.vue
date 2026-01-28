<script setup>
import AvatarUploader from '@theme/storefront/components/account/AvatarUploader.vue'
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useToast } from 'vue-toastification';
import { useAuthCustomerStore } from '@/stores/auth/customer';

const { t } = useI18n();
const toast = useToast();
const authCustomerStore = useAuthCustomerStore();

const formdata = ref({
    firstname: undefined,
    lastname: undefined,
    email: undefined,
    avatar: undefined,
    newsletter: 0,
    meta: null
});

onMounted(() => {
    formdata.value = {
        ...authCustomerStore.profile,
        newsletter: !!+authCustomerStore.profile.newsletter,
    };
});

const updateProfile = async () => {
    await authCustomerStore.updateCustomerProfile(formdata.value)
        .then(() => {
            toast.success(t('Updated!'));
        })
        .catch(error => {
            toast.error(t(error.response.data.message));
        });
}

function updateContent(v) {
    formdata.value[v.field] = v.content
}

function updateMetaForm(obj) {
    formdata.value.meta = { ...formdata.meta, ...obj }
}
</script>

<template>
    <div>
        <div class="flex-1 flex flex-col min-h-full w-full rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
            <div class="px-6 py-6 space-y-8">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $t('Profile') }}
                </h1>
                <form @submit.prevent="updateProfile()" class="space-y-4">
                    <div class="w-64">
                        <AvatarUploader type="single" :current-images="[authCustomerStore.profile.avatar]" @updateContent="updateContent" />
                    </div>
                    <div class="grid lg:grid-cols-3 gap-4">
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
                        <div class="max-w-sm">
							<div class="relative">
								<input v-model="formdata.email" id="email" type="email" :placeholder="$t('Email address')" class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" />

								<label for="email" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not(:placeholder-shown)]:top-[13px] peer-[&:not(:placeholder-shown)]:text-xs">
									{{ $t('Email address') }}
								</label>
							</div>
						</div>
                    </div>

                    <!-- Used to hook into the account profile input. -->
                    <template v-for="(component, index) in $pluginStorefrontHooks['account_profile']" :key="index">
                        <component :is="component" @updateMetaForm="updateMetaForm" :profile="authCustomerStore.profile"></component>
                    </template>

                    <div class="relative flex gap-x-3">
                        <div class="flex h-6 items-center">
                            <input v-model="formdata.newsletter" :value="1" id="newsletter" name="newsletter" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600" />
                        </div>
                        <div class="text-sm/6">
                            <label for="newsletter" class="font-medium text-gray-900">{{ $t('Subscribe me to newsletter.') }}</label>
                        </div>
                    </div>
                    <div class="text-right">
                        <button type="submit" class="ml-auto flex justify-center items-center rounded-lg bg-gray-800 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-800 transition-colors duration-200 cursor-pointer">
                            {{ $t('Update') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
