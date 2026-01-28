<script setup>
import { ref, onMounted } from 'vue';
import AccountMenu from '@theme/storefront/components/account/Menu.vue';
import { useAuthCustomerStore } from '@/stores/auth/customer';
import { useRedirect } from '@/composables/useRedirect';

const authCustomerStore = useAuthCustomerStore();
const { redirectTo } = useRedirect();

onMounted(() => {
    if(!authCustomerStore.isLoggedIn) {
        redirectTo('/login');
    }
});

const upgrading = ref(false);
const upgradeGuestToAccount = async () => {
    await authCustomerStore.upgradeGuestToAccount();
    upgrading.value = true;
}
</script>

<template>
    <div v-if="authCustomerStore.isLoggedIn">
        <div class="flex min-h-full flex-1 flex-col justify-center lg:py-12 px-4">
            <div v-if="authCustomerStore.isRegisteredCustomer" class="grid grid-cols-12 gap-6 lg:min-h-96">
                <div class="account-nav lg:col-span-3 col-span-12">
                    <AccountMenu></AccountMenu>
                </div>
                <div class="lg:col-span-9 col-span-12">
                    <router-view></router-view>
                </div>
            </div>
            <div v-else class="flex flex-col text-center gap-4">
                <div class="text-2xl">{{ $t("You're viewing the account page as a guest") }}</div>
                <p>{{ $t('To receive more offers and promotional emails, we suggest you create an account by clicking the "Create an account" button below. You will receive an email containing the password.') }}</p>
                <div><button :disabled="upgrading" class="px-6 py-3 text-white text-lg font-medium bg-blue-500 hover:bg-blue-600 disabled:bg-blue-300 rounded-lg transition-all" @click.stop="upgradeGuestToAccount">{{ $t('Create an account') }}</button></div>
            </div>
        </div>
    </div>
</template>