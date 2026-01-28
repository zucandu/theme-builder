<script setup>
import SearchForm from '@theme/storefront/components/article/SearchForm.vue'
import DisplayBlock from '@theme/storefront/components/menu/DisplayBlock.vue'

import { ref, onMounted } from 'vue';
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
const loadedTop = ref(false);
const blogTopMenu = ref([]);

onMounted(async () => {
    await getPaginatedArticles({ sort: 'new', keyword: route.query.keyword });
	loaded.value = true;
	
	blogTopMenu.value = await menuStore.fetchMenuByType('blog-top');
	loadedTop.value = true;
});

// Perform actions when the route changes
onBeforeRouteUpdate(async (to, from) => {
    if (to.query.keyword !== from.query.keyword || to.query.page !== from.query.page) {
        getPaginatedArticles(to.query);
    }
});

const getPaginatedArticles = async (obj) => {
    const res = await postStore.fetchPosts(obj);
    articles.value = res.paginator.data;
    pageLinks.value = res.paginator.links;
    paginationInfo.value = { from: res.paginator.from, to: res.paginator.to, total: res.paginator.total };
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
	<div class="lg:w-9/12 w-full mx-auto px-4 py-12">
		<h1 class="text-3xl font-bold text-gray-700 mb-4">{{ translateItemField(categoryInfo, 'name', $i18n.locale) }}</h1>
		<nav v-if="loadedTop" class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-4 border-b border-gray-200 sticky top-[110px] bg-white/95 backdrop-blur py-2 z-10">
			<div class="overflow-x-auto lg:whitespace-normal whitespace-nowrap scroll-smooth">
				<DisplayBlock :block="blogTopMenu" menu-key="blog-top" />
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
		<section class="mt-4">
			<!-- Loaded -->
			<div v-if="loaded" class="grid gap-6 md:grid-cols-1 gap-12">
				<article
					v-for="article in articles"
					:key="article.id || translateItemField(article, 'slug', $i18n.locale)"
					class="rounded-md overflow-hidden transition"
				>
					<LocalizedLink
						:to="`/article/${translateItemField(article, 'slug', $i18n.locale)}`"
						class="block group h-full"
					>
						<div class="grid grid-cols-1 sm:grid-cols-12 h-full">
							<!-- Image (left full) -->
							<div class="sm:col-span-5 h-full">
								<div class="aspect-[16/9] h-full w-full overflow-hidden bg-gray-50">
									<img
										:src="(article.image || (article.images && article.images[0]))
											? `/storage/${article.image || article.images[0]}`
											: 'https://placehold.co/400x300?text=No+Image'"
										:alt="translateItemField(article, 'title', $i18n.locale)"
										class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.05]"
										loading="lazy"
										@error="($event.target.src = 'https://placehold.co/400x300?text=No+Image')"
									/>
								</div>
							</div>

							<!-- Content (right) -->
							<div class="sm:col-span-7 p-4 sm:p-5 flex flex-col">
								<!-- categories + date -->
								<div class="mb-1 flex flex-wrap items-center gap-2 text-[11px] text-gray-500">
									<template v-if="article.categories && article.categories.length">
										<LocalizedLink
											v-for="(cat, i) in article.categories.slice(0, 2)"
											:key="i"
											:to="`/blog/category/${translateItemField(cat, 'slug', $i18n.locale)}`"
											class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 font-medium text-gray-700 hover:bg-gray-200"
										>
											{{ translateItemField(cat, 'name', $i18n.locale) }}
										</LocalizedLink>
									</template>
									<span v-if="article.date_added" class="ml-auto sm:ml-0">{{ article.date_added }}</span>
								</div>

								<!-- title -->
								<h2 class="text-lg sm:text-xl font-extrabold leading-snug text-gray-900 group-hover:text-blue-600">
									{{ translateItemField(article, 'title', $i18n.locale) }}
								</h2>

								<!-- summary -->
								<div
									v-if="translateItemField(article, 'summary', $i18n.locale)"
									class="mt-2 text-sm text-gray-600 line-clamp-3"
									v-html="translateItemField(article, 'summary', $i18n.locale)"
								></div>

								<!-- read more -->
								<span class="mt-3 inline-flex items-center gap-2 text-sm font-medium text-blue-600">
									{{ $t('Read more') }}
									<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
										<path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l5 5a1 1 0 010 1.414l-5 5a1 1 0 11-1.414-1.414L13.586 10 10.293 6.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
									</svg>
								</span>
							</div>
						</div>
					</LocalizedLink>
				</article>
			</div>

			<!-- Skeleton -->
			<div v-else class="grid gap-6 md:grid-cols-2">
				<div v-for="i in 6" :key="i" class="bg-white rounded-md overflow-hidden border shadow-sm">
					<div class="grid grid-cols-1 sm:grid-cols-12">
						<div class="sm:col-span-5">
							<div class="aspect-[16/9] sm:aspect-[4/3] bg-gray-200"></div>
						</div>
						<div class="sm:col-span-7 p-4 sm:p-5 space-y-3">
							<div class="h-5 bg-gray-200 rounded w-3/4"></div>
							<div class="h-4 bg-gray-200 rounded w-full"></div>
							<div class="h-4 bg-gray-200 rounded w-5/6"></div>
							<div class="h-4 bg-gray-200 rounded w-2/3"></div>
						</div>
					</div>
				</div>
			</div>

			<!-- Pagination (giữ nguyên logic của mày) -->
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
		</section>

	</div>
</template>