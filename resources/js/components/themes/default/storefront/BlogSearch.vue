<script setup>
import SearchForm from '@theme/storefront/components/article/SearchForm.vue'
import ArticleCard from '@theme/storefront/components/article/ArticleCard.vue'

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

onMounted(async () => {
    await getPaginatedArticles({ sort: 'new', keyword: route.query.keyword });
	loaded.value = true;
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
	<div class="py-12 bg-gray-50/50 min-h-screen">
		<div class="container mx-auto px-4 py-6">
			
			<!-- Header & Search -->
			<div class="mb-12 text-center max-w-2xl mx-auto">
				<h1 class="text-3xl font-bold text-gray-900 mb-2">
					{{ $t('Search Results') }}
				</h1>
				<p v-if="route.query.keyword" class="text-gray-500 mb-6">
					{{ $t('Showing results for') }}: <span class="font-semibold text-gray-800">"{{ route.query.keyword }}"</span>
				</p>
				<div class="relative">
					<SearchForm :initial-keyword="route.query.keyword" />
				</div>
			</div>

			<!-- Article Listing -->
			<section>
				<!-- Loading Skeleton -->
				<div v-if="!loaded" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
					<div v-for="i in 6" :key="i" class="bg-white rounded-lg overflow-hidden border border-gray-100 shadow-sm h-96 animate-pulse">
						<div class="h-48 bg-gray-200"></div>
						<div class="p-5 space-y-4">
							<div class="h-6 bg-gray-200 rounded w-3/4"></div>
							<div class="h-4 bg-gray-200 rounded w-full"></div>
							<div class="h-4 bg-gray-200 rounded w-5/6"></div>
						</div>
					</div>
				</div>

				<!-- Results -->
				<div v-else>
					<div v-if="articles.length > 0">
						<!-- Sort & Info -->
						<div class="flex justify-between items-center pb-4 mb-6 border-b border-gray-200">
							<p class="text-sm text-gray-500 font-medium">
								{{ $t('listing.paginationArticle', { from: paginationInfo.from, to: paginationInfo.to, total: paginationInfo.total }) }}
							</p>
						</div>

						<!-- Grid -->
						<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
							<div v-for="article in articles" :key="article.id" class="h-full">
								<ArticleCard :article="article" />
							</div>
						</div>

						<!-- Pagination -->
						<div v-if="pageLinks.length > 1" class="flex justify-center items-center space-x-2 mt-12">
							<div 
								v-for="(link, index) in pageLinks" 
								:key="index" 
								:class="['inline-block transition-colors', link.active ? 'text-white bg-blue-600 shadow-md' : 'text-gray-600 hover:bg-gray-100', 'rounded-lg px-4 py-2 font-medium']"
							>
								<LocalizedLink 
									v-if="link.url"
									:to="{ 
										path: `/blog/search`, 
										query: { ...getUrlParams(['page']), keyword: route.query.keyword, page: getUrlParam(link.url, 'page') } 
									}" 
									class="disabled-active block w-full h-full"
								>
									<span v-html="link.label"></span>
								</LocalizedLink>
								<span v-else v-html="link.label" class="opacity-50 cursor-not-allowed block w-full h-full"></span>
							</div>
						</div>
					</div>
					
					<!-- No Results -->
					<div v-else class="text-center py-20">
						<div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4 text-gray-400">
							<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
							</svg>
						</div>
						<h3 class="text-xl font-medium text-gray-900 mb-2">{{ $t('No results found') }}</h3>
						<p class="text-gray-500">{{ $t('Try adjusting your search or filter to find what you\'re looking for.') }}</p>
					</div>
				</div>
			</section>
		</div>
	</div>
</template>