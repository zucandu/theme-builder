<script setup>
import { onMounted } from 'vue';
import { useAuthCustomerStore } from '@/stores/auth/customer';

const authCustomerStore = useAuthCustomerStore();
authCustomerStore.clearCustomerAuth();
</script>

<template>
    <div>
        <MetaTags />
        <div class="flex items-center justify-center min-h-screen">
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-800">{{ $t("You're logged off") }}</h1>
                <p class="text-gray-600 mt-2">{{ $t("You have successfully logged out of your account.") }}</p>
            </div>
        </div>
    </div>
</template>
