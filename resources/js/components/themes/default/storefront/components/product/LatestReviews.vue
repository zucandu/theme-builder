<script setup>
import { ref, onMounted, computed } from 'vue'

import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toastification';

import { useHelpers } from '@/composables/useHelpers';

const router = useRouter();
const toast = useToast();
const { t, locale } = useI18n();


const { translateItemObj } = useHelpers();
import { useProductStore } from '@/stores/catalog/product';
const productStore = useProductStore();

const props = defineProps({
    id: Number,
	childProducts: Array
});

const loaded = ref(false);
const reviews = ref([]);
onMounted(async () => {
	reviews.value = await productStore.fetchLatestReviews(props.id);
	loaded.value = true;
});

// Define the events the component can emit.
const emit = defineEmits(['updateSelectedAttributes']);
const goToProduct = (sku) => {
	if (props.childProducts?.length > 0) {
        const product = props.childProducts.find(item => item.sku === sku);
		if (!product?.attributes) return;
		
		const selected = {};
        for (const attr of product.attributes) {
            if (attr.attribute_option.type !== 'readonly') {
				selected[attr.attribute_option_id] = attr.attribute_option_value_id;
            }
        }
		emit('updateSelectedAttributes', selected);
    }
}

const randomBGColor = (c) => {
	const r = Math.floor(Math.random() * 256);
	const g = Math.floor(Math.random() * 256);
	const b = Math.floor(Math.random() * 256);
	return `rgb(${r},${g},${b})` || 'rgb(251,34,92)'
}
</script>
<template>
    <section v-if="loaded" class="mt-8 md:mt-12">
        <template v-if="reviews.length > 0">
            <h2 class="text-2xl font-bold mb-6">{{ $t('Customer Reviews') }}</h2>

            <div v-for="review in reviews" :key="review.id" class="py-6 border-b border-gray-200 last:border-0">
                <div class="flex flex-col md:flex-row gap-6">
                    <div class="flex-shrink-0">
                        <div class="flex items-center">
                            <img
                                v-if="review.customer?.avatar"
                                :src="`/storage/${review.customer.avatar}`"
                                alt="review.customer_name"
                                class="w-14 h-14 rounded-full object-cover mr-4 border border-gray-200"
                            >
                            <div
                                v-else-if="review.customer"
                                :style="{ backgroundColor: randomBGColor(review.customer.firstname) }"
                                class="w-14 h-14 rounded-full flex items-center justify-center text-white text-2xl font-medium mr-4"
                            >
                                {{ review.customer.firstname.charAt(0).toUpperCase() }}
                            </div>
                            <div
                                v-else-if="review.customer_name"
                                :style="{ backgroundColor: randomBGColor(review.customer_name) }"
                                class="w-14 h-14 rounded-full flex items-center justify-center text-white text-2xl font-medium mr-4"
                            >
                                {{ review.customer_name.charAt(0).toUpperCase() }}
                            </div>

                            <div>
                                <div class="font-semibold text-gray-900">
                                    {{ review.customer?.firstname || review.customer_name || 'Anonymous' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex-1">
						<div class="mb-1">
							<h4 class="font-bold text-lg text-gray-900 !mb-0">{{ review.review_title }}</h4>
						</div>

						<div class="flex flex-wrap items-center gap-3 mb-3">
							<div class="flex items-center gap-0.5">
								<svg
									v-for="i in review.rating"
									:key="'filled-' + i"
									class="w-4 h-4 text-yellow-400 fill-current"
									viewBox="0 0 16 16"
								>
									<path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
								</svg>
								<svg
									v-for="i in (5 - review.rating)"
									:key="'empty-' + i"
									class="w-4 h-4 text-gray-300 fill-current"
									viewBox="0 0 16 16"
								>
									<path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
								</svg>
							</div>

							<span class="w-px h-3 bg-gray-300"></span>

							<div v-if="+review.parent_id > 0" 
								 @click="goToProduct(review.sku)" 
								 class="flex items-center text-xs cursor-pointer group"
							>
								<span class="text-gray-500 group-hover:text-blue-600 transition-colors">{{ $t('For model:') }}</span>
								<span class="ml-1.5 font-semibold text-gray-700 group-hover:text-blue-600 underline decoration-dotted">
									{{ review.sku }}
								</span>
							</div>
						</div>

						<p class="text-gray-700 leading-relaxed text-sm">{{ review.review_text }}</p>
					</div>
                </div>
            </div>
        </template>

        <template v-else>
            <h4 class="text-2xl font-bold mb-6">{{ $t('Customer Reviews') }}</h4>
            <p class="text-gray-500 italic">{{ $t('There are no reviews for this product yet.') }}</p>
        </template>
    </section>
	<div v-else class="animate-spin rounded-full h-8 w-8 border-t-3 border-gray-500"></div>
</template>