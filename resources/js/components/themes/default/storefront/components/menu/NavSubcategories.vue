<script setup>
import { ref, watch, onMounted } from 'vue'
import { Disclosure, DisclosureButton, DisclosurePanel, } from '@headlessui/vue'
import BaseLink from '@theme/storefront/components/menu/BaseLink.vue'
import NavDropdown from '@theme/storefront/components/menu/NavDropdown.vue'
import NavMegaMenu from '@theme/storefront/components/menu/NavMegaMenu.vue'

import { useMenuStore } from '@/stores/utils/menu';

const menuStore = useMenuStore();

const props = defineProps({
    menuKey: String,
});

const loaded = ref(false);
const menu = ref([]);
const loadMenu = async () => {
    menu.value = await menuStore.fetchMenuByType(props.menuKey);
	loaded.value = true;
}

const filterSeeMore = ref([]);

onMounted(() => {
	loadMenu();
	filterSeeMore.value = JSON.parse(localStorage.getItem("filter_see_more")) || [];
});

//
watch(() => props.menuKey, () => {
    loadMenu()
});

const toggleSeeMore = (key) => {
	if (filterSeeMore.value.includes(key)) {
		// Remove
		filterSeeMore.value = filterSeeMore.value.filter(k => k !== key);
	} else {
		// Add
		filterSeeMore.value.push(key);
	}
	localStorage.setItem("filter_see_more", JSON.stringify(filterSeeMore.value));
}

</script>

<template>
	<div v-if="loaded">
		<div v-if="menu && menu.items?.length > 0" :class="`mb-4 count-${menu.items.length}`">
			<Disclosure :default-open="true" v-slot="{ open }">
				<DisclosureButton class="flex w-full justify-between items-center cursor-pointer">
					<span>{{ $t(`Sub-categories`) }}</span>
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5" :class="open ? 'rotate-180 transform' : ''">
						<path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
					</svg>
				</DisclosureButton>
				<DisclosurePanel class="text-sm text-gray-500">
					<div class="mt-2 space-y-2 pr-2">
						<div class="w-full">
							<ul class="relative space-y-2 overflow-hidden max-h-33 transition-all duration-300 ease-in-out" 
								:class="{ 'max-h-none': filterSeeMore.includes(`subcategories`) }">
								<li :class="`nav-item ${item.blocks && item.blocks.length > 0 ? (item.blocks.length > 1 ? `dropdown has-megamenu` : 'dropdown') : ''}`" v-for="(item, index) in menu.items" :key="index">
									<BaseLink :item="item" extra-class="block text-gray-700 relative" />
									<template v-if="item.blocks && item.blocks.length > 0 && item.submenu === 'dropdown'">
										<NavDropdown :items="item.blocks[0].elements" />
									</template>
									<template v-if="item.blocks && item.blocks.length > 0 && item.submenu === 'megamenu'">
										<NavMegaMenu :items="item.blocks" />
									</template>
								</li>
							</ul>
							<div v-if="menu.items.length > 5" class="mt-2">
								<button
									class="text-sm text-blue-600 hover:underline cursor-pointer"
									@click="toggleSeeMore(`subcategories`)">
									{{ filterSeeMore.includes(`subcategories`) ? $t('See less') : $t('See more') }}
								</button>
							</div>
						</div>
					</div>
				</DisclosurePanel>
			</Disclosure>
		</div>
	</div>
</template>