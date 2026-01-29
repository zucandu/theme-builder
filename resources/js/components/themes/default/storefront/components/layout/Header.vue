<script setup>
import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue'

import { ref, onMounted, watch } from 'vue'

import DisplayMenu from '@theme/storefront/components/menu/DisplayMenu.vue'

import { useI18n } from 'vue-i18n';
import { useRouter, useRoute } from 'vue-router';
import { useSettingsStore } from '@/stores/settings'
import { useCartStore } from '@/stores/cart'
import { useAuthCustomerStore } from '@/stores/auth/customer';
import { useMenuStore } from '@/stores/utils/menu';
import { useHelpers } from '@/composables/useHelpers';
import { useRedirect } from '@/composables/useRedirect';
import { useLoader } from '@/composables/useLoader'

const { locale } = useI18n();
const router = useRouter();
const route = useRoute();

const settingsStore = useSettingsStore();
const menuStore = useMenuStore();
const cartStore = useCartStore();
const authCustomerStore = useAuthCustomerStore();

const { translateItemField, parseMenuLink } = useHelpers();
const { redirectTo } = useRedirect();
const { loadScript } = useLoader()

// Display & change currency
const selectedCurrency = ref(settingsStore.selectedCurrency);
watch(selectedCurrency, (newCurrency) => {
    settingsStore.setSelectedCurrency(newCurrency);
});

// Change language
const selectedLanguage = ref(settingsStore.selectedLanguage);
watch(selectedLanguage, (nLocale, oLocale) => {
    let currentPath = router.currentRoute.value.path.replace(/^\/|\/$/g, '');
    settingsStore.changeLanguage(currentPath, nLocale, oLocale);
});

// Use ref for reactivity
const primaryMenu = ref({}); 
const HIDE_DELAY = 300; // 0.3s
let cleanups = [];
onMounted(async () => {
	
	/*
	await loadScript('https://cdnjs.cloudflare.com/ajax/libs/headroom/0.12.0/headroom.min.js');
	const header = document.querySelector('header');
	const headroom = new Headroom(header, {
		offset: 10, 
		tolerance: 10,
		classes: {
			initial: 'headroom',
			pinned: 'headroom--pinned',
			unpinned: 'headroom--unpinned',
			top: 'headroom--top',
			notTop: 'headroom--not-top'
		}
	});
	headroom.init();
	*/
	
    primaryMenu.value = await menuStore.fetchMenuByType('primary').catch(error => {
		console.error('Error fetching menu data:', error)
	});
	
	// Close dropdown menu when click body
	window.addEventListener('click', function(e) {
		let autocomplete = document.querySelector('.autocomplete');
		if (autocomplete && !autocomplete.contains(e.target) && !e.target.classList.contains('autocomplete-input')) {
			autocomplete.classList.remove("block");
			autocomplete.classList.add('hidden');
		}
	});
	
	// Show the current dropdown menu when focus on input
	const autocompleteInput = document.querySelector('.autocomplete-input');
	autocompleteInput.addEventListener('focus', (event) => {
		const dropdown = event.target.nextSibling;
		if(!dropdown.classList.contains('block') && productSuggestions.value.length > 0) {
			dropdown.classList.remove("hidden");
			dropdown.classList.add("block");
		}
	});
	
	const nav = document.querySelector('.navbar-primary');
    if (!nav) return;
	let timer = null;
    let activeChain = [];

    const cancelClose = () => {
        if (timer) {
            clearTimeout(timer);
            timer = null;
        }
    };

    const buildChain = (item) => {
        const chain = [];
        let node = item;
        while (node && node !== nav) {
            if (node.classList && node.classList.contains('nav-item')) {
                chain.unshift(node);
            }
            node = node.parentElement;
        }
        return chain;
    };

    const applyChain = (chain) => {
        activeChain.forEach(li => {
            if (!chain.includes(li)) li.classList.remove('is-active');
        });
        chain.forEach(li => li.classList.add('is-active'));
        activeChain = chain;
    };

    const setActiveByItem = (item) => {
        cancelClose();
        nav.classList.add('keepalive');
        const chain = buildChain(item);
        applyChain(chain);
    };

    const scheduleClose = () => {
        cancelClose();
        timer = setTimeout(() => {
            nav.classList.remove('keepalive');
            activeChain.forEach(li => li.classList.remove('is-active'));
            activeChain = [];
        }, HIDE_DELAY);
    };

    const onMouseOver = (e) => {
        const item = e.target.closest('li.nav-item');
        if (item && nav.contains(item)) {
            setActiveByItem(item);
        }
    };

    const onNavEnter = () => cancelClose();
    const onNavLeave = () => scheduleClose();

    nav.addEventListener('mouseover', onMouseOver);
    nav.addEventListener('mouseenter', onNavEnter);
    nav.addEventListener('mouseleave', onNavLeave);
	
});

