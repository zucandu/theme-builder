<script setup>
import { ref, onMounted, computed } from 'vue'

import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toastification';

import { useCartStore } from '@/stores/cart';
import { useHelpers } from '@/composables/useHelpers';

const toast = useToast();
const { t, locale } = useI18n();
const cartStore = useCartStore();
const { translateItemObj, translateItemField } = useHelpers();

const props = defineProps({
    product: Object,
	index: {
		type: Number,
		default: 0
	}
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

const emit = defineEmits(['open-restock-modal', 'open-review-modal', 'open-discount-modal']);

</script>

<template>
	<div class="flex flex-col h-full">
		<div :class="`relative ${index % 2 == 0 ? `product-even` : `product-odd`}`">
			<div class="aspect-[4/3] relative w-full overflow-hidden mb-4">
				<!-- Placeholder loading -->
				<div v-if="!loadedImg[`product${product.id}`]" class="absolute inset-0 flex items-center justify-center bg-gray-200 z-10"></div>
	
				<!-- Image -->
				<LocalizedLink :to="`/product/${translation.slug}`" class="hover:underline block mb-4">
					<img 
						:src="`/storage/${product.images[0]?.src}`" 
						class="absolute inset-0 w-full h-full object-contain transition-opacity duration-500" 
						:class="{ 'opacity-0': !loadedImg[`product${product.id}`], 'opacity-100': loadedImg[`product${product.id}`] }"
						:width="zucConfig.medium_image_size"
						:height="zucConfig.medium_image_size"
						@load="loadedImg[`product${product.id}`] = true"
					/>
				</LocalizedLink>
			</div>
			
			<div v-if="product.manufacturer" class="manufacturer">
				<LocalizedLink :to="`/manufacturer/${translateItemField(product.manufacturer, 'slug', $i18n.locale)}`" class="hover:underline font-bold">
					{{ translateItemField(product.manufacturer, 'name', $i18n.locale) }}
				</LocalizedLink>
            </div>

			<!-- Title -->
			<h2 class="leading-snug mb-1">
				<LocalizedLink :to="`/product/${translation.slug}`" class="hover:underline">
					{{ translation.name }}
				</LocalizedLink>
			</h2>

			<!-- SKU -->
			<div class="flex justify-between items-center mb-3">
				<div class="text-gray-500 text-xs">{{ product.sku }}</div>
				<div v-if="product.meta?.features" class="relative group inline-block">
					<svg
						xmlns="http://www.w3.org/2000/svg"
						width="16"
						height="16"
						fill="currentColor"
						class="bi bi-info-circle text-green-800 cursor-pointer tooltip-icon ml-2 lg:ml-0"
						viewBox="0 0 16 16"
					>
						<path d="M8 1a7 7 0 1 0 0 14A7 7 0 0 0 8 1zM0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8z"/>
						<path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.01-.252 1.223-.598l.088-.416c-.287.287-.655.287-.738 0l.738-3.468c.194-.897-.105-1.319-.808-1.319z"/>
						<circle cx="8" cy="4.5" r="1"/>
					</svg>

					<!-- TOOLTIP -->
					<div
						:class="`product-card-tooltip absolute bottom-full ${index % 2 == 0 ? `left-0 lg:left-full lg:-translate-x-full` : `right-0`} mb-2 hidden group-hover:block bg-gray-800 text-white text-sm rounded px-2 py-1 shadow-lg z-10 w-48 lg:w-64`"
						v-html="product.meta.features"
					>
					</div>
				</div>
			</div>
			
			<div v-if="product.total_reviews > 0" class="flex mb-3 items-center text-gray-500 text-sm">
				<button @click.prevent="emit('open-review-modal', { ...product, ...translation })" class="flex cursor-pointer">
					<svg
						v-for="i in 5"
						:key="'star-' + i"
						xmlns="http://www.w3.org/2000/svg"
						width="16" height="16"
						:fill="i <= Math.floor(product.rating) ? 'currentColor' : '#dddddd'"
						:class="i <= Math.floor(product.rating) ? 'text-yellow-300' : ''"
						class="bi bi-star-fill"
						viewBox="0 0 16 16"
					>
						<path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
					</svg>
					&nbsp;({{ product.total_reviews }})
				</button>
			</div>
			
			<p 
				v-if="+product.quantity_discount_status === 1" 
				@click.prevent="emit('open-discount-modal', { ...product, ...translation })" 
				class="mb-3 flex items-center gap-2 text-sm text-green-800 cursor-pointer"
			>
				<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-badge-percent-icon lucide-badge-percent shrink-0 hidden sm:block"><path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"/><path d="m15 9-6 6"/><path d="M9 9h.01"/><path d="M15 15h.01"/></svg>
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
		
		<!-- Add to Cart -->
		<div class="mt-auto pt-4 cart-form">
			<div v-if="+product.quantity > 0">
				<form v-if="product.type === `simple`" @submit.prevent="addToCart">
					<div class="flex items-center gap-2">
						<input v-model="formdata[product.id]" type="number" min="1" class="w-16 h-10 text-center border border-gray-300 rounded" />
						<button class="flex-1 bg-gray-900 text-white h-10 px-4 rounded hover:bg-gray-600 cursor-pointer">
							<span>{{ $t('+') }}</span>
							<span class="hidden sm:inline">{{ $t(` Add`) }}</span>
						</button>
					</div>
				</form>
				<LocalizedLink v-else :to="`/product/${translation.slug}`" class="flex items-center justify-center h-10 rounded bg-blue-500 hover:bg-blue-400 text-white">
					{{ $t('Options') }}
				</LocalizedLink>
			</div>
			<div v-else>
				<button
					@click.prevent="emit('open-restock-modal', { ...product, ...translation })"
					type="button"
					class="w-full h-10 px-4 rounded-md bg-yellow-600 text-white hover:bg-yellow-500 flex items-center justify-center gap-2 cursor-pointer"
				>
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 hidden sm:block">
						<path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
					</svg>
					{{ $t('Get Notified') }}
				</button>
			</div>
		</div>
	</div>
</template>