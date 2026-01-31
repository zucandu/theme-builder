<script setup>
import { useHelpers } from '@/composables/useHelpers';
const { translateItemField } = useHelpers();

const props = defineProps({
    items: {
        type: Array,
        required: true,
        default: () => []
    },
	subtotal: {
        type: Number,
        required: true,
        default: 0
    },
    shippingCost: {
        type: Number,
        required: true,
        default: 0
    },

    taxAmount: {
        type: Number,
        required: true,
        default: 0
    },

    total: {
        type: Number,
        required: true,
        default: 0
    },
    selections: {
        type: Object,
        required: true,
        default: () => ({})
    }
})

</script>

<template>
	<div class="cart-contents">
		<!-- <div class="text-lg font-semibold mb-4 font-bold uppercase tracking-widest bg-gray-50 p-4 rounded-t-xl border-b border-gray-100">{{ $t('Order Review') }}</div> -->
        <div class="p-6 bg-gray-50 border-b border-gray-100">
             <h3 class="font-bold text-gray-900 text-lg">{{ $t('Order Summary') }}</h3>
             <p class="text-sm text-gray-500 mt-1">{{ items.length }} {{ $t('items in cart') }}</p>
        </div>

		<div class="cart-items p-6 max-h-[400px] overflow-y-auto custom-scrollbar">
            <div class="space-y-6">
                 <div v-for="item in items" :key="item.id" class="flex gap-4">
                    <div class="h-20 w-20 shrink-0 overflow-hidden rounded-md border border-gray-200">
                        <img 
                            v-if="item.images && item.images.length > 0" 
                            :src="`/storage/${item.images[0].src}`" 
                            alt="product" 
                            class="h-full w-full object-cover object-center"
                        >
                        <div v-else class="h-full w-full bg-gray-100 flex items-center justify-center text-gray-400">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                    </div>

                    <div class="flex flex-1 flex-col">
                        <div>
                            <div class="flex justify-between text-base font-medium text-gray-900">
                                 <h3 class="line-clamp-2">
                                     <span v-html="translateItemField(item, 'name', $i18n.locale)"></span>
                                 </h3>
                                 <p class="ml-4 shrink-0">
                                     <PriceConverter :product="item" :qty="item.qty" />
                                 </p>
                            </div>
                            <!-- <p class="mt-1 text-sm text-gray-500">{{ item.color }}</p> -->
                        </div>
                        <div class="flex flex-1 items-end justify-between text-sm">
                            <p class="text-gray-500">{{ $t('Qty') }} {{ item.qty }}</p>
                        </div>
                        <p v-if="+item.meta?.is_oversized === 1" class="text-xs text-yellow-600 mt-1 bg-yellow-50 px-2 py-1 rounded inline-block">
                            {{ $t('Oversized item') }}
                        </p>
                    </div>
                </div>
            </div>
		</div>

		<div class="border-t border-gray-100 bg-gray-50/50 p-6 space-y-4">
            <div class="flex justify-between text-base font-medium text-gray-900">
                <p class="text-gray-500">{{ $t('Subtotal') }}</p>
                <p><PriceDisplay :price="subtotal" /></p>
            </div>
            
            <template v-if="selections.discounts.length > 0">
                 <div v-for="(discount, index) in selections.discounts" :key="index" class="flex justify-between text-base font-medium text-green-600">
                    <p>{{ $t(discount.module) }}</p>
                     <p>
                        - <PriceDisplay :price="discount.details.amount" class="inline" />
                    </p>
                </div>
            </template>

            <div v-if="selections.shipping.id" class="flex justify-between text-base font-medium text-gray-900">
                <p class="text-gray-500">{{ $t('Shipping') }}</p>
                 <p><PriceDisplay :price="shippingCost" /></p>
            </div>

            <div v-if="taxAmount > 0" class="flex justify-between text-base font-medium text-gray-900">
                <p class="text-gray-500">{{ $t('Sales Tax') }}</p>
                <p><PriceDisplay :price="taxAmount" /></p>
            </div>

            <div class="flex justify-between items-center border-t border-gray-200 pt-4 mt-4">
                <p class="text-xl font-bold text-gray-900">{{ $t('Total') }}</p>
                <p class="text-2xl font-bold text-gray-900"><PriceDisplay :price="total" /></p>
            </div>
		</div>

		<!-- Hook checkout summary: checkout_summary -->
	</div>
</template>