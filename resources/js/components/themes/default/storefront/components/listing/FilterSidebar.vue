<script setup>
import { ref, onBeforeMount, computed, watch, nextTick, defineModel } from 'vue';
import { Disclosure, DisclosureButton, DisclosurePanel, } from '@headlessui/vue'
import PriceRange from '@theme/storefront/components/listing/PriceRange.vue'
import NavSubcategories from '@theme/storefront/components/menu/NavSubcategories.vue'

import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useToast } from 'vue-toastification';

import { useHelpers } from '@/composables/useHelpers';

const route = useRoute();
const router = useRouter();
const { t, locale } = useI18n();
const toast = useToast();

const { translateItemField } = useHelpers();

const selected = defineModel('selected', { type: Array, default: [] });
const priceRange = ref({
    min: undefined,
    max: undefined
});

// Define props to accept `price` and `currency` from the parent component
const props = defineProps({
    filters: {
        type: Object,
        required: true
    },
	objectId: {
		type: Number,
		default: 0
	}
});

const filterAccordion = ref(['categories', 'manufacturers']);

const loadAccordionState = () => {
    const stored = localStorage.getItem('filter_accordion');
	if (stored) {
		try {
			const loaded = JSON.parse(stored);
			loaded.forEach(item => {
				if (!filterAccordion.value.includes(item)) {
					filterAccordion.value.push(item);
				}
			});
		} catch (e) {
			console.warn('Invalid filter accordion in localStorage:', e);
		}
	}
}

const toggleAccordion = (id) => {
    const index = filterAccordion.value.indexOf(id);
    if (index === -1) {
        filterAccordion.value.push(id);
    } else {
        filterAccordion.value.splice(index, 1);
    }
    localStorage.setItem('filter_accordion', JSON.stringify(filterAccordion.value));
}

const filterSeeMore = ref([]);
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

onBeforeMount(async () => {
	
	loadAccordionState();
	filterSeeMore.value = JSON.parse(localStorage.getItem("filter_see_more")) || [];
	
	await nextTick();
	setupObserver();
});

// Make filters reactive
const filters = computed(() => props.filters);

const closeFilterSidebar = () => {
	document.getElementById('filter-button').click();
}

const handleTouchStart = (event) => {
	this.touchStartX = event.changedTouches[0].screenX;
}

const handleTouchMove = (event) => {
	this.touchEndX = event.changedTouches[0].screenX;
}

const handleTouchEnd = () => {
	if (this.touchStartX < this.touchEndX - 50) {
		closeFilterSidebar();
	}
}

const minPrice = ref();
const maxPrice = ref();
const priceMinMax = computed(() => {
    const temp = []

    if (filters.value && filters.value.price && filters.value.price.ranges) {
        filters.value.price.ranges.forEach(o => {
            o.name.split('-').forEach(p => temp.push(Number(p)))
        })
    }

    return {
        min: temp.length > 0 ? Math.min(...temp) : 0,
        max: temp.length > 0 ? Math.max(...temp) : 0
    }
})

const applyPriceRange = () => {

	if (
        !minPrice.value || !maxPrice.value ||
        minPrice.value <= 0 || maxPrice.value <= 0 ||
        maxPrice.value <= minPrice.value
    ) {
		toast.warning(t(`Min/Max price must be greater than 0, and Max must be greater than Min.`));
        return
    }
	
	selected.value.forEach((str, index) => {
		if(str.indexOf("p:") !== -1) {
			selected.value.splice(index, 1);
		}
	})
	document.getElementById('price-range-trigger').click()
}

const resetPriceRange = () => {
	minPrice.value = maxPrice.value = null;
	document.getElementById('price-range-trigger').click()
}

const isStuck = ref(false)
const stickySentinel = ref(null)
const filterEl = ref(null)

let observer

/**
 * Convert a string like "170px" to a number 170
 */
function parsePx(px) {
    const n = Number(String(px).replace('px', ''))
    return Number.isFinite(n) ? n : 0
}

/**
 * Setup an IntersectionObserver to detect when the sticky element
 * reaches the top of the viewport
 */
function setupObserver() {
    if (!stickySentinel.value || !filterEl.value) return

    // Get the current "top" value from computed styles (e.g., '170px')
    const { top } = getComputedStyle(filterEl.value)
    const topPx = parsePx(top) || 170

    // Disconnect the previous observer before creating a new one
    if (observer) observer.disconnect()

    observer = new IntersectionObserver(
        ([entry]) => {
            // If the sentinel is no longer intersecting the viewport,
            // it means the sticky element has reached the top
            isStuck.value = entry.intersectionRatio === 0
        },
        {
            root: null,
            threshold: [0, 1],
            // Shift the root margin upwards by topPx so that "0" matches
            // the moment the sticky element touches the top
            rootMargin: `-${topPx}px 0px 0px 0px`,
        }
    )

    observer.observe(stickySentinel.value)
}

