<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useToast } from 'vue-toastification';
import { useHelpers } from '@/composables/useHelpers';

import { useAuthCustomerStore } from '@/stores/auth/customer';
import { useProductStore } from '@/stores/catalog/product';
import { useCartStore } from '@/stores/cart';

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
	
	// Set meta title
	document.title = translation?.meta_title;

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
        <div class="py-12">
            <div class="container mx-auto py-6">
                <div v-if="loaded">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-6">
                            <img :src="`${zucConfig.store_url}/storage/${productComputed.productData.images[0]?.src}`" :alt="productComputed.translation.name">
                        </div>
                        <div class="col-span-6">
                            <form @submit.prevent="addToCart">
                                <h2 class="text-3xl">{{ productComputed.translation.name }}</h2>
                                <ul class="flex divide-x divide-gray-300">
                                    <li class="pr-4">{{ productComputed.productData.sku }}</li>
                                    <li class="px-4">{{ productComputed.productData.quantity }} {{ $t('In stock') }}</li>
                                    <li v-if="productComputed.manufacturerTranslation" class="px-4">
                                        <LocalizedLink :to="`/manufacturer/${productComputed.manufacturerTranslation.slug}`" class="text-blue-600 hover:text-blue-400 hover:underline">{{ productComputed.manufacturerTranslation.name }}</LocalizedLink>
                                    </li>
                                </ul>
                                <div class="mt-4">
                                    <LocalizedLink :to="`/product/${productComputed.translation.slug}/write-review`">{{ $t('Write a Review') }}</LocalizedLink>
                                </div>
                                <div class="mt-4">
                                    <PriceConverter :product="productComputed.productData" class="text-2xl" />
                                </div>
                                <div v-if="productStore.getVariants.length > 0" class="mt-4">
                                    <div v-for="ao in productStore.getVariants" :key="ao.id" class="mb-3">
                                        <div :id="`option${ao.id}`">
                                            <div class="mb-2">
                                                <span class="fw-bold">{{ translateItemField(ao, 'name', $i18n.locale) }}:</span>
                                                <span class="ms-2">{{ translateItemField(ao.values.find(v => v.id === selectedAttributes[ao.id]), 'name', $i18n.locale) }}</span>
                                            </div>
                                            <div class="flex flex-wrap gap-3">
                                                <div v-for="aov in ao.values" :value="aov.vid" :key="aov.vid" @click.stop="selectedAttributes[ao.id] = aov.vid" :data-aovid="aov.vid" :id="`attr-${ao.id}-${aov.vid}`" :class="`aov-item cursor-pointer py-2 px-3 border rounded text-center min-w-24 min-h-8 flex items-center justify-center ${selectedAttributes[ao.id] === aov.vid ? 'bg-blue-100 border-blue-500 text-blue-700 font-semibold shadow-md' : 'bg-gray-100 border-gray-300 text-gray-700'}`">
                                                    <template v-if="+ao.display_ov_image === 1 && aov.image">
                                                        <img :src="`/storage/${zucConfig.small_image_size}/${aov.image}`" :alt="productComputed.translation.name" :width="zucConfig.small_image_size" :height="zucConfig.small_image_size" class="img-fluid">
                                                    </template>
                                                    <template v-else>
                                                        {{ translateItemField(aov, 'name', $i18n.locale) }}
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="cart-block-outer">
                                    <div class="cart-block inline-block mt-3 w-auto">
                                        <template v-if="productComputed.productData.quantity > 0">
                                            <input v-model="formdata.qty" class="form-input inline-block mr-2 px-3 py-2 border border-gray-300 rounded-md focus:outline-hidden focus:ring-2 focus:ring-blue-500" type="number">
                                            <button class="inline-block text-white bg-green-500 hover:bg-green-600 focus:ring-2 focus:ring-green-400 px-4 py-2 rounded" type="submit">{{ $t('Add to Cart') }}</button>
                                        </template>
                                        <!-- <button v-else @click.prevent="showModal = true, picked = { id: productComputed.productData.id, name: productComputed.translation.name }" class="btn btn-warning inline-block align-items-center me-2" type="button">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-magic me-2" viewBox="0 0 16 16">
                                                <path d="M9.5 2.672a.5.5 0 1 0 1 0V.843a.5.5 0 0 0-1 0v1.829Zm4.5.035A.5.5 0 0 0 13.293 2L12 3.293a.5.5 0 1 0 .707.707L14 2.707ZM7.293 4A.5.5 0 1 0 8 3.293L6.707 2A.5.5 0 0 0 6 2.707L7.293 4Zm-.621 2.5a.5.5 0 1 0 0-1H4.843a.5.5 0 1 0 0 1h1.829Zm8.485 0a.5.5 0 1 0 0-1h-1.829a.5.5 0 0 0 0 1h1.829ZM13.293 10A.5.5 0 1 0 14 9.293L12.707 8a.5.5 0 1 0-.707.707L13.293 10ZM9.5 11.157a.5.5 0 0 0 1 0V9.328a.5.5 0 0 0-1 0v1.829Zm1.854-5.097a.5.5 0 0 0 0-.706l-.708-.708a.5.5 0 0 0-.707 0L8.646 5.94a.5.5 0 0 0 0 .707l.708.708a.5.5 0 0 0 .707 0l1.293-1.293Zm-3 3a.5.5 0 0 0 0-.706l-.708-.708a.5.5 0 0 0-.707 0L.646 13.94a.5.5 0 0 0 0 .707l.708.708a.5.5 0 0 0 .707 0L8.354 9.06Z"/>
                                            </svg>
                                            {{ $t('Notify when available') }}
                                        </button> -->
                                    </div>

                                    <!-- Hook after add to cart button. -->
                                    <template v-for="(component, index) in $pluginStorefrontHooks['product_after_add_to_cart_button']" :key="index">
                                        <component :is="component" :product="productComputed.productData" :translation="productComputed.translation" :formdata="formdata" @updateMetaForm="updateMetaForm"></component>
                                    </template>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div v-html="productComputed.translation.description"></div>
                </div>
                <div v-else class="flex items-center justify-center">
                    <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-blue-500"></div>
                    <p class="mt-4 text-gray-600 ps-4">{{ $t('Loading, please wait...') }}</p>
                </div>
            </div>
            
        </div>
    </div>
</template>