const selectedIndex = ref(-1);
const keyword = ref(null);
const productSuggestions = ref([]);
let debounceTimeout = null

watch(keyword, (v) => {
    clearTimeout(debounceTimeout)

    if (!v) {
        productSuggestions.value = []
        return
    }

    debounceTimeout = setTimeout(async () => {
        try {
			const res = await axios.get('/api/v3/app/EYQODGXioeBA54RL3AAUlPEcc3KSHaVaFV8Z0P36exwxQFTN/get', {
				params: {
					endpoint: 'api/v1/autocomplete',
					query: { keyword: v },
				}
			});
            productSuggestions.value = res.data.data.suggestions
            selectedIndex.value = -1
        } catch (error) {
            errorMsg.value = error.response?.data?.message || 'Something went wrong'
        }
    }, 100);
});

const handleKeydown = (event) => {
	if (event.code === 'ArrowDown' || event.keyCode === 40) {
		event.preventDefault();
		if (selectedIndex.value < productSuggestions.value.length - 1) {
			selectedIndex.value++;
		}
	} else if (event.code === 'ArrowUp' || event.keyCode === 38) {
		event.preventDefault();
		if (selectedIndex.value > 0) {
			selectedIndex.value--;
		}
	} else if (event.code === 'Enter' || event.keyCode === 13) {
		event.preventDefault();
		if (selectedIndex.value >= 0) {
			goTo(productSuggestions.value[selectedIndex.value]);
		} else {
			submitSearchForm();
		}
	}
}

const submitSearchForm = () => {
	
	// Hide the keyboard on the mobile
	document.activeElement?.blur();
	
	router.push(`/addon/advanced-search?keyword=${keyword.value}`);
	const autocomplete = document.querySelector('.autocomplete');
	if (autocomplete) {
		autocomplete.classList.remove('block');
		autocomplete.classList.add('hidden');
	}
}

const goTo = (item) => {
	router.push(`/product/${item._source.slug}`);
	productSuggestions.value = [];
    selectedIndex.value = -1;
}

const removeProduct = (id) => {
    cartStore.removeProduct(id);
}

</script>

