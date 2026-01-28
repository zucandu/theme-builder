<script setup>
import SearchForm from '@theme/storefront/components/article/SearchForm.vue'
import DisplayBlock from '@theme/storefront/components/menu/DisplayBlock.vue'

import { ref, onMounted, nextTick } from 'vue';
import { onBeforeRouteUpdate, useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';

import { useHelpers } from '@/composables/useHelpers';
import { useRedirect } from '@/composables/useRedirect';

import { usePostStore } from '@/stores/utils/post';
import { useMenuStore } from '@/stores/utils/menu';

const route = useRoute();
const { t, locale } = useI18n();
const { translateItemField, getUrlParams, getUrlParam } = useHelpers();
const { redirectTo } = useRedirect();

const postStore = usePostStore();
const menuStore = useMenuStore();

const loaded = ref(false);

// 
const keyword = ref();
const articles = ref([]);
const pageLinks = ref([]);
const paginationInfo = ref({});
const categoryInfo = ref({});
const loadedTop = ref(false);
const blogTopMenu = ref([]);
const customMenu = ref({});

const sliderEl = ref(null)
function scrollActiveIntoView () {
    const wrap = sliderEl.value
    if (!wrap) return

    const active =
        wrap.querySelector('.router-link-exact-active') ||
        wrap.querySelector('.router-link-active') ||
        wrap.querySelector('[aria-current="page"]')

    if (!active) return

    const left = active.offsetLeft
    const width = active.offsetWidth
    const target = Math.max(0, left - (wrap.clientWidth - width) / 2)

    wrap.scrollTo({ left: target, behavior: 'smooth' })
}

onMounted(async () => {
    await getPaginatedArticles({ sort: 'new', category: route.params.slug });
	loaded.value = true;
	
	// Get custom menu if available
	customMenu.value = await menuStore.fetchMenuByType(categoryInfo.value.meta?.schematic_menu);
	
	blogTopMenu.value = await menuStore.fetchMenuByType('blog-top');
	loadedTop.value = true;
	
	await nextTick()
    scrollActiveIntoView();
});

// Perform actions when the route changes
onBeforeRouteUpdate(async (to, from) => {
    if (to.params.slug !== from.params.slug || to.query.page !== from.query.page) {
        await getPaginatedArticles({ ...to.query, category: to.params.slug});
		
		// Get custom menu if available
		customMenu.value = await menuStore.fetchMenuByType(categoryInfo.value.meta?.schematic_menu);
    }
});

const getPaginatedArticles = async (obj) => {
    const res = await postStore.fetchPosts(obj);
    articles.value = res.paginator.data;
    pageLinks.value = res.paginator.links;
    paginationInfo.value = { from: res.paginator.from, to: res.paginator.to, total: res.paginator.total };
    categoryInfo.value = res.object;
	
	// Set meta title
	document.title = translateItemField(categoryInfo.value, 'meta_title', locale.value);
}

const searchArticle = () => {
	redirectTo('/blog/search', {
		query: {
			keyword: keyword.value
		}
	});
}

</script>

<template>
	<div class="lg:w-9/12 w-full mx-auto px-4 py-4 lg:py-12">
		<h1 class="text-3xl font-bold text-gray-700 mb-4">{{ translateItemField(categoryInfo, 'name', $i18n.locale) }}</h1>
		<p>{{ translateItemField(categoryInfo, 'description', $i18n.locale) }}</p>
	<!--	<nav v-if="loadedTop" class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-4 border-b border-gray-200 sticky top-[94px] bg-white/95 backdrop-blur py-2 z-10"> -->
	      <nav v-if="loadedTop" class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-4 border-b border-gray-200 bg-white/95 backdrop-blur py-2 z-10 sticky top-[110px]">
			<div ref="sliderEl" class="overflow-x-auto lg:whitespace-normal whitespace-nowrap scroll-smooth">
				<template v-if="customMenu?.id > 0">
					<DisplayBlock :block="customMenu" :menu-key="categoryInfo.meta?.schematic_menu" :show-title="false" />
				</template>
				<template v-else>
					<DisplayBlock :block="blogTopMenu" menu-key="blog-top" />
				</template>
			</div>
			
			<form @submit.prevent="searchArticle" class="w-full md:w-auto">
				<div class="relative">
					<input
						ref="searchInputEl"
						v-model="keyword"
						type="text"
						:placeholder="$t('Search for articles')"
						aria-label="Search articles"
						class="w-full md:w-64 rounded-full pl-9 pr-20 py-1.5 text-sm
							   bg-white text-gray-900 placeholder:text-gray-400
							   ring-1 ring-gray-300 shadow-sm
							   focus:outline-none focus:ring-2 focus:ring-blue-500"
					/>

					<!-- Icon -->
					<span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
						<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
							<path fill-rule="evenodd" d="M12.9 14.32a8 8 0 111.414-1.414l3.387 3.387a1 1 0 01-1.414 1.414l-3.387-3.387zM14 8a6 6 0 11-12 0 6 6 0 0112 0z" clip-rule="evenodd"/>
						</svg>
					</span>

					<!-- Submit -->
					<button
						type="submit"
						class="absolute inset-y-0 right-1 my-0.5 rounded-full px-3 text-xs font-medium
							   bg-blue-600 text-white hover:bg-blue-700"
					>
						{{ $t('Search') }}
					</button>
				</div>
			</form>
		</nav>
		<div v-else class="mb-4 border-b border-gray-300 sticky top-[110px] bg-white px-4">
			<div class="flex space-x-4">
				<div class="h-6 w-24 bg-gray-200 rounded my-2"></div>
				<div class="h-6 w-20 bg-gray-200 rounded my-2"></div>
				<div class="h-6 w-28 bg-gray-200 rounded my-2"></div>
			</div>
		</div>
		
		<!-- Article Listing -->
		<div class="flex flex-col gap-6">
			<article
				v-if="articles[0]"
			>
				<div class="grid lg:grid-cols-12">
					<!-- Left: Image -->
					<div class="lg:col-span-6">
						<LocalizedLink
							:to="`/article/${translateItemField(articles[0], 'slug', $i18n.locale)}`"
							class="block group h-full"
						>
							<div class="h-full">
								<div class="aspect-[16/9] overflow-hidden">
									<img
										class="h-full w-full object-cover transition-transform duration-300 rounded-xl"
										:src="`/storage/${articles[0].image}`"
										:alt="translateItemField(articles[0], 'title', $i18n.locale)"
										loading="lazy"
									/>
								</div>
							</div>
						</LocalizedLink>
					</div>

					<!-- Right: Content -->
					<div class="lg:col-span-6 p-5 sm:p-6 flex flex-col">
					<!--	<div class="mb-2 flex items-center gap-3 text-xs text-gray-500">
							<span
								v-if="articles[0].categories?.[0]"
								class="rounded-full bg-gray-100 px-2.5 py-1 font-medium text-gray-700"
							>
								{{ translateItemField(articles[0].categories[0], 'name', $i18n.locale) }}
							</span>
							<span v-if="articles[0].date_added">{{ articles[0].date_added }}</span>
						</div>  -->

						<LocalizedLink
							:to="`/article/${translateItemField(articles[0], 'slug', $i18n.locale)}`"
							class="block"
						>
							<h3 class="text-2xl lg:text-3xl font-extrabold leading-snug tracking-tight text-gray-900">
								{{ translateItemField(articles[0], 'title', $i18n.locale) }}
							</h3>
						</LocalizedLink>

						<div
							v-if="translateItemField(articles[0], 'summary', $i18n.locale)"
							class="mt-3 text-base text-gray-600 line-clamp-4"
							v-html="translateItemField(articles[0], 'summary', $i18n.locale)"
						></div>

						<div class="mt-5">
							<LocalizedLink
								:to="`/article/${translateItemField(articles[0], 'slug', $i18n.locale)}`"
								class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-medium"
							>
								{{ $t('Read more') }}
								<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
									<path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l5 5a1 1 0 010 1.414l-5 5a1 1 0 11-1.414-1.414L13.586 10 10.293 6.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
									<path fill-rule="evenodd" d="M3 10a1 1 0 011-1h10a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
								</svg>
							</LocalizedLink>
						</div>
					</div>
				</div>
			</article>
			<div class="grid gap-6 md:grid-cols-3">
				<template v-for="(item, idx) in articles" :key="item.id">
					<article
						v-if="idx > 0"
						class="flex flex-col"
					>
						<LocalizedLink
							:to="`/article/${translateItemField(item, 'slug', $i18n.locale)}`"
							class="group block"
						>
							<!-- Image -->
							<div class="aspect-[16/9] overflow-hidden">
								<img
									class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.05] rounded-lg"
									:src="`/storage/${item.image}`"
									:alt="translateItemField(item, 'title', $i18n.locale)"
									loading="lazy"
								/>
							</div>

							<!-- Content -->
							<div class="py-4 flex flex-col flex-grow">
							<!--	<div class="mb-1 flex items-center gap-3 text-[11px] text-gray-500">
									<span
										v-if="item.categories?.[0]"
										class="uppercase tracking-wide text-gray-500 font-semibold"
									>
										{{ translateItemField(item.categories[0], 'name', $i18n.locale) }}
									</span>
									<span v-if="item.date_added">{{ item.date_added }}</span> 
								</div> -->

								<h5 class="text-lg font-bold leading-snug text-gray-900 group-hover:text-blue-600 mb-0">
									{{ translateItemField(item, 'title', $i18n.locale) }}
								</h5>

								<p
									v-if="translateItemField(item, 'summary', $i18n.locale)"
									class="mt-2 line-clamp-3 text-sm text-gray-600"
									v-html="translateItemField(item, 'summary', $i18n.locale)"
								></p>
							</div>
						</LocalizedLink>
					</article>
				</template>
			</div>
			<div v-if="articles.length > 0" class="flex justify-center items-center space-x-2 mt-6">
				<div
					v-for="(link, index) in pageLinks"
					:key="index"
					:class="[
						'inline-block rounded px-4 py-2 text-sm transition-colors duration-200',
						link.active
							? 'bg-blue-500 text-white cursor-default'
							: link.url
							? 'text-blue-500 hover:bg-blue-100'
							: 'text-gray-400 cursor-not-allowed'
					]"
				>
					<LocalizedLink
						v-if="link.url"
						:to="{
							path: `/blog/category/${route.params.slug}`,
							query: { ...getUrlParams(['page']), page: getUrlParam(link.url, 'page') }
						}"
						class="block w-full h-full text-center"
					>
						<span v-html="link.label"></span>
					</LocalizedLink>
					<span v-else class="block w-full h-full text-center" v-html="link.label"></span>
				</div>
			</div>
		</div>
	</div>
</template>