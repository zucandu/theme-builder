<script setup>
import { ref, onMounted, computed } from 'vue'

import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toastification';

import { useCartStore } from '@/stores/cart';
import { useHelpers } from '@/composables/useHelpers';

const toast = useToast();
const { t, locale } = useI18n();
const cartStore = useCartStore();
const { translateItemObj } = useHelpers();

const props = defineProps({
    product: Array,
});

const loadedImg = ref({});
const formdata = ref({});

onMounted(() => {
	if (props.product?.id) {
		formdata.value[props.product.id] = 1;
	}
});

const translation = computed(() => {
	return translateItemObj(props.product, locale.value);
});

const addToCart = () => {
    cartStore.addProduct({ 
        id: props.product.id, 
        cart_quantity: formdata.value[props.product.id]
    }).then(() => {
        toast.success(t('cart.itemAdded', { name: translation.value.name }));
    }).catch(error => {
        toast.error(t(error.response.data.message));
    });

}

const productIds = ref([]);

</script>

<template>
	<div class="flex flex-col h-full">
		<div class="relative">
		
			<div class="aspect-[4/3] relative w-full overflow-hidden mb-4">
				<!-- Placeholder loading -->
				<div v-if="!loadedImg[`product${product.id}`]" class="absolute inset-0 flex items-center justify-center bg-gray-200 z-10"></div>
	
				<!-- Image -->
				<LocalizedLink :to="`/product/${translation.slug}`" class="hover:underline block mb-4">
					<img 
						:src="`/storage/${product.images[0]?.src}`" 
						class="absolute inset-0 w-full h-full object-contain transition-opacity duration-500" 
						:class="{ 'opacity-0': !loadedImg[`product${product.id}`], 'opacity-100': loadedImg[`product${product.id}`] }"
						@load="loadedImg[`product${product.id}`] = true"
					/>
				</LocalizedLink>
				
				<input
					v-modal="productIds"
					type="checkbox"
					class="absolute top-2 left-2 w-5 h-5 z-20 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
				/>

			</div>

			<!-- Title -->
			<h2 class="leading-snug mb-1">
				<LocalizedLink :to="`/product/${translation.slug}`" class="hover:underline">
					{{ translation.name }}
				</LocalizedLink>
			</h2>

			<!-- SKU -->
			<p class="text-gray-500 mb-2 text-xs">{{ product.sku }}</p>
			
			<p v-if="+product.quantity_discount_status === 1" class="mb-3 flex items-center gap-2 text-sm text-blue-400">
				<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 1010 10A10 10 0 0012 2z" />
				</svg>
				{{ $t('Buy More Save More') }}
			</p>
			<div v-if="product.availability_date" class="flex items-center gap-2 text-sm mb-3">
				<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-4 4h4m-4 4h4M5 3h14a2 2 0 012 2v16a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/>
				</svg>
				<span>{{ $t('Estimated Restock Date:') }} {{ product.availability_date }}</span>
			</div>

			<!-- Price -->
			<div class="text-lg font-bold text-gray-800">
				<PriceConverter :product="product" />
			</div>
		</div>
		
	</div>
</template>