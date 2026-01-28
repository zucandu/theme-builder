<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useToast } from 'vue-toastification';
import { useOrderStore } from '@/stores/order';
import DropzoneUploader from '@theme/storefront/components/widgets/DropzoneUploader.vue'

const route = useRoute();
const { t } = useI18n();
const toast = useToast();
const orderStore = useOrderStore();

const formdata = ref({
    id: undefined,
    order_ref: undefined,
    resolution: {},
    qty: {},
    reason: {},
    additional: undefined,
    attachments: []
});
const loaded = ref(false);
const reasonItems = ref(["Doesn't Fit", "Doesn't Match", "Received Wrong Item", "Arrived Damaged", "Don't like", "Other"]);
const order = ref({});
onMounted(async () => {

    // Fetch order details
    await orderStore.fetchOrderDetailsByRef(route.params.ref);
    order.value = orderStore.retrieveOrder;

    // Set formdata
    order.value.items.map(item => {
        formdata.value.resolution[item.product_id] = `refund`
        formdata.value.qty[item.product_id] = 1
        formdata.value.reason[item.product_id] = undefined
        formdata.value.order_ref = order.value.reference
    })

    loaded.value = true;

});

const submitRequest = async() => {
    await orderStore.processReturn(formdata.value)
        .catch(error => {
            toast.error(t(error.response.data.message));
        });
    
    await orderStore.fetchOrderDetailsByRef(route.params.ref);
    order.value = orderStore.retrieveOrder;
    
    toast.success(t(`We have received your request and we will contact you as soon as possible.`));
}

function updateContent(v) 
{        
    switch(v.type) {
        case 'add':
            formdata.value.attachments.push(v.content)
        break;
        case 'remove':
            const index = formdata.value.attachments.indexOf(v.content)
            if (index !== -1) {
                formdata.value.attachments.splice(index, 1)
            }
        break;
    }
}
</script>


