<script setup>
import NavBlock from '@theme/storefront/components/menu/NavBlock.vue'
import BaseLink from '@theme/storefront/components/menu/BaseLink.vue'

const props = defineProps({
    items: {
        type: Array,
        required: true
    }
})
</script>

<template>
	<div class="dropdown-menu megamenu w-full">
		<div class="bg-white p-4 border container mx-auto">
			<div class="grid grid-cols-12 gap-4">
				<div
					v-for="block in items"
					:key="block.id"
					:class="[
						'col-span-12',
						`md:col-span-${block.block_width}`
					]"
				>
					<template v-for="(el, index) in block.elements" :key="el.id">
						<div
							:class="[
								'text-gray-600',
								index === 0 ? 'link-first' : '',
								el.heading === 1 ? 'link-heading font-bold' : ''
							]"
						>
							<BaseLink v-if="el.block_type === 'link'" :item="el" />
							<NavBlock v-else :item="el" />
						</div>
					</template>
				</div>
			</div>
		</div>
	</div>
</template>
