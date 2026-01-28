<script setup>
import { ref } from 'vue';
import { useRoute } from 'vue-router';
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
});

const submitForm = async () => {
    await authCustomerStore.unsubscribeNewsletter(formdata.value)
        .then(() => {
            toast.success(t("You've been unsubscribed from our newsletter.")); 
            redirectTo('/');
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
                <h2 class="text-center text-2xl/9 font-bold tracking-tight text-gray-900">{{ $t("We're sorry to see you go!") }}</h2>
            </div>
            <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
                <form @submit.prevent="submitForm" class="space-y-6">
                    <div>
                        <label for="email" class="block text-sm/6 font-medium text-gray-900">{{ $t('Email address') }}</label>
                        <div class="mt-2">
                            <input v-model="formdata.email" id="email" name="email" type="email" autocomplete="email" required="" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-xs ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6 px-2" />
                        </div>
                    </div>
                    <div>
                        <button type="submit" class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm/6 font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">{{ $t("It's over!") }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