</script>

<template>
    <div class="filter-sidebar-outer h-full">
		<div ref="stickySentinel" aria-hidden="true" class="h-px w-full"></div>
        <div 
			v-if="Object.keys(filters).length > 0" 
			id="filter-sidebar" 
			ref="filterEl"
			:class="[
				'lg:sticky overflow-y-auto transition-all duration-300 ease-in-out',
				isStuck
					? 'lg:top-[150px] lg:max-h-[calc(100vh-165px)]'
					: 'lg:top-[170px] lg:max-h-[calc(100vh-290px)]'
			]"
		>
			<button
				@click.prevent="closeFilterSidebar"
				type="button"
				class="mb-4 px-6 py-2 rounded bg-gray-800 text-white font-semibold uppercase shadow-sm flex items-center hover:bg-gray-700 transition cursor-pointer"
			>
				<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 mr-2">
					<path stroke-linecap="round" stroke-linejoin="round" d="M6 13.5V3.75m0 9.75a1.5 1.5 0 0 1 0 3m0-3a1.5 1.5 0 0 0 0 3m0 3.75V16.5m12-3V3.75m0 9.75a1.5 1.5 0 0 1 0 3m0-3a1.5 1.5 0 0 0 0 3m0 3.75V16.5m-6-9V3.75m0 3.75a1.5 1.5 0 0 1 0 3m0-3a1.5 1.5 0 0 0 0 3m0 9.75V10.5" />
				</svg>
				{{ $t('Hide Filter') }}
			</button>
			
			<template v-if="+objectId > 0">
				<NavSubcategories :menu-key="`custom_category_${objectId}`" />
			</template>
			
            <!-- base -->
            <div v-for="(filterOptions, filterName) in filters.base" :key="filterName" :class="`filter-option border-0 count-${filterOptions.length}`">
                <div class="mb-4">
					<Disclosure :default-open="filterAccordion.includes(filterName)" v-slot="{ open }">
						<DisclosureButton @click="toggleAccordion(filterName)" class="flex w-full justify-between items-center cursor-pointer">
							<span>{{ t(filterName) }}</span>
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5" :class="open ? 'rotate-180 transform' : ''">
								<path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
							</svg>
						</DisclosureButton>
						<DisclosurePanel class="text-sm text-gray-500">
							<div class="mt-2 space-y-2 overflow-hidden max-h-33 transition-all duration-300 ease-in-out" 
								:class="{ 'max-h-none': filterSeeMore.includes(filterName) }">
								<div v-for="(option, index) in filterOptions" :key="index" class="flex items-center text-sm">
									<input v-model="selected" :value="option.id" type="checkbox" :id="`cb-option${option.id}`" class="form-checkbox text-blue-600 rounded border-gray-300 focus:ring-blue-500 disabled:opacity-50" :disabled="option.count === 0">
									<label :for="`cb-option${option.id}`" class="ml-2 text-gray-700">
										{{ translateItemField(option, 'name', $i18n.locale) }} 
										({{ option.count }})
									</label>
								</div>
							</div>
							<div v-if="filterOptions.length > 5" class="mt-2">
								<button
									class="text-sm text-blue-600 hover:underline cursor-pointer"
									@click="toggleSeeMore(filterName)">
									{{ filterSeeMore.includes(filterName) ? $t('See less') : $t('See more') }}
								</button>
							</div>
						</DisclosurePanel>
					</Disclosure>
                </div>
            </div>

            <!-- attribute -->
            <div v-for="(attOption, aoid) in filters.attribute" :key="aoid" :class="`accordion-item filter-option border-0 count-${Object.keys(attOption.values).length}`">
                <div class="mb-4">
					<Disclosure :default-open="filterAccordion.includes(attOption.id)" v-slot="{ open }">
						<DisclosureButton @click="toggleAccordion(attOption.id)" class="flex w-full justify-between items-center cursor-pointer">
							<span>{{ translateItemField(attOption, 'name', $i18n.locale) }}</span>
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5" :class="open ? 'rotate-180 transform' : ''">
								<path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
							</svg>
						</DisclosureButton>
						<DisclosurePanel class="text-sm text-gray-500">
							<div class="mt-2 space-y-2 overflow-hidden max-h-33 transition-all duration-300 ease-in-out" 
								:class="{ 'max-h-none': filterSeeMore.includes(attOption.id) }">
								<div v-for="(attOptionValue, aovid) in attOption.values" :key="aovid" class="flex items-center text-sm">
									<input v-model="selected" :value="attOptionValue.id" class="orm-checkbox text-blue-600 rounded border-gray-300 focus:ring-blue-500 disabled:opacity-50" type="checkbox" :id="`cb-option${attOptionValue.id}`" :disabled="attOptionValue.count === 0">
									<label class="ml-2 text-gray-700" :for="`cb-option${attOptionValue.id}`">
										{{ translateItemField(attOptionValue, 'name', $i18n.locale) }} 
										({{attOptionValue.count}})
									</label>
								</div>
							</div>
							<div v-if="Object.keys(attOption.values).length > 5" class="mt-2">
								<button
									class="text-sm text-blue-600 hover:underline cursor-pointer"
									@click="toggleSeeMore(attOption.id)">
									{{ filterSeeMore.includes(attOption.id) ? $t('See less') : $t('See more') }}
								</button>
							</div>
						</DisclosurePanel>
					</Disclosure>
                </div>
            </div>

            <!-- customize -->
			<!--
            <div v-for="(filterOptions, filterName) in filters.customize" :key="filterName" class="filter-option border-0">
				<Disclosure :default-open="filterAccordion.includes(filterOptions)" v-slot="{ open }">
					<DisclosureButton class="flex w-full justify-between items-center cursor-pointer">
						<span>{{ t(filterName) }}</span>
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5" :class="open ? 'rotate-180 transform' : ''">
							<path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
						</svg>
					</DisclosureButton>
					<DisclosurePanel class="text-sm text-gray-500">
						<div class="mt-2 space-y-2 max-h-60 overflow-y-auto pr-2">
                            <div v-for="(option, index) in filterOptions" :key="index" class="flex items-center text-sm">
                                <input v-model="selected" :value="option.id" class="form-checkbox text-blue-600 rounded border-gray-300 focus:ring-blue-500 disabled:opacity-50" type="checkbox" :id="`cb-option${option.id}`" :disabled="option.count === 0">
                                <label class="ml-2 text-gray-700" :for="`cb-option${option.id}`" v-html="t(option.name) + ' (' + option.count + ')'"></label>
                            </div>
                        </div>
					</DisclosurePanel>
				</Disclosure>
            </div>
			-->

            <!-- price -->
            <div v-for="(filterOptions, filterName) in filters.price" :key="filterName" class="filter-option border-0">
                <div class="mb-4">
					<Disclosure :default-open="filterAccordion.includes(`price-range`)" v-slot="{ open }">
						<DisclosureButton @click="toggleAccordion(`price-range`)" class="flex w-full justify-between items-center cursor-pointer">
							<span>{{ t('Price Range') }}</span>
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5" :class="open ? 'rotate-180 transform' : ''">
								<path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
							</svg>
						</DisclosureButton>
						<DisclosurePanel class="text-sm text-gray-500">
							<div class="mt-2 space-y-2 max-h-60 overflow-y-auto pr-2">
								<div class="mb-4 lg:me-4">
									<label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('Min price') }}</label>
									<input
										v-model="minPrice"
										type="number"
										:min="+priceMinMax.min"
										:max="+priceMinMax.max"
										step="0.1"
										:placeholder="`E.g. ${+priceMinMax.min}`"
										class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
									/>
								</div>

								<div class="mb-4 lg:me-4">
									<label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('Max price') }}</label>
									<input
										v-model="maxPrice"
										type="number"
										:min="+priceMinMax.min"
										:max="+priceMinMax.max"
										step="0.1"
										:placeholder="`E.g. ${+priceMinMax.max}`"
										class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
									/>
								</div>

								<button
									@click="applyPriceRange"
									type="button"
									class="inline-flex items-center px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded shadow-sm cursor-pointer"
								>
									<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 16 16">
										<path fill-rule="evenodd" d="M4 8a.5.5 0 0 1 .5-.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5A.5.5 0 0 1 4 8z"/>
									</svg>
									{{ $t('Apply') }}
								</button>

								<button
									@click="resetPriceRange"
									v-if="selected.filter(s => s.indexOf(`p:`) !== -1).length > 0"
									class="ml-2 text-sm text-blue-600 hover:underline cursor-pointer"
								>
									{{ $t('Clear') }}
								</button>

								<input
									v-model="selected"
									:value="`p:${minPrice}-${maxPrice}`"
									type="checkbox"
									class="sr-only"
									id="price-range-trigger"
								/>
							</div>
						</DisclosurePanel>
					</Disclosure>
                </div>
            </div>
			<div v-if="selected.length > 0" class="mr-4">
				<button
					id="clear-all-btn"
					@click="selected = []"
					class="w-full mb-2 hidden md:block border border-red-500 text-red-600 hover:bg-red-50 font-medium rounded px-4 py-2 text-sm cursor-pointer"
				>
					{{ t('Reset All') }}
				</button>
			</div>
        </div>
		<div
			@click="closeFilterSidebar"
			@touchstart="handleTouchStart"
			@touchmove="handleTouchMove"
			@touchend="handleTouchEnd"
			id="filter-overlay"
			class="fixed top-0 left-0 w-full h-full bg-black opacity-25 lg:hidden z-40"
		></div>
    </div>
</template>