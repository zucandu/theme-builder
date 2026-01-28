<script setup>
import { useToast } from 'vue-toastification';
import { useI18n } from 'vue-i18n';
import { useAuthCustomerStore } from '@/stores/auth/customer';

const toast = useToast();
const { t } = useI18n();
const authCustomerStore = useAuthCustomerStore();

const deleteAddress = async (id) => {
    let isConfirm = confirm("Are you sure you want to delete this address?");
    if(isConfirm) {
        await authCustomerStore.deleteCustomerAddress(id)
            .then(() => {
                toast.success(t("Your address has been deleted!"));
            })
            .catch(error => {
                toast.error(t(error.response.data.message));
            });
    }
}

</script>
<template>
    <div>
        <div class="flex-1 flex flex-col min-h-full w-full rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
            <div class="px-6 py-6 space-y-8">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $t('Address Book') }}
                </h1>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                                {{ $t('Current Shipping Address') }}
                            </h2>
                            <div class="rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800 p-4">
                                <DisplayAddress :address="authCustomerStore.customerShippingAddress" />
                            </div>
                        </div>

                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                                {{ $t('Current Billing Address') }}
                            </h2>
                            <div class="rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800 p-4">
                                <DisplayAddress :address="authCustomerStore.customerBillingAddress" />
                            </div>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
                            {{ $t('All addresses') }}
                        </h2>

                        <div v-if="authCustomerStore.customerAddressTotal > 0" class="space-y-4">
                            <div
                                v-for="address in authCustomerStore.customerAddresses"
                                :key="address.id"
                                class="rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5"
                            >
                                <DisplayAddress :address="address" />

                                <div class="mt-4 flex flex-wrap items-center gap-3">
                                    <LocalizedLink
                                        :to="`/account/address-book/${address.id}`"
                                        class="inline-flex items-center rounded-lg border border-gray-300 dark:border-gray-700
                                               px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50
                                               dark:text-gray-200 dark:hover:bg-gray-800
                                               focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900"
                                    >
                                        {{ $t('Edit') }}
                                    </LocalizedLink>

                                    <button
                                        @click.stop="deleteAddress(address.id)"
                                        type="button"
                                        class="flex justify-center items-center rounded-lg bg-gray-800 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-800 transition-colors duration-200 cursor-pointer"
                                    >
                                        {{ $t('Delete') }}
                                    </button>
                                </div>
                            </div>

                            <div class="pt-2">
                                <LocalizedLink
                                    to="/account/address-book/new"
                                    class="inline-flex items-center rounded-lg text-gray-800 hover:text-gray-600 hover:underline"
                                >
									<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 mr-1">
										<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
									</svg>
                                    {{ $t('Add a new address') }}
                                </LocalizedLink>
                            </div>
                        </div>

                        <div v-else class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-8 text-center">
                            <p class="mb-3 text-gray-600 dark:text-gray-300">
                                {{ $t('You have no additional address entries in your address book.') }}
                            </p>
                            <LocalizedLink
                                to="/account/address-book/new"
                                class="inline-flex items-center rounded-lg bg-blue-600 text-white px-4 py-2 text-sm font-medium hover:bg-blue-700
                                       focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2
                                       dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus-visible:ring-offset-gray-900"
                            >
                                {{ $t('Add a new address') }}
                            </LocalizedLink>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
