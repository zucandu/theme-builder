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
		<div class="text-lg font-semibold mb-4 font-bold uppercase tracking-widest bg-gray-100 lg:bg-white py-2 px-4 lg:p-0 rounded">{{ $t('Order Review') }}</div>
		<div class="cart-items">
			<table class="table-auto w-full border-collapse text-sm">
				<tbody>
					<tr v-for="item in items" :key="item.id" class="border-b">
						<td class="p-2 align-top w-24 h-24">
							<img v-if="item.images && item.images.length > 0" :src="`/storage/${zucConfig.medium_image_size}/${item.images[0].src}`" alt="product" class="rounded-md border">
						</td>
						<td class="p-2 align-top">
							<div v-html="translateItemField(item, 'name', $i18n.locale)"></div>
							<p v-if="+item.meta?.is_oversized === 1" class="text-yellow-600">{{ $t('Oversized product, not available for free shipping') }}</p>
							<div class="text-gray-500 text-sm">{{ $t('Qty') }}: {{ item.qty }}</div>

							<!-- Hook product title: checkout_product_title -->

						</td>
						<td class="p-2 text-right align-top">
							<PriceConverter :product="item" :qty="item.qty" />
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="total-summary mt-4">
			<table class="table-auto w-full border-collapse">
				<tfoot class="text-base font-bold">
					<tr>
						<td colspan="3" class="text-left lg:text-right border-0">{{ $t('Subtotal:') }}</td>
						<td class="text-right border-0">
							<PriceDisplay :price="subtotal" />
						</td>
					</tr>
					<template v-if="selections.discounts.length > 0">
						<tr v-for="(discount, index) in selections.discounts" :key="index">
							<td colspan="3" class="text-left lg:text-right border-0">{{ $t(discount.module) }}</td>
							<td class="text-right border-0">
								<span>-</span>
								<PriceDisplay :price="discount.details.amount" class="inline" />
							</td>
						</tr>
					</template>
					<tr v-if="selections.shipping.id">
						<td colspan="3" class="text-left lg:text-right border-0">{{ $t('Shipping: ') }}<!-- {{ selections.shipping.title }}--></td>
						<td class="text-right border-0">
							<PriceDisplay :price="shippingCost" />
						</td>
					</tr>
					<tr v-if="taxAmount > 0">
						<td colspan="3" class="text-left lg:text-right border-0">{{ $t('Sales Tax') }}:</td>
						<td class="text-right border-0">
							<PriceDisplay :price="taxAmount" />
						</td>
					</tr>
					<tr>
						<td colspan="3" class="text-left lg:text-right border-0 py-3">{{ $t('Total:') }}</td>
						<td class="text-right border-0 py-3">
							<PriceDisplay :price="total" />
						</td>
					</tr>
				</tfoot>
			</table>
		</div>

		<!-- Hook checkout summary: checkout_summary -->

	</div>
</template>