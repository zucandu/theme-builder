<script setup>
import { ref, onMounted } from 'vue'
import BaseLink from '@theme/storefront/components/menu/BaseLink.vue'
import NavDropdown from '@theme/storefront/components/menu/NavDropdown.vue'
import NavMegaMenu from '@theme/storefront/components/menu/NavMegaMenu.vue'
import MmenuSubmenu from '@theme/storefront/components/menu/MmenuSubmenu.vue'
import MmenuLink from '@theme/storefront/components/menu/MmenuLink.vue'

import { useLoader } from '@/composables/useLoader';
import { useHelpers } from '@/composables/useHelpers';
const { translateItemField } = useHelpers();
const { loadScript, loadCSS } = useLoader();

const props = defineProps({
    menuKey: String,
    items: Array,
    responsive: Boolean
});

const drawer = ref(null);
const navigator = ref(null);

const windowWidth = ref(1024);

const resizeHandler = () => {
	windowWidth.value = window.innerWidth;
}

onMounted(async () => {
	resizeHandler();
	window.addEventListener('resize', resizeHandler);
	
	loadCSS('https://cdn.jsdelivr.net/npm/mmenu-light/dist/mmenu-light.css')
    await loadScript('https://cdn.jsdelivr.net/npm/mmenu-light/dist/mmenu-light.js')

    const menu = new window.MmenuLight(document.querySelector('#mobile-menu'))
    navigator.value = menu.navigation();
	drawer.value = menu.offcanvas();
	
});

const openMmenu = () => {
	navigator.value.openPanel(document.querySelector( "#panel-mobile-menu" ));
    drawer.value.open();
}

const closeMmenu = () => {
	drawer.value.close();
}
		
</script>

<template>
	<div class="w-full">
		<nav v-if="windowWidth >= 1024" :class="`navbar-${props.menuKey} flex flex-nowrap justify-start`">
			<div v-if=" props.items?.length > 0" class="w-full">
				<ul class="flex flex-row top-links relative">
					<li :class="`nav-item mr-4 ${item.blocks && item.blocks.length > 0 ? (item.blocks.length > 1 ? `dropdown has-megamenu` : 'dropdown') : ''}`" v-for="(item, index) in props.items" :key="index">
						<BaseLink :item="item" extra-class="p-2 block text-gray-600 relative" />
						<template v-if="item.blocks && item.blocks.length > 0 && item.submenu === 'dropdown'">
							<NavDropdown :items="item.blocks[0].elements" />
						</template>
						<template v-if="item.blocks && item.blocks.length > 0 && item.submenu === 'megamenu'">
							<NavMegaMenu :items="item.blocks" />
						</template>
					</li>
				</ul>
			</div>
			<div v-else>
				<div class="flex space-x-4 py-1">
					<div class="w-24 p-4 rounded-md bg-gray-200"></div>
					<div class="w-24 p-4 rounded-md bg-gray-200"></div>
					<div class="w-24 p-4 rounded-md bg-gray-200"></div>
					<div class="w-24 p-4 rounded-md bg-gray-200"></div>
					<div class="w-24 p-4 rounded-md bg-gray-200"></div>
				</div>
			</div>
		</nav>
		<div v-if="responsive">
			<template v-if="windowWidth < 1024">
				<button @click.prevent="openMmenu">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8">
						<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
					</svg>
				</button>
				<button @click="closeMmenu" id="close-mmenu" type="button" class="hidden">{{ $t('Close') }}</button>
			</template>
			<nav id="mobile-menu">
				<ul id="panel-mobile-menu">
					<template v-if=" props.items?.length > 0 && windowWidth < 992">
						<li v-for="(item, index) in props.items" :key="index">
							<template v-if="item.blocks && item.blocks.length > 0 && item.submenu !== 'nosub'">
								<span>{{ translateItemField(item, 'title', $i18n.locale) }}</span>
								<ul>
									<template v-for="block in item.blocks" :key="block.id">
										<li v-for="el in block.elements" :key="el.id">
											<template v-if="el.children.length > 0">
												<span>{{ translateItemField(el, 'title', $i18n.locale) }}</span>
												<MmenuSubmenu :items="el.children" />
											</template>
											<template v-else>
												<MmenuLink :item="el" />
											</template>
										</li>
									</template>
								</ul>
							</template>
							<template v-else>
								<MmenuLink :item="item" />
							</template>
						</li>
					</template>
				</ul>
			</nav>
		</div>
	</div>
</template>

<style scoped>
.navbar-primary .nav-item.dropdown .dropdown-menu {
	display: block;
    opacity: 0;
    transform: translateY(10px);
    transition: all 0.3s ease;
    visibility: hidden;
    pointer-events: none;
    position: absolute;
    z-index: 50;
}

/* .navbar-primary .nav-item.dropdown {
	position: relative;
} */

.navbar-primary .nav-item.dropdown:hover > .dropdown-menu {
    opacity: 1;
    transform: translateY(0);
    visibility: visible;
    pointer-events: auto;
}

.navbar-primary.keepalive .nav-item.is-active > .dropdown-menu {
    opacity: 1;
    transform: translateY(0);
    visibility: visible;
    pointer-events: auto;
}

.navbar-primary:not(.click-to-hide) .nav-item.is-active > .dropdown-menu {
    opacity: 0;
    transform: translateY(-8px);
    visibility: hidden;
    pointer-events: none;
}

.navbar-primary .nav-item:hover > :deep(.base-link) {
    background-color: #e9ecef;
}

.navbar-primary .nav-item.dropdown.has-sub > :deep(.base-link):after {
    content: "";
    position: absolute;
    right: 10px;
    top: 50%;
    width: 6px;
    height: 6px;
    border-right: 2px solid #ccc;
    border-bottom: 2px solid #ccc;
    transform: translateY(-50%) rotate(-45deg);
    pointer-events: none;
}
</style>