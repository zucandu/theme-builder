<script setup>

const props = defineProps({
    product: {
        type: Object,
        required: true
    }
})

const calculateDiscountedPrice = (discount) => {
    const price = parseFloat(props.product.price || 0)
    let finalPrice = price

    if (discount.discount_type === 1) {
        finalPrice = price - discount.discount_amount
    } else if (discount.discount_type === 2) {
        finalPrice = price - (price * (discount.discount_amount / 100))
    }

    return +finalPrice
}

const calculatePercentageDiscount = (discount) => {
    const price = parseFloat(props.product.price || 0)

    if (discount.discount_type === 1) {
        return ((discount.discount_amount / price) * 100).toFixed(2)
    } else if (discount.discount_type === 2) {
        return discount.discount_amount
    }

    return 0
}
</script>

<template>
    <div v-if="+props.product.quantity_discount_status === 1" class="mt-6">
        <!-- Heading text -->
        <div class="flex items-center text-lg font-semibold text-gray-800 mb-3">
            {{ $t('Buy More, Save More!') }}
        </div>

        <!-- Discounts grid -->
        <div class="grid grid-cols-4 lg:grid-cols-2 xl:grid-cols-4 gap-4">
            <div
                v-for="(discount, k) in props.product.quantity_discounts"
                :key="k"
                class="relative rounded-lg border bg-white shadow-sm hover:shadow-md transition p-1 sm:p-4 text-center"
            >
                <!-- Best value badge -->
                <div
                    v-if="k === props.product.quantity_discounts.length - 1"
                    class="absolute -top-2 right-3 hidden sm:inline-flex items-center gap-1 rounded-full bg-emerald-100 text-emerald-700 px-2 py-0.5 text-[10px] font-medium ring-1 ring-emerald-200"
                >
                    <span class="inline-block h-1 w-1 rounded-full bg-emerald-500"></span>
                    {{ $t('Best value') }}
                </div>

                <!-- Qty range -->
                <div class="text-xs uppercase tracking-wide text-gray-500 mb-1">
                    {{ $t('Buy') }}
                    <span class="font-medium text-gray-900">{{ discount.min_qty }}</span>
                    <template v-if="k === props.product.quantity_discounts.length - 1">+</template>
                    <template v-else>– <span class="font-medium text-gray-900">{{ discount.max_qty }}</span></template>
                </div>

                <!-- Price -->
                <div class="text-base font-semibold text-gray-900">
                    <PriceDisplay :price="calculateDiscountedPrice(discount)" />
                </div>

                <!-- Save % -->
                <div
                    v-if="calculatePercentageDiscount(discount) > 0"
                    class="mt-1 inline-flex items-center gap-1 rounded bg-rose-50 text-rose-700 px-1.5 py-0.5 text-[11px] font-semibold ring-1 ring-rose-200"
                >
                    <div class="hidden sm:block">{{ calculatePercentageDiscount(discount) }}%<br>{{ $t('OFF') }}</div>
					<div class="block sm:hidden text-xs">{{ calculatePercentageDiscount(discount) }}%</div>
                </div>
            </div>
        </div>
    </div>
</template>