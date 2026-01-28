<script setup>
import ProductCardGrid from '@theme/storefront/components/product/ProductCardGrid.vue'
import ProductCardPlaceholder from '@theme/storefront/components/product/ProductCardPlaceholder.vue'
import FilterSidebar from '@theme/storefront/components/listing/FilterSidebar.vue'
import SelectedFiltersBar from '@theme/storefront/components/listing/SelectedFiltersBar.vue'

import { ref, onMounted, computed, watch } from 'vue';
import { onBeforeRouteUpdate, useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';

import { useHelpers } from '@/composables/useHelpers';
import { useListingStore } from '@/stores/catalog/listing';

const route = useRoute();
const router = useRouter();
const { t, locale } = useI18n();
const listingStore = useListingStore();

const { translateItemField, translateItemObj, getUrlParams, getUrlParam, getGridClasses } = useHelpers();

const loaded = ref(false);
const resetFilter = ref(false);
const sortBy = ref(getUrlParam(window.location.href, 'sort') || `sorting`);
const suppressQuerySync = ref(false);

const parseFlt = (v) => {
    if (!v) return [];
    const raw = Array.isArray(v) ? v.join('|') : String(v);
    return decodeURIComponent(raw)
        .split('|')
        .map(s => s.trim())
        .filter(Boolean);
};

const hydrateFromRoute = (r) => {
    selectedFilters.value = parseFlt(r.query.flt);
    sortBy.value = r.query.sort || 'sorting';
};

const sortByItems = ref([
    { field: 'popular', name: 'popular' },
    { field: 'price_asc', name: 'Price: Lowest First' },
    { field: 'price_desc', name: 'Price: Highest First' },
    { field: 'date_asc', name: 'Date Added: Old to New' },
    { field: 'date_desc', name: 'Date Added: New to Old' },
    { field: 'sku', name: 'Model Code: A to Z' },
    { field: 'sorting', name: 'Default Sorting' },
]);
const selectedFilters = ref([]);

const showFilterBar = ref(window.innerWidth > 992);

const toggleFilterSidebar = () => {
	showFilterBar.value = !showFilterBar.value;
	localStorage.setItem("enabled_filter_sidebar", showFilterBar.value ? 1 : 0);
}

const filterSideBarStatus = () => {
	if(window.innerWidth > 992) {
		if(localStorage.getItem("enabled_filter_sidebar")) {
			showFilterBar.value = +localStorage.getItem("enabled_filter_sidebar") === 1 ? true : false;
		}
	} else {
		showFilterBar.value = false;
	}
}

onMounted(async () => {
	
	suppressQuerySync.value = true;
    hydrateFromRoute(route);
	
    await listingStore.fetchProductsByManufacturer(route.params.slug, getUrlParams());
	
	suppressQuerySync.value = false;
	
    loaded.value = true;
	
	// Show the filter sidebar
	filterSideBarStatus();
});

const listingComputed = computed(() => {
    const objectTranslation = translateItemObj(listingStore.object, locale.value);
	
	// Set meta title
	document.title = objectTranslation?.meta_title;
	
    return { objectTranslation };
});

// Perform actions when the route changes
onBeforeRouteUpdate(async (to, from, next) => {
	
	suppressQuerySync.value = true;
    // hydrateFromRoute(to);

    await listingStore.fetchProductsByManufacturer(to.params.slug, {  ...to.query, sort: sortBy.value });

    next();
    requestAnimationFrame(() => {
        suppressQuerySync.value = false;
    });
	
});

watch(
    () => route.query.flt,
    (n, o) => {
        if (n === o) return;
        suppressQuerySync.value = true;
        selectedFilters.value = parseFlt(n);
        requestAnimationFrame(() => { suppressQuerySync.value = false; });
    }
);

// Watch filters
watch(
    () => selectedFilters.value,
    (newFilters, oldFilters) => {
        if (suppressQuerySync.value) return;

        const toStr = (arr) => Array.isArray(arr) ? arr.join('|') : '';
        const newStr = toStr(newFilters);
        const oldStr = toStr(oldFilters);
        if (newStr === oldStr) return;

        // Keeps page if filters do not change
        const fltInUrlStr = toStr(parseFlt(route.query.flt));
        if (newStr === fltInUrlStr) return;

        // Filters changed, set new queries
        const queries = { ...route.query };
        if (newFilters && newFilters.length) queries.flt = newStr;
        else delete queries.flt;

        delete queries.page;
        window.scrollTo({ top: 0, behavior: 'smooth' });
        router.replace({ query: queries });
    },
    { immediate: false }
);

// Watch sort
watch(
    () => sortBy.value,
    (newSort, oldSort) => {
        if (suppressQuerySync.value) return;
        if (newSort === oldSort) return;
        if (newSort === route.query.sort) return;

        const queries = { ...route.query };
        if (newSort) queries.sort = newSort; else delete queries.sort;

        delete queries.page;
        window.scrollTo({ top: 0, behavior: 'smooth' });
        router.replace({ query: queries });
    },
    { immediate: false }
);

</script>

<template>
    <div class="container mx-auto px-4">
		<section v-if="loaded" class="py-6">
			<h1 class="text-3xl font-bold text-gray-700">{{ listingComputed.objectTranslation?.name }}</h1>
			<div class="mt-4 w-full lg:w-1/2 text-gray-600 prose prose-sm" v-html="listingComputed.objectTranslation?.description"></div>
		</section>
		<section v-else class="py-6">
			<div class="h-8 bg-gray-300 rounded w-48"></div>
		</section>
		
		<!-- Hook: cateogry top -->
		<template v-for="(component, index) in $pluginStorefrontHooks['manufacturer_top']" :key="index">
			<component :is="component"></component>
		</template>
		
		<div class="mt-4">
			<button
				@click.stop="toggleFilterSidebar"
				id="filter-button"
				type="button"
				:class="[
					'px-6 py-2 rounded bg-gray-800 hover:bg-gray-700 text-white font-semibold shadow-sm uppercase flex items-center transition cursor-pointer',
					showFilterBar ? 'hidden' : 'flex'
				]"
			>
				<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 mr-2">
					<path stroke-linecap="round" stroke-linejoin="round" d="M6 13.5V3.75m0 9.75a1.5 1.5 0 0 1 0 3m0-3a1.5 1.5 0 0 0 0 3m0 3.75V16.5m12-3V3.75m0 9.75a1.5 1.5 0 0 1 0 3m0-3a1.5 1.5 0 0 0 0 3m0 3.75V16.5m-6-9V3.75m0 3.75a1.5 1.5 0 0 1 0 3m0-3a1.5 1.5 0 0 0 0 3m0 9.75V10.5" />
				</svg>
				<span v-if="showFilterBar">{{ $t('Hide Filter') }}</span>
				<span v-else>{{ $t('Filter') }}</span>
			</button>
		</div>
		
		<section v-if="loaded" class="grid grid-cols-12 gap-4">
		
			<!-- Sidebar Filter -->
			<div
				id="col-filter"
				:class="`block col-span-12 lg:col-span-2 filter-${showFilterBar ? 'show' : 'hide'}`"
			>
				<FilterSidebar v-model:selected="selectedFilters" :filters="listingStore.filters" :reset-filter="resetFilter" />
			</div>

			<!-- Main Product List -->
			<div :class="showFilterBar ? 'col-span-12 lg:col-span-10' : 'col-span-12'">
				<template v-if="+listingStore.paginationInfo.total > 0">
				
					<!-- Display items text and sort -->
					<div class="block sm:flex justify-between mb-4 items-center">
						<div class="showing my-3 sm:my-0">
							{{ $t('listing.paginationProduct', { from: listingStore.paginationInfo.from, to: listingStore.paginationInfo.to, total: listingStore.paginationInfo.total }) }}
						</div>
						<div class="flex items-center space-x-2">
							<select id="sort-by" v-model="sortBy" class="w-64 px-4 py-2 border select-bg appearance-none rounded focus:outline-hidden focus:ring-2 focus:ring-blue-500">
								<option v-for="item in sortByItems" :key="item.field" :value="item.field">{{ item.name }}</option>
							</select>
						</div>
					</div>

					<!-- Display the selected filters -->
					<SelectedFiltersBar :filters="listingStore.filters" :selected-filters="selectedFilters" class="mb-4" />
					
					<ProductCardGrid :items="listingStore.products" />
					
					<!-- Pagination -->
					<div class="lg:flex justify-center items-center space-x-2 mt-10">
						<div 
							v-for="(link, index) in listingStore.paginationLinks" 
							:key="index" 
							:class="['inline-block', link.active ? 'text-white bg-blue-500' : 'text-blue-500 hover:bg-blue-100', 'rounded']"
						>
							<LocalizedLink 
								:to="{ 
									path: `/manufacturer/${$route.params.slug}`, 
									query: { ...getUrlParams(['page']), page: getUrlParam(link.url, 'page') } 
								}" 
								class="disabled-active px-4 py-2 block"
							>
								<span v-html="link.label"></span>
							</LocalizedLink>
						</div>
					</div>
					
				</template>
				<div v-else class="text-center pt-10">
					<p class="text-gray-600">{{ $t('There is no product in this manufacturer.') }}</p>
					<a
						:href="`/manufacturer/${$route.params.slug}`"
						class="inline-block mt-4 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded shadow hover:bg-blue-700"
					>
						{{ $t('Reset all filtered') }}
					</a>
				</div>
			</div>
		</section>
		<section v-else class="grid grid-cols-12 gap-4">
		
			<!-- Sidebar Filter -->
			<div id="col-filter" class="hidden lg:block lg:col-span-2">
				<div class="space-y-4 p-4">
					<div class="h-6 bg-gray-300 rounded w-3/4"></div> <!-- section title -->
					<div class="h-4 bg-gray-200 rounded w-full"></div> <!-- item -->
					<div class="h-4 bg-gray-200 rounded w-5/6"></div> <!-- item -->
					<div class="h-4 bg-gray-200 rounded w-2/3"></div> <!-- item -->

					<div class="h-6 bg-gray-300 rounded w-1/2 mt-6"></div> <!-- another section -->
					<div class="h-4 bg-gray-200 rounded w-full"></div>
					<div class="h-4 bg-gray-200 rounded w-4/5"></div>
					<div class="h-4 bg-gray-200 rounded w-3/4"></div>
				</div>
			</div>

			<!-- Main Product List -->
			<div class="col-span-12 lg:col-span-10">
				<div :class="getGridClasses()">
					<ProductCardPlaceholder :item-count="+zucConfig.number_of_query_limit" />
				</div>
			</div>
		</section>
	</div>
</template>
