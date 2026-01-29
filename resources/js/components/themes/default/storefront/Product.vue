<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useToast } from 'vue-toastification';
import { useHelpers } from '@/composables/useHelpers';

import { useAuthCustomerStore } from '@/stores/auth/customer';
import { useProductStore } from '@/stores/catalog/product';
import { useCartStore } from '@/stores/cart';

// Components
import PriceConverter from '@theme/cores/PriceConverter.vue';
import MetaTags from '@theme/cores/MetaTags.vue';
import Carousel from '@theme/storefront/components/product/Carousel.vue';

const { t, locale } = useI18n();
const toast = useToast();
const productStore = useProductStore();
const cartStore = useCartStore();
const { translateItemField, translateItemObj } = useHelpers();

const loaded = ref(false);
const route = useRoute();
const selectedAttributes = ref({});
const formdata = ref({ qty: 1, meta: {} });

// Query product details by slug
onMounted(async () => {
    await productStore.retrieveProductDetails(route.params.slug).finally(() => loaded.value = true);
    if(productStore.productDetails.default_attributes !== undefined) {
        Object.keys(productStore.productDetails.default_attributes).forEach(aoId => (selectedAttributes.value = {  ...selectedAttributes.value, [aoId]: productStore.productDetails.default_attributes[aoId] }))
    }
});

// Get real product data
const productComputed = computed(() => {

    // Get product details
    const actualProduct = productStore.childProduct(productStore.productDetails, selectedAttributes.value);
    const productData = actualProduct !== undefined ? actualProduct : productStore.productDetails;

    // Get translation
    const translation = translateItemObj(productData, locale.value);

    // Manufacturer
    const manufacturerTranslation = translateItemObj(productStore.productDetails.manufacturer, locale.value);

    // Set readonly attributes
	const readonlyAttributes = productStore.getAttributes(productData, 'readonly');
	
    return { productData, translation, manufacturerTranslation, readonlyAttributes };
});

// Set outofstock and invalid class
watch(selectedAttributes, async (val) => {
    const oids = await Promise.all(Object.keys(val))
    oids.forEach(oid => {
        const attrEls = document.querySelectorAll(`#option${oid} .aov-item`)
        attrEls.forEach(e => document.querySelector(`#attr-${oid}-${e.getAttribute('data-aovid')}`).classList.remove('outofstock', 'invalid'))
        productStore.productDetails.attributes.filter(at => +at.attribute_option_id === +oid)
            .map(r => r.attribute_option_value_id)
            .forEach(ovid => {
                const childProduct = productStore.childProduct(productStore.productDetails, { ...selectedAttributes.value, [oid]: ovid })
                if(childProduct.status === 0) {
                    document.querySelector(`#attr-${oid}-${ovid}`).classList.add('invalid')
                } else {
                    if(childProduct.quantity === 0) {
                        document.querySelector(`#attr-${oid}-${ovid}`).classList.add('outofstock')
                    }
                }
            })
    })
}, { deep: true });

const addToCart = async () => {
    const { productData, translation } = productComputed.value;
    await cartStore.addProduct({ 
        id: productData.id, 
        cart_quantity: (formdata.value.qty > 0 ? formdata.value.qty : 1), meta: formdata.value.meta 
    })
    .then(() => {
        toast.success(t('cart.itemAdded', { name: translation.name }));
    })
    .catch(error => {
        toast.error(t(error.response.data.message));
    });

}

function updateMetaForm(obj) {
    formdata.value.meta = { ...formdata.value.meta, ...obj }
}

</script>