<template>
    <div>
        <div class="flex min-h-full flex-1 flex-col justify-center px-4 sm:px-6 lg:px-8 py-8">
            <div v-if="loaded" class="w-full lg:w-9/12 mx-auto space-y-6">
                <!-- START A RETURN -->
                <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 sm:p-6 shadow-sm">
                    <h4 class="mb-4 text-xl font-semibold text-gray-900 dark:text-gray-100">
                        {{ $t('Start a Return For Order') }} #{{ order.id }}
                    </h4>
                    <form @submit.prevent="submitRequest" class="space-y-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                                <thead class="bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-200">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-medium"></th>
                                        <th class="px-4 py-3 text-left font-medium">{{ $t('Name') }}</th>
                                        <th class="px-4 py-3 text-center font-medium hidden sm:table-cell">{{ $t('Resolution') }}</th>
                                        <th class="px-4 py-3 text-center font-medium hidden sm:table-cell">{{ $t('Quantity') }}</th>
                                        <th class="px-4 py-3 text-left font-medium hidden sm:table-cell">{{ $t('Reason') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    <tr v-for="item in order.items" :key="item.product_id" class="align-top">
                                        <!-- Image -->
                                        <td class="px-4 py-3 w-24">
                                            <img
                                                v-if="item.images && item.images.length > 0"
                                                :src="`/storage/${zucConfig.small_image_size}/${item.images[0].src}`"
                                                class="aspect-square w-full rounded-lg bg-gray-100 object-cover"
                                            />
                                            <img
                                                v-else
                                                :src="`/storage/store/no-image.png`"
                                                :width="zucConfig.small_image_size"
                                                :alt="item.product_id"
                                                class="aspect-square w-full rounded-lg bg-gray-100 object-cover"
                                            />
                                        </td>
                                        <!-- Name + sub-controls for mobile -->
                                        <td class="px-4 py-3">
                                            <div class="mb-2 text-gray-900 dark:text-gray-100 font-medium">
                                                <span>{{ item.name }}</span>
                                                <span v-if="item.sku" class="ml-2 text-xs text-gray-500">#{{ item.sku }}</span>
                                            </div>
                                            <div
                                                v-if="item.returnable == 0"
                                                class="mb-2 text-xs text-yellow-700 bg-yellow-100 rounded px-2 py-1 inline-block"
                                            >
                                                {{ $t('This product is not eligible for return.') }}
                                            </div>

                                            <!-- Hook -->
                                            <template v-for="(component, index) in $pluginStorefrontHooks['account_product_title']" :key="index">
                                                <component :is="component" :product="item"></component>
                                            </template>

                                            <!-- Mobile-only controls -->
                                            <div class="mt-3 space-y-3 sm:hidden">
                                                <!-- Resolution -->
                                                <div class="flex flex-col space-y-2">
                                                    <label class="flex items-center space-x-2">
                                                        <input v-model="formdata.resolution[item.product_id]" value="refund" type="radio"
                                                            class="form-radio text-blue-600 focus:ring-2 focus:ring-blue-200" />
                                                        <span>{{ $t('Refund') }}</span>
                                                    </label>
                                                    <label class="flex items-center space-x-2">
                                                        <input v-model="formdata.resolution[item.product_id]" value="exchange" type="radio"
                                                            class="form-radio text-blue-600 focus:ring-2 focus:ring-blue-200" />
                                                        <span>{{ $t('Exchange') }}</span>
                                                    </label>
                                                </div>
                                                <!-- Quantity -->
                                                <div>
                                                    <template v-if="(item.qty - item.qty_returned)">
                                                        <select v-model="formdata.qty[item.product_id]"
                                                            class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm select-bg appearance-none">
                                                            <option v-for="i in (item.qty - item.qty_returned)" :value="i" :key="i">{{ i }}</option>
                                                        </select>
                                                    </template>
                                                    <template v-else>
                                                        <span class="text-xs text-green-700 bg-green-100 rounded px-2 py-1">
                                                            {{ $t('Returned all') }}
                                                        </span>
                                                    </template>
                                                </div>
                                                <!-- Reason -->
                                                <div>
                                                    <select v-model="formdata.reason[item.product_id]"
                                                        :disabled="!(item.qty - item.qty_returned) || (item.returnable == 0)"
                                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm disabled:cursor-not-allowed disabled:opacity-60  select-bg appearance-none">
                                                        <option :value="undefined">{{ $t('-Please select-') }}</option>
                                                        <option v-for="reason in reasonItems" :value="reason" :key="reason">{{ $t(reason) }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </td>
                                        <!-- Desktop Resolution -->
                                        <td class="px-4 py-3 hidden sm:table-cell">
                                            <div class="flex flex-col space-y-2">
                                                <label class="flex items-center space-x-2">
                                                    <input v-model="formdata.resolution[item.product_id]" value="refund" type="radio"
                                                        class="form-radio text-blue-600 focus:ring-2 focus:ring-blue-200" />
                                                    <span>{{ $t('Refund') }}</span>
                                                </label>
                                                <label class="flex items-center space-x-2">
                                                    <input v-model="formdata.resolution[item.product_id]" value="exchange" type="radio"
                                                        class="form-radio text-blue-600 focus:ring-2 focus:ring-blue-200" />
                                                    <span>{{ $t('Exchange') }}</span>
                                                </label>
                                            </div>
                                        </td>
                                        <!-- Desktop Quantity -->
                                        <td class="px-4 py-3 hidden sm:table-cell">
                                            <template v-if="(item.qty - item.qty_returned)">
                                                <select v-model="formdata.qty[item.product_id]"
                                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm select-bg appearance-none">
                                                    <option v-for="i in (item.qty - item.qty_returned)" :value="i" :key="i">{{ i }}</option>
                                                </select>
                                            </template>
                                            <template v-else>
                                                <span class="text-xs text-green-700 bg-green-100 rounded px-2 py-1">
                                                    {{ $t('Returned all') }}
                                                </span>
                                            </template>
                                        </td>
                                        <!-- Desktop Reason -->
                                        <td class="px-4 py-3 hidden sm:table-cell">
                                            <select v-model="formdata.reason[item.product_id]"
                                                :disabled="!(item.qty - item.qty_returned) || (item.returnable == 0)"
                                                class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm disabled:cursor-not-allowed disabled:opacity-60 select-bg appearance-none">
                                                <option :value="undefined">{{ $t('-Please select-') }}</option>
                                                <option v-for="reason in reasonItems" :value="reason" :key="reason">{{ $t(reason) }}</option>
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Dropzone -->
                        <div>
                            <DropzoneUploader @updateContent="updateContent" />
                        </div>

                        <!-- Additional -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                {{ $t('Additional') }} ({{ $t('Optional') }})
                            </label>
                            <textarea
                                v-model="formdata.additional"
                                class="mt-2 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 sm:text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 resize-y"
                                :placeholder="$t('Write something...(optional)')"
                            ></textarea>
                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-between items-center">
                            <LocalizedLink to="/account/order/list" class="text-blue-600 hover:underline">
                                {{ $t('Back to my account') }}
                            </LocalizedLink>
                            <button
                                class="bg-blue-600 text-white uppercase font-medium py-2 px-4 rounded-md shadow-sm hover:bg-blue-700 focus:ring-2 focus:ring-blue-200 cursor-pointer"
                            >
                                {{ $t('Submit Request') }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- HISTORY -->
                <div v-if="order.returns && order.returns.length > 0"
                    class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 sm:p-6 shadow-sm">
                    <h4 class="mb-4 text-xl font-semibold text-gray-900 dark:text-gray-100">
                        {{ $t('Returned Item History') }}
                    </h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                            <thead class="bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-200">
                                <tr>
                                    <th class="px-4 py-3 font-medium">{{ $t('Date') }}</th>
                                    <th class="px-4 py-3 font-medium">{{ $t('Type') }}</th>
                                    <th class="px-4 py-3 font-medium">{{ $t('Item') }}</th>
                                    <th class="px-4 py-3 font-medium">{{ $t('Reason') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr v-for="item in order.returns" :key="item.product_id">
                                    <td class="px-4 py-3">{{ item.created_at }}</td>
                                    <td class="px-4 py-3 capitalize">{{ item.resolution }}</td>
                                    <td class="px-4 py-3"><strong>{{ item.qty }}</strong> × {{ item.name }}</td>
                                    <td class="px-4 py-3">{{ item.reason }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
				
				<!-- Return Attachments (Images) -->
				<div
					v-if="order.return_attachments && order.return_attachments.length > 0"
					class="mt-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 sm:p-6 shadow-sm"
				>
					<h4 class="mb-4 text-xl font-semibold text-gray-900 dark:text-gray-100">
						{{ $t('Return Attachments') }}
					</h4>

					<div class="flex flex-wrap gap-3">
						<a
							v-for="(att, i) in order.return_attachments"
							:key="i"
							:href="att.url"
							target="_blank"
							rel="noopener"
							class="block w-28"
						>
							<img
								:src="att.url"
								alt="return attachment"
								class="h-20 w-28 object-cover rounded-md border border-gray-200 dark:border-gray-700 shadow-sm hover:opacity-90 transition"
							/>
							<div class="mt-1 text-[11px] text-gray-500 dark:text-gray-400 truncate">
								{{ att.created_at }}
							</div>
						</a>
					</div>
				</div>

				
            </div>

            <!-- Loading -->
            <div v-else class="flex justify-center py-12">
                <span class="animate-spin rounded-full h-8 w-8 border-4 border-gray-200 border-t-blue-500"></span>
            </div>
        </div>
    </div>
</template>