<template>
    <header class="sticky top-0 z-50 bg-white transition-all duration-300 lg:py-0 py-1">
		<section class="header-middle py-1">
			<div class="container mx-auto px-4">
				<div class="flex flex-wrap items-center">
					<!-- Logo -->
					<div class="w-1/2 lg:w-1/4 flex items-center">
						<LocalizedLink to="/">
							<img
								:src="`/storage/${zucConfig.fileuploader_store_logo}`"
								:alt="zucConfig.store_name"
								:width="zucConfig.store_logo_width"
								:height="zucConfig.store_logo_height"
								class="object-cover logo transition-all duration-300"
							>
						</LocalizedLink>
					</div>

					<!-- Search form: hidden on mobile, show only on lg+ -->
					<div class="hidden lg:block w-full lg:w-5/12">
						<form @submit.prevent="submitSearchForm" class="w-full">
							<div class="relative flex">
								<!-- Input -->
								<input
									v-model="keyword"
									type="text"
									:placeholder="$t('Search for products')"
									@keydown="handleKeydown"
									class="autocomplete-input flex-grow px-4 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:border-gray-400 transition duration-150 ease-in-out"
								/>

								<!-- Autocomplete Dropdown -->
								<ul
									:class="`autocomplete absolute top-full left-0 mt-1 w-full z-10 bg-white border border-gray-200 rounded-md shadow-lg ${
										productSuggestions.length > 0 ? 'block' : 'hidden'
									}`"
								>
									<li
										v-for="(item, index) in productSuggestions"
										:key="index"
									>
										<a
											@click.stop="goTo(item)"
											href="javascript:void(0)"
											:class="[
												'block px-4 py-2 text-sm',
												selectedIndex === index
													? 'bg-gray-100 text-gray-900'
													: 'hover:bg-gray-50 text-gray-700'
											]"
										>
											{{ item._source.name_suggest }}
										</a>
									</li>
								</ul>
								
								<!-- Search Button -->
								<button
									type="submit"
									:aria-label="$t(`Click to submit your search`)"
									class="px-4 py-2 border border-l-0 border-gray-300 bg-white rounded-r-md cursor-pointer"
								>
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
										<path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
									</svg>
								</button>
								
							</div>
						</form>
					</div>

					<!-- Account icon & mini cart -->
					<div class="w-1/2 lg:w-1/3 flex justify-end items-center">
						<div class="flex justify-end items-center">
							<LocalizedLink to="/account" class="flex items-center gap-2 mr-6 text-gray-800 hover:text-blue-600 transition" :title="$t(`Go to My Account`)">
								<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
									<path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/>
								</svg>
							</LocalizedLink>

							<!-- Mini Cart -->
							<Popover v-if="route.name !== `checkout`" class="relative inline-flex">
								<PopoverButton class="relative inline-flex items-center gap-2 cursor-pointer outline-none">
									<svg v-if="cartStore.numberOfItems === 0" xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" :class="`bi bi-cart3 transition-all duration-300 ${cartStore.numberOfItems > 0 ? `` : `no-item`}`" viewBox="0 0 16 16">
										<path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9.397.472L4.415 11H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l.84 4.479 9.144-.459L13.89 4H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
									</svg>
									<span v-else class="relative">
										<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="31.999" height="31.999" :class="`cart4 ${cartStore.numberOfItems > 0 ? `has-item` : ``}`" viewBox="0 0 67 67">
											<image y="0" width="63" height="63" xlink:href="data:img/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAYAAAA7MK6iAAACS0lEQVRIib2XbUtVQRDHf5nChUx60DQosqgoi1S6WEZFQUT0HfoKvep1X6qgTO1N9aaybhlBFERFkD1AZKXh1RND/4XhsOfcA+1xYNm9c2bmv7PzsHsBngFtYA34BdwBelgHMrAsN07UDdsFDAMTwCjwUPyT6+Gxp+vyfEabqoO2xmxecEd+rCbg18CDPHMQ+CPgZg2gwbG1/HEuAC+13lMD8ITmG7E4vsoJpaTDstWKAc9pHqkB+JTm+djHy4rDW2BDQtCdalJme29MYJ8TGE4IfEk2P1JQq+81cDFJQcHW4yLgtovBWELgUJ6tImCj55pTeNytbjWp3/OBGaOWeMeBI4r5dt1avcA2bXpQs33bBDSAHdLtl32T3wIMiP+kbJfjwGrk1vrfcS+cclG5NFROQwL6CXyR51+VB8ZbBFbU8TJ9WwJ+a53pW1uOvFBLLqX7Urymo+pJ+UAou/pCnC2e3+XZSirgouTCZfYZYHMCrMWqgmddIv3QWArXWoeRSTboLatxNKoA9wFTShSfzcsVMjuvY4k1WxXY6BzwzRn4BFxRDx8rGLuAqy6rbXwAjlYFnYzsPFMf7y/R2w98jugtVOmEXYpJpiMalXdz7jEYI+sLbyRzWzpNp3ezE/But9Mhxz/gYjYQ0Tvoks+/Jpsu9r1UfMK23TrUselt7KC3WrDOypTM8FMJTesZZJfFI/Hului9k8wtxXRcfxSMZ1XSkU6XlIvFvIjOF+hYXR+qAmxk5WQ3SlA278O9WkYXXa+3Y7ZEM8//EfAXUm/aTBW2rGEAAAAASUVORK5CYII="/>
										</svg>
										<span class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/3 flex items-center justify-center h-5 w-5 text-[12px] font-bold text-blue-600">{{ cartStore.numberOfItems }}</span>
									</span>

									<span class="hidden sm:block text-sm">
										<span class="uppercase font-bold text-gray-900 block transition-all duration-300 cart-text">{{ $t('Shopping cart') }}</span>
										<span class="total-items block">
											{{ cartStore.numberOfItems }} {{ $t('item(s)') }} - <PriceDisplay :price="cartStore.total" class="inline" />
										</span>
									</span>
								</PopoverButton>

								<transition
									enter="transition duration-200 ease-out"
									enter-from="opacity-0 translate-y-1"
									enter-to="opacity-100 translate-y-0"
									leave="transition duration-150 ease-in"
									leave-from="opacity-100 translate-y-0"
									leave-to="opacity-0 translate-y-1"
								>
									<PopoverPanel
										 v-slot="{ close }"
										class="absolute top-[30px] right-0 z-20 mt-2 w-72 sm:w-80 bg-white shadow-lg rounded-md p-4"
									>
										<template v-if="cartStore.numberOfItems > 0">
											<div class="overflow-auto space-y-4 max-h-80">
												<div v-for="(item, key) in cartStore.getItems" :key="item.id" :class="key > 0 ? 'border-t pt-4' : ''">
													<div class="flex gap-3">
														<div class="w-16 h-16 flex-shrink-0">
															<img :src="`/storage/${zucConfig.small_image_size}/${item.images[0].src}`" alt="" class="w-full h-full object-contain border rounded" />
														</div>
														<div class="flex-1 text-sm">
															<div class="flex justify-between items-start">
																<LocalizedLink :to="`/product/${translateItemField(item, 'slug', $i18n.locale)}`" class="hover:underline">
																	<span v-html="`${item.qty} x ${translateItemField(item, 'name', $i18n.locale)}`"></span>
																</LocalizedLink>
																<button @click.stop="removeProduct(item.id)" class="text-gray-400 text-xs hover:text-red-500 mx-3 cursor-pointer">
																	<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
																		<path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
																	</svg>
																</button>
															</div>
															<PriceConverter :product="item" :qty="item.qty" />
														</div>
													</div>
												</div>
											</div>

											<!-- Footer -->
											<div class="border-t mt-3 pt-3 space-y-3">
												<div class="flex justify-between text-sm">
													<div>{{ $t('Subtotal') }}</div>
													<div><PriceDisplay :price="cartStore.total" class="inline" /></div>
												</div>
												<LocalizedLink to="/checkout" @click="close" class="block w-full text-center bg-gray-900 text-white py-2 rounded hover:bg-gray-600 text-sm">
													{{ $t('Checkout') }}
												</LocalizedLink>
												<LocalizedLink to="/cart" @click="close" class="block w-full text-center border border-gray-300 text-gray-800 py-2 rounded hover:bg-gray-100 text-sm">
													{{ $t('View Cart') }}
												</LocalizedLink>
											</div>
										</template>
										<p v-else class="p-4 text-gray-500 text-sm">{{ $t('Your cart is currently empty!') }}</p>

									</PopoverPanel>
								</transition>
							</Popover>
							<LocalizedLink v-else to="/cart" class="relative inline-flex items-center gap-2 cursor-pointer outline-none">
								<svg v-if="cartStore.numberOfItems === 0" xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" :class="`bi bi-cart3 transition-all duration-300 ${cartStore.numberOfItems > 0 ? `` : `no-item`}`" viewBox="0 0 16 16">
									<path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9.397.472L4.415 11H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l.84 4.479 9.144-.459L13.89 4H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
								</svg>
								<span v-else class="relative">
									<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="31.999" height="31.999" :class="`cart4 ${cartStore.numberOfItems > 0 ? `has-item` : ``}`" viewBox="0 0 67 67">
										<image y="0" width="63" height="63" xlink:href="data:img/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAYAAAA7MK6iAAACS0lEQVRIib2XbUtVQRDHf5nChUx60DQosqgoi1S6WEZFQUT0HfoKvep1X6qgTO1N9aaybhlBFERFkD1AZKXh1RND/4XhsOfcA+1xYNm9c2bmv7PzsHsBngFtYA34BdwBelgHMrAsN07UDdsFDAMTwCjwUPyT6+Gxp+vyfEabqoO2xmxecEd+rCbg18CDPHMQ+CPgZg2gwbG1/HEuAC+13lMD8ITmG7E4vsoJpaTDstWKAc9pHqkB+JTm+djHy4rDW2BDQtCdalJme29MYJ8TGE4IfEk2P1JQq+81cDFJQcHW4yLgtovBWELgUJ6tImCj55pTeNytbjWp3/OBGaOWeMeBI4r5dt1avcA2bXpQs33bBDSAHdLtl32T3wIMiP+kbJfjwGrk1vrfcS+cclG5NFROQwL6CXyR51+VB8ZbBFbU8TJ9WwJ+a53pW1uOvFBLLqX7Urymo+pJ+UAou/pCnC2e3+XZSirgouTCZfYZYHMCrMWqgmddIv3QWArXWoeRSTboLatxNKoA9wFTShSfzcsVMjuvY4k1WxXY6BzwzRn4BFxRDx8rGLuAqy6rbXwAjlYFnYzsPFMf7y/R2w98jugtVOmEXYpJpiMalXdz7jEYI+sLbyRzWzpNp3ezE/But9Mhxz/gYjYQ0Tvoks+/Jpsu9r1UfMK23TrUselt7KC3WrDOypTM8FMJTesZZJfFI/Hului9k8wtxXRcfxSMZ1XSkU6XlIvFvIjOF+hYXR+qAmxk5WQ3SlA278O9WkYXXa+3Y7ZEM8//EfAXUm/aTBW2rGEAAAAASUVORK5CYII="/>
									</svg>
									<span class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/3 flex items-center justify-center h-5 w-5 text-[12px] font-bold text-blue-600">{{ cartStore.numberOfItems }}</span>
								</span>

								<span class="hidden sm:block text-sm">
									<span class="uppercase font-bold text-gray-900 block transition-all duration-300 cart-text">{{ $t('Shopping cart') }}</span>
									<span class="total-items block">
										{{ cartStore.numberOfItems }} {{ $t('item(s)') }} - <PriceDisplay :price="cartStore.total" class="inline" />
									</span>
								</span>
							</LocalizedLink>
						</div>
					</div>
				</div>
			</div>
		</section>
		<div class="nav-shadow">
			<div class="container flex items-center mx-auto px-4">
				<div class="flex-grow-1">
					<DisplayMenu menu-key="primary" :items="primaryMenu?.items" :responsive="true" />
				</div>
				<div class="text-right block lg:hidden ml-3 flex-grow-1">
					<form @submit.prevent="submitSearchForm" class="relative w-full">
						<input
							v-model="keyword"
							enterkeyhint="go"
							type="search"
							class="border w-full px-3 py-2 pr-10 border border-gray-300 rounded focus:outline-none focus:ring focus:border-blue-300"
							:placeholder="$t(`Search...`)"
							@search="submitSearchForm"
							@keyup.enter="submitSearchForm"
						/>
						
						<svg
							class="w-5 h-5 text-gray-400 absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none"
							fill="none"
							stroke="currentColor"
							stroke-width="2"
							viewBox="0 0 24 24"
						>
							<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M16 10a6 6 0 11-12 0 6 6 0 0112 0z" />
						</svg>
					</form>
				</div>
			</div>
		</div>
	</header>
</template>

<style scoped>
@media (min-width: 1024px) {
    .nav-shadow {
        border-bottom: 1px solid #ced4da;
		border-top: 1px solid #ced4da;
		box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .08);
    }
}

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

.navbar-primary .nav-item:hover > .base-link {
    background-color: #e9ecef;
}

.navbar-primary .nav-item.dropdown.has-sub > .base-link:after {
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