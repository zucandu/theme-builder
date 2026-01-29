<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n';
import { useToast } from 'vue-toastification';
import { TransitionRoot, TransitionChild, Dialog, DialogPanel, DialogTitle, } from '@headlessui/vue'

import ProductCard from '@theme/storefront/components/product/ProductCard.vue'
import ProductCardPlaceholder from '@theme/storefront/components/product/ProductCardPlaceholder.vue'

import { useProductStore } from '@/stores/catalog/product';
import { useAuthCustomerStore } from '@/stores/auth/customer';



const toast = useToast();
const { t, locale } = useI18n();

const productStore = useProductStore();
const authCustomerStore = useAuthCustomerStore();

const props = defineProps({
    items: Array,
});

const isRestockModalOpen = ref(false);
const selectedProduct = ref(null);

const openRestockModal = (product) => {
    selectedProduct.value = product;
    isRestockModalOpen.value = true;
	
	// Fill in form restock data
	formdata.value.name = authCustomerStore.profile?.firstname ?? undefined;
	formdata.value.email = authCustomerStore.profile?.email ?? undefined;
};

const closeRestockModal = () => {
    isRestockModalOpen.value = false;
    selectedProduct.value = null;
};

const reviewLoading = ref(false);
const reviews = ref([]);
const isReviewModalOpen = ref(false);
const openReviewModal = async (product) => {
	reviewLoading.value = true;
	isReviewModalOpen.value = true;
	try {
        reviews.value = await productStore.fetchLatestReviews(product.id);
    } catch (error) {
        console.error("Failed to fetch reviews:", error);
        reviews.value = [];
    } finally {
        reviewLoading.value = false;
    }

};
const closeReviewModal = () => {
    isReviewModalOpen.value = false;
	reviews.value = [];
};

const formdata = ref({
	email: undefined,
	name: undefined,
	product_id: undefined,
	locale: undefined,
	grecaptcha_token: undefined,
});

const restockNotify = async () => {
	if (zucConfig?.recaptcha_site_key) {
		const token = await new Promise((resolve, reject) => {
			grecaptcha.ready(() => {
				grecaptcha.execute(zucConfig.recaptcha_site_key, { action: 'submit' })
					.then(resolve)
					.catch(reject);
			});
		});
		formdata.value.grecaptcha_token = token;
	}

	try {
		await productStore.subscribeRestockNotification({
			...formdata.value,
			product_id: selectedProduct.value.id,
			locale: selectedProduct.value.locale
		});
		toast.success(t('You have been successfully subscribed to the restock notification list.'));
		closeRestockModal();
	} catch (error) {
		toast.error(t(error.response?.data?.message || 'Something went wrong.'));
	}
}

onMounted(() => {
    const onDocClick = (e) => {
        const icon = e.target.closest('.tooltip-icon');
        const allTooltips = document.querySelectorAll('.product-card-tooltip');
        if (icon) {
            e.stopPropagation();
            allTooltips.forEach(t => t.classList.add('hidden'));
            const tooltip = icon.nextElementSibling;
            if (tooltip) tooltip.classList.toggle('hidden');
        } else {
            allTooltips.forEach(t => t.classList.add('hidden'));
        }
    };

    document.addEventListener('click', onDocClick);
});

const isDiscountModalOpen = ref(false);
const openDiscountModal = (product) => {
	selectedProduct.value = product;
	isDiscountModalOpen.value = true;
};
const closeDiscountModal = () => {
	selectedProduct.value = null;
    isDiscountModalOpen.value = false;
};

const calculateDiscountedPrice = (discount) => {
    const price = parseFloat(selectedProduct.value.price || 0)
    let finalPrice = price

    if (discount.discount_type === 1) {
        finalPrice = price - discount.discount_amount
    } else if (discount.discount_type === 2) {
        finalPrice = price - (price * (discount.discount_amount / 100))
    }

    return +finalPrice
}

const calculatePercentageDiscount = (discount) => {
    const price = parseFloat(selectedProduct.value.price || 0)

    if (discount.discount_type === 1) {
        return ((discount.discount_amount / price) * 100).toFixed(2)
    } else if (discount.discount_type === 2) {
        return discount.discount_amount
    }

    return 0
}

</script>