<template>
    <div>
        <MetaTags v-if="loaded" :title="productComputed.translation.meta_title" :description="productComputed.translation.meta_description" />
        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="container mx-auto px-4 py-6">
                <div v-if="loaded" class="bg-white rounded-xl shadow-sm p-6 lg:p-10">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                        <!-- Left Column: Carousel -->
                        <div class="lg:col-span-7">
                            <Carousel 
                                :images="productComputed.productData?.images" 
                                :embeds="productComputed.productData?.embeds" 
                                :product-name="productComputed.translation?.name" 
                            />
                        </div>

                        <!-- Right Column: Details -->
                        <div class="lg:col-span-5">
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ productComputed.translation.name }}</h1>
                            
                            <div class="flex items-center text-sm text-gray-500 mb-6 space-x-4">
                                <div><span class="font-medium text-gray-700">SKU:</span> {{ productComputed.productData.sku }}</div>
                                <div class="w-px h-4 bg-gray-300"></div>
                                <div class="flex items-center">
                                    <span class="w-2 h-2 rounded-full mr-2" :class="productComputed.productData.quantity > 0 ? 'bg-green-500' : 'bg-red-500'"></span>
                                    {{ productComputed.productData.quantity > 0 ? $t('In stock') : $t('Out of stock') }}
                                </div>
                                <div v-if="productComputed.manufacturerTranslation" class="hidden sm:block w-px h-4 bg-gray-300"></div>
                                <LocalizedLink 
                                    v-if="productComputed.manufacturerTranslation" 
                                    :to="`/manufacturer/${productComputed.manufacturerTranslation.slug}`" 
                                    class="hidden sm:block text-blue-600 hover:text-blue-700 font-medium"
                                >
                                    {{ productComputed.manufacturerTranslation.name }}
                                </LocalizedLink>
                            </div>

                            <div class="mb-8 p-4 bg-gray-50 rounded-lg border border-gray-100">
                                <PriceConverter :product="productComputed.productData" class="text-3xl font-bold text-gray-900" />
                            </div>

                            <!-- Attributes -->
                            <div v-if="productStore.getVariants.length > 0" class="mb-8 space-y-6">
                                <div v-for="ao in productStore.getVariants" :key="ao.id">
                                    <div :id="`option${ao.id}`">
                                        <div class="flex justify-between mb-2">
                                            <span class="font-semibold text-gray-800">{{ translateItemField(ao, 'name', $i18n.locale) }}</span>
                                            <span class="text-gray-600 font-medium">{{ translateItemField(ao.values.find(v => v.id === selectedAttributes[ao.id]), 'name', $i18n.locale) }}</span>
                                        </div>
                                        <div class="flex flex-wrap gap-3">
                                            <div 
                                                v-for="aov in ao.values" 
                                                :key="aov.vid" 
                                                @click.stop="selectedAttributes[ao.id] = aov.vid" 
                                                :data-aovid="aov.vid" 
                                                :id="`attr-${ao.id}-${aov.vid}`" 
                                                :class="[
                                                    'aov-item cursor-pointer px-4 py-2 border-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center justify-center min-w-[3rem]',
                                                    selectedAttributes[ao.id] === aov.vid 
                                                        ? 'border-blue-600 bg-blue-50 text-blue-700' 
                                                        : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300 hover:bg-gray-50'
                                                ]"
                                            >
                                                <template v-if="+ao.display_ov_image === 1 && aov.image">
                                                    <img :src="`/storage/${zucConfig.small_image_size}/${aov.image}`" :alt="productComputed.translation.name" class="w-8 h-8 object-contain">
                                                </template>
                                                <template v-else>
                                                    {{ translateItemField(aov, 'name', $i18n.locale) }}
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <form @submit.prevent="addToCart" class="mt-8 border-t pt-8 border-gray-100">
                                <div class="flex flex-col sm:flex-row gap-4">
                                    <template v-if="productComputed.productData.quantity > 0">
                                        <div class="w-full sm:w-32">
                                            <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">{{ $t('Quantity') }}</label>
                                            <div class="relative">
                                                <input 
                                                    v-model="formdata.qty" 
                                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-medium text-center" 
                                                    type="number" 
                                                    min="1"
                                                >
                                            </div>
                                        </div>
                                        <button 
                                            class="flex-1 bg-gray-900 text-white font-bold py-3 px-8 rounded-lg hover:bg-black transition-colors duration-200 flex items-center justify-center shadow-lg hover:shadow-xl transform hover:-translate-y-0.5" 
                                            type="submit"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                            </svg>
                                            {{ $t('Add to Cart') }}
                                        </button>
                                    </template>
                                    <div v-else class="w-full bg-red-50 text-red-600 rounded-lg p-4 text-center font-medium border border-red-100">
                                        {{ $t('This product is currently out of stock') }}
                                    </div>
                                </div>

                                <!-- Hooks -->
                                <div class="mt-6">
                                    <template v-for="(component, index) in $pluginStorefrontHooks['product_after_add_to_cart_button']" :key="index">
                                        <component 
                                            :is="component" 
                                            :product="productComputed.productData" 
                                            :translation="productComputed.translation" 
                                            :formdata="formdata" 
                                            @updateMetaForm="updateMetaForm"
                                        ></component>
                                    </template>
                                </div>
                            </form>
                            
                            <!-- Write Review Link -->
                             <div class="mt-6 text-center">
                                <LocalizedLink :to="`/product/${productComputed.translation.slug}/write-review`" class="text-sm text-gray-500 hover:text-gray-900 underline decoration-gray-300 hover:decoration-gray-900 transition-all">
                                    {{ $t('Write a Review') }}
                                </LocalizedLink>
                            </div>

                        </div>
                    </div>

                    <!-- Description Tab/Section -->
                    <div class="mt-16 border-t border-gray-100 pt-10">
                        <h3 class="text-xl font-bold mb-6 text-gray-900">{{ $t('Description') }}</h3>
                        <div v-html="productComputed.translation.description" class="prose prose-blue max-w-none text-gray-600"></div>
                    </div>
                </div>
                
                <!-- Loading State -->
                <div v-else class="flex flex-col items-center justify-center min-h-[60vh]">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-gray-900 mb-4"></div>
                    <p class="text-gray-500 font-medium">{{ $t('Loading product details...') }}</p>
                </div>
            </div>
        </div>
    </div>
</template>