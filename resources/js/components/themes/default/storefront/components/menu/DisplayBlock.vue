<script setup>
import { ref, onMounted } from 'vue'
import BaseLink from '@theme/storefront/components/menu/BaseLink.vue'
import NavDropdown from '@theme/storefront/components/menu/NavDropdown.vue'
import NavMegaMenu from '@theme/storefront/components/menu/NavMegaMenu.vue'
import MmenuSubmenu from '@theme/storefront/components/menu/MmenuSubmenu.vue'
import MmenuLink from '@theme/storefront/components/menu/MmenuLink.vue'

import { useHelpers } from '@/composables/useHelpers';
const { translateItemField, parseMenuLink } = useHelpers();

const props = defineProps({
    menuKey: {
        type: String,
        default: ""
    },
    block: {
        type: Object,
        default: () => ({})
    },
    showTitle: {
        type: Boolean,
        default: true
    }
});
		
</script>

<template>
	<div class="w-full">
		<section
			v-if="props.block"
			:class="`${props.menuKey}-section`"
			class="grid grid-cols-12"
		>
			<template v-for="(item, index) in props.block.items" :key="index">
				
				<div v-if="showTitle" :class="`h3 menu-block-${item.id} col-span-full text-lg font-bold text-gray-800 col-span-12`">
					{{ translateItemField(item, 'title', $i18n.locale) }}
				</div>
				
				<template v-for="(block, i) in item.blocks" :key="i">
					<div
						:class="`block-item block-item-${i} ${i === 0 ? `active` : `inactive`} content col-span-12 md:col-span-${block.block_width}`"
					>
						<div v-for="(el, elindex) in block.elements" :key="el.id" class="elem">
							<template v-if="el.block_type === `link`">
								<template v-if="el.link === `readonly`">
									<span :class="`nav-link cursor-pointer ${el.heading === 1 ? `heading` : ``}`">{{ translateItemField(el, 'title', $i18n.locale) }}</span>
								</template>
								<template v-else>
									<LocalizedLink v-if="el.link !== `webaddress`" :to="parseMenuLink(el, 'url', $i18n.locale)" :class="`nav-link hover:text-gray-600 text-gray-800 transition ${el.heading === 1 ? `heading` : `element-item`}`">
										{{ translateItemField(el, 'title', $i18n.locale) }}
									</LocalizedLink>
									<a v-else :href="translateItemField(el, 'url', $i18n.locale)" target="_blank" :class="`nav-link ${el.heading === 1 ? `heading` : ``}`">{{ translateItemField(el, 'title', $i18n.locale) }}</a>
								</template>
							</template>
							<template v-else>
							
								<!-- Icon -->
								<div v-if="el.icon" class="mb-3">
									<img
										:src="`/storage/${el.icon}`"
										:alt="translateItemField(el, 'title', $i18n.locale)"
										width="40"
										height="40"
										class="w-10 h-10 object-contain"
									/>
								</div>

								<!-- Main Image -->
								<div v-if="el.extra_data?.image" class="mb-4">
									<LocalizedLink
										v-if="el.extra_data?.no_link !== 1"
										:to="parseMenuLink(el, 'url', $i18n.locale)"
										class="block"
									>
										<img
											:src="`/storage/${el.extra_data?.image}`"
											:alt="translateItemField(el, 'title', $i18n.locale)"
											class="w-full h-auto rounded-lg object-cover"
										/>
									</LocalizedLink>
									<img
										v-else
										:src="`/storage/${el.extra_data?.image}`"
										:alt="translateItemField(el, 'title', $i18n.locale)"
										class="w-full h-auto rounded-lg object-cover"
									/>
								</div>

								<!-- Content -->
								<div :class="`space-y-2 index-${index}`">
									<!-- Title -->
									<div :class="['font-semibold ', el.heading === 1 ? 'text-lg uppercase mt-3 heading' : '']">
										<LocalizedLink
											v-if="el.extra_data?.no_link !== 1"
											:to="parseMenuLink(el, 'url', $i18n.locale)"
											class="text-gray-800"
										>
											{{ translateItemField(el, 'title', $i18n.locale) }}
										</LocalizedLink>
										<template v-else>
											<span class="text-gray-800">{{ translateItemField(el, 'title', $i18n.locale) }}</span>
										</template>
									</div>

									<!-- Content text -->
									<div v-html="translateItemField(el, 'content', $i18n.locale)" class="text-sm text-gray-600 element-item"></div>
								</div>

							</template>
						</div>
					</div>
				</template>
				
			</template>
		</section>

	</div>
</template>