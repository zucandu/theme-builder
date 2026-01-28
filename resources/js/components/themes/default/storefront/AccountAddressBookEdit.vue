<script setup>
import AddressForm from '@theme/storefront/components/account/AddressForm.vue'
</script>

<template>
    <div>
        <div class="flex-1 flex flex-col min-h-full w-full rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
            <div class="px-6 py-6 space-y-8">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $t('Edit address') }}
                </h1>
                <AddressForm />
            </div>
        </div>
    </div>
</template>