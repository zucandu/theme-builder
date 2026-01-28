<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from "vue-router";
import { useI18n } from 'vue-i18n';
import { useToast } from 'vue-toastification';

import { useAuthCustomerStore } from '@/stores/auth/customer';
import { useProductStore } from '@/stores/catalog/product';
import { useHelpers } from '@/composables/useHelpers';
import { useRedirect } from '@/composables/useRedirect';

const route = useRoute();
const { t, locale } = useI18n();
const toast = useToast();
const { redirectTo } = useRedirect();

const productStore = useProductStore();
const authCustomerStore = useAuthCustomerStore();
const { translateItemField, translateItemObj } = useHelpers();

const formdata = ref({
    rating: 5,
    review_title: undefined,
    review_text: undefined,
    customer_name: undefined,
    grecaptcha_token: undefined
});
const loaded = ref(false);

onMounted(() => {
    productStore.retrieveProductDetails(route.params.slug)
        .then(() => {
            if (authCustomerStore.isLoggedIn) {
                formdata.value.customer_name = authCustomerStore.customerInfo.username || authCustomerStore.customerInfo.firstname;
            }
        })
        .finally(() => loaded.value = true);
});

const submitProductReview = async () => {

    if(zucConfig.recaptcha_site_key) {
        grecaptcha.ready(function() {
            grecaptcha.execute(zucConfig.recaptcha_site_key, { action: 'submit' }).then(function(token) {
                formdata.value.grecaptcha_token = token
            })
        })
        while(formdata.value.grecaptcha_token === undefined) {
            await new Promise(r => setTimeout(r, 100))
        }
    }
	
    await productStore.addReview({ ...formdata.value, locale: locale.value, product_id: productComputed.value.productData.id })
        .then(() => {
            toast.success(t(`Thank you for your review! It will be reviewed and published shortly.`));
            redirectTo(`/product/${productComputed.value.translation.slug}`);
        })
        .catch(error => toast.error(t(error.response.data.message)));
}

// Get real product data
const productComputed = computed(() => {

    // Get product details
	let productData = productStore.productDetails
	if(productStore.productDetails.children && productStore.productDetails.children.length > 0) {
		const matched = productStore.productDetails.children.find(p => p.translations.find(t => t.slug === route.params.slug)) || undefined
		if(matched) {
			productData = matched;
		}
	}

    // Get translation
    const translation = translateItemObj(productData, locale.value);

    return { productData, translation };
});
</script>

<template>
    <section>
        <div class="flex min-h-full flex-1 flex-col justify-center px-6 py-12 lg:px-8">
            <div class="sm:mx-auto sm:w-full sm:max-w-sm">
                <h2 class="text-center text-2xl/9 font-bold tracking-tight text-gray-900">{{ $t('Write Your Own Review') }}</h2>
            </div>
            <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-xl">
                <form v-if="loaded" @submit.prevent="submitProductReview">
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <div class="text-2xl font-semibold">{{ $t('Write Your Own Review') }}</div>
                        <p class="text-sm text-gray-500">
                            <LocalizedLink :to="`/product/${productComputed.translation.slug}`" class="text-blue-600 hover:underline">
                                {{ productComputed.translation.name }} {{ productComputed.productData.sku }}
                            </LocalizedLink>
                        </p>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">{{ $t('Please choose rating') }}</label>
                            <div class="flex space-x-4 mt-2">
                                <div v-for="rating in 5" :key="rating" class="flex items-center">
                                    <input v-model="formdata.rating" type="radio" :id="`rrating${rating}`" :value="rating" class="hidden" />
                                    <label :for="`rrating${rating}`" :class="`cursor-pointer flex items-center ${+formdata.rating === +rating ? 'text-yellow-300 font-bold' : 'text-slate-500'}`">
                                        {{ rating }}
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" :fill="+formdata.rating === +rating ? '#fde047' : '#f4f4f5'" class="bi bi-star-fill ml-1" viewBox="0 0 16 16">
                                            <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
                                        </svg>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="rtitle" class="block text-sm font-medium text-gray-700">{{ $t('Review Title') }} {{ $t('(Optional)') }}</label>
                            <input v-model="formdata.review_title" id="rtitle" class="mt-1 block w-full border border-gray-300 rounded px-2 py-1 shadow-xs focus:outline-hidden focus:ring-3 focus:ring-blue-300 focus:border-blue-500" :placeholder="$t('Enter your review title')" />
                        </div>

                        <div class="mb-4">
                            <label for="rtext" class="block text-sm font-medium text-gray-700">{{ $t('Let Us Know Your Thoughts?') }}</label>
                            <textarea v-model="formdata.review_text" id="rtext" class="mt-1 block w-full border border-gray-300 rounded px-2 py-1 shadow-xs focus:outline-hidden focus:ring-3 focus:ring-blue-300 focus:border-blue-500" :placeholder="$t('Write something...')" rows="3" required></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="rnickname" class="block text-sm font-medium text-gray-700">{{ $t('Nickname') }}</label>
                            <input v-model="formdata.customer_name" id="rnickname" class="mt-1 block w-full border border-gray-300 rounded px-2 py-1 shadow-xs focus:outline-hidden focus:ring-3 focus:ring-blue-300 focus:border-blue-500" :placeholder="$t('Enter your nickname')" />
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="px-4 py-2 bg-gray-600 text-white font-semibold rounded-lg hover:bg-gray-700 focus:outline-hidden focus:ring-3 focus:ring-blue-300 focus:ring-offset-2 cursor-pointer">{{ $t('Submit Review') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</template>