<template>
	<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
			
		<!-- Loading state -->
		<template v-if="!props.items || props.items.length === 0">
			<ProductCardPlaceholder :item-count="+zucConfig.number_of_items_per_block" />
		</template>
		<template v-else>
			<div v-for="(product, index) in props.items" :key="product.id" class="rounded-lg p-4 items-center">
				<ProductCard 
					:product="product" 
					:index="index" 
					@open-restock-modal="openRestockModal" 
					@open-review-modal="openReviewModal" 
					@open-discount-modal="openDiscountModal" 
				/>
			</div>
		</template>
		
		<TransitionRoot appear :show="isRestockModalOpen" as="template">
			<Dialog as="div" @close="closeRestockModal" class="relative z-10">
				<TransitionChild
					as="template"
					enter="duration-300 ease-out"
					enter-from="opacity-0"
					enter-to="opacity-100"
					leave="duration-200 ease-in"
					leave-from="opacity-100"
					leave-to="opacity-0"
				>
					<div class="fixed inset-0 bg-black/25" />
				</TransitionChild>

				<div class="fixed inset-0 overflow-y-auto">
					<div
						class="flex min-h-full items-center justify-center p-4 text-center"
					>
						<TransitionChild
							as="template"
							enter="duration-300 ease-out"
							enter-from="opacity-0 scale-95"
							enter-to="opacity-100 scale-100"
							leave="duration-200 ease-in"
							leave-from="opacity-100 scale-100"
							leave-to="opacity-0 scale-95"
						>
							<DialogPanel
								class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all"
							>
								<DialogTitle
									as="h3"
									class="text-lg font-medium leading-6 text-gray-900"
								>
									{{ $t(`Notify When In Stock`) }}
								</DialogTitle>
								
								<div class="mt-2">
									<p class="text-sm text-gray-500">{{ selectedProduct?.name }}</p>
								</div>
								
								<form @submit.prevent="restockNotify()">
									<!-- Email field -->
									<div class="mb-4">
										<label for="email-address" class="block text-sm font-medium text-gray-700 mb-1">
											{{ $t('Email address') }}
										</label>
										<input
											v-model="formdata.email"
											type="email"
											id="email-address"
											class="w-full border border-gray-300 rounded px-3 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
											placeholder="E.g. name@example.com"
											required
										/>
									</div>

									<!-- Name field -->
									<div class="mb-4">
										<label for="name" class="block text-sm font-medium text-gray-700 mb-1">
											{{ $t('Your name') }}
										</label>
										<input
											v-model="formdata.name"
											id="name"
											type="text"
											:placeholder="$t('E.g. John Wick')"
											required
											class="w-full border border-gray-300 rounded px-3 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
										/>
									</div>

									<!-- Action buttons -->
									<div class="flex justify-between items-center mt-4">
										<button
											type="button"
											@click="closeRestockModal"
											class="bg-gray-200 text-gray-700 hover:bg-gray-300 px-4 py-2 rounded-md text-sm transition cursor-pointer"
										>
											{{ $t('Close') }}
										</button>
										<button
											type="submit"
											class="inline-flex justify-center rounded-md border border-transparent bg-blue-100 px-4 py-2 text-sm font-medium text-blue-900 hover:bg-blue-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 cursor-pointer transition"
										>
											{{ $t('Notify me') }}
										</button>
									</div>
								</form>
							</DialogPanel>
						</TransitionChild>
					</div>
				</div>
			</Dialog>
		</TransitionRoot>
		
		<!-- REVIEW MODAL -->
		<TransitionRoot appear :show="isReviewModalOpen" as="template">
			<Dialog as="div" @close="closeReviewModal" class="relative z-10">
				<TransitionChild
					as="template"
					enter="duration-300 ease-out"
					enter-from="opacity-0"
					enter-to="opacity-100"
					leave="duration-200 ease-in"
					leave-from="opacity-100"
					leave-to="opacity-0"
				>
					<div class="fixed inset-0 bg-black/25" />
				</TransitionChild>

				<div class="fixed inset-0 overflow-y-auto">
					<div
						class="flex min-h-full items-center justify-center p-4 text-center"
					>
						<TransitionChild
							as="template"
							enter="duration-300 ease-out"
							enter-from="opacity-0 scale-95"
							enter-to="opacity-100 scale-100"
							leave="duration-200 ease-in"
							leave-from="opacity-100 scale-100"
							leave-to="opacity-0 scale-95"
						>
							<DialogPanel
								class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all"
							>
								<DialogTitle
									as="h3"
									class="text-lg font-medium leading-6 text-gray-900"
								>
									{{ $t(`Customer Reviews`) }}
								</DialogTitle>
								<div v-if="reviewLoading">Loading...</div>
								<div v-else>
									<div class="space-y-6">
										<template v-if="reviews.length > 0">
											<div v-for="(review, index) in reviews" :key="index" class="border-t pt-4 space-y-2">
												<!-- Rating & Date -->
												<div class="flex items-center">
													<div class="flex items-center mr-2">
														<div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-sm font-medium text-white mr-2 overflow-hidden">
															<img
																v-if="review.customer?.avatar"
																:src="`/storage/${review.customer?.avatar}`"
																alt=""
																class="w-full h-full object-cover rounded-full"
															/>
															<span v-else class="text-gray-600">
																{{ review.customer_name?.charAt(0).toUpperCase() }}
															</span>
														</div>
														<span class="text-sm text-gray-700 font-medium">{{ review.customer_name }}</span>
													</div>
													<div class="flex text-yellow-400">
														<span v-for="n in 5" :key="n">
															<svg
																v-if="n <= review.rating"
																xmlns="http://www.w3.org/2000/svg"
																class="h-5 w-5 fill-yellow-400"
																viewBox="0 0 20 20"
																fill="currentColor"
															>
																<path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.975a1 1 0 00.95.69h4.18c.969 0 1.371 1.24.588 1.81l-3.388 2.462a1 1 0 00-.364 1.118l1.287 3.975c.3.921-.755 1.688-1.538 1.118l-3.388-2.462a1 1 0 00-1.175 0L5.15 17.075c-.783.57-1.838-.197-1.538-1.118l1.287-3.975a1 1 0 00-.364-1.118L1.147 9.402c-.783-.57-.38-1.81.588-1.81h4.18a1 1 0 00.95-.69l1.286-3.975z" />
															</svg>
															<svg
																v-else
																xmlns="http://www.w3.org/2000/svg"
																class="h-5 w-5 fill-gray-300"
																viewBox="0 0 20 20"
																fill="currentColor"
															>
																<path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.975a1 1 0 00.95.69h4.18c.969 0 1.371 1.24.588 1.81l-3.388 2.462a1 1 0 00-.364 1.118l1.287 3.975c.3.921-.755 1.688-1.538 1.118l-3.388-2.462a1 1 0 00-1.175 0L5.15 17.075c-.783.57-1.838-.197-1.538-1.118l1.287-3.975a1 1 0 00-.364-1.118L1.147 9.402c-.783-.57-.38-1.81.588-1.81h4.18a1 1 0 00.95-.69l1.286-3.975z" />
															</svg>
														</span>
													</div>
													<span class="text-xs text-gray-400 ml-2">{{ review.created_at }}</span>
												</div>

												<!-- Content -->
												<p class="text-sm text-gray-600">{{ review.review_text }}</p>
												
											</div>
										</template>
									</div>
								</div>
							</DialogPanel>
						</TransitionChild>
					</div>
				</div>
			</Dialog>
		</TransitionRoot>
		
		<!-- DISCOUNT MODAL -->
		<TransitionRoot appear :show="isDiscountModalOpen" as="template">
			<Dialog as="div" @close="closeDiscountModal" class="relative z-10">
				<TransitionChild
					as="template"
					enter="duration-300 ease-out"
					enter-from="opacity-0"
					enter-to="opacity-100"
					leave="duration-200 ease-in"
					leave-from="opacity-100"
					leave-to="opacity-0"
				>
					<div class="fixed inset-0 bg-black/25" />
				</TransitionChild>

				<div class="fixed inset-0 overflow-y-auto">
					<div
						class="flex min-h-full items-center justify-center p-4 text-center"
					>
						<TransitionChild
							as="template"
							enter="duration-300 ease-out"
							enter-from="opacity-0 scale-95"
							enter-to="opacity-100 scale-100"
							leave="duration-200 ease-in"
							leave-from="opacity-100 scale-100"
							leave-to="opacity-0 scale-95"
						>
							<DialogPanel
								class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all "
							>
								<template v-if="selectedProduct">
                                    <DialogTitle class="text-lg font-semibold mb-4 relative">
                                        <span class="mr-5">{{ $t('Quantity Discount') }} - {{ selectedProduct.name }}</span>
										<button 
											@click="closeDiscountModal" 
											class="absolute top-0 right-0 text-gray-400 hover:text-gray-600 transition-colors focus:outline-none p-1"
										>
											<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
												<path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
											</svg>
										</button>
                                    </DialogTitle>
                                    
                                    <div class="mt-2">
                                        <table class="w-full text-sm text-left border-collapse overflow-hidden rounded-lg">
											<thead>
												<tr class="bg-gray-100 text-gray-700">
													<th class="p-3 border-b border-gray-200">{{ $t('Quantity') }}</th>
													<th class="p-3 border-b border-gray-200">{{ $t('Price Per Unit') }}</th>
													<th class="p-3 border-b border-gray-200 text-right">{{ $t('Discount') }}</th>
												</tr>
											</thead>
											<tbody>
												<tr 
													v-for="(discount, idx) in selectedProduct.quantity_discounts" 
													:key="idx"
													class="hover:bg-gray-50 transition-colors"
												>
													<td class="p-3 border-b border-gray-100 text-gray-600">
														<span class="font-medium">
															{{ discount.min_qty }}{{ +discount.max_qty > 0 ? ` - ${discount.max_qty}` : '+' }}
														</span>
													</td>

													<td class="p-3 border-b border-gray-100 font-bold text-gray-900">
														<PriceDisplay :price="calculateDiscountedPrice(discount)" />
													</td>

													<td class="p-3 border-b border-gray-100 text-right">
														<span 
															v-if="calculatePercentageDiscount(discount) > 0"
															class="inline-flex items-center px-2 py-1 rounded-md bg-red-100 text-red-700 text-xs font-bold"
														>
															<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3 mr-1">
																<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3" />
															</svg>
															{{ calculatePercentageDiscount(discount) }}% {{ $t('OFF') }}
														</span>
														<span v-else class="text-gray-400 text-xs italic">
															{{ $t('Standard') }}
														</span>
													</td>
												</tr>
											</tbody>
										</table>
                                    </div>
                                </template>
								
							</DialogPanel>
						</TransitionChild>
					</div>
				</div>
			</Dialog>
		</TransitionRoot>
	</div>
</template>