<script setup>
import SearchForm from '@theme/storefront/components/article/SearchForm.vue'
import ArticleCard from '@theme/storefront/components/article/ArticleCard.vue'

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
const articles = ref([]);
const pageLinks = ref([]);
const paginationInfo = ref({});
const categoryInfo = ref({});
const customMenu = ref({});

onMounted(async () => {
    await getPaginatedArticles({ sort: 'new', category: route.params.slug });
	loaded.value = true;
});

// Perform actions when the route changes
onBeforeRouteUpdate(async (to, from) => {
    if (to.params.slug !== from.params.slug || to.query.page !== from.query.page) {
        await getPaginatedArticles({ ...to.query, category: to.params.slug});
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

</script>

<template>
	<div class="py-12 bg-gray-50/50 min-h-screen">
		<div class="container mx-auto px-4 py-6">
			
			<!-- Header & Search -->
			<div class="mb-12 text-center max-w-2xl mx-auto">
				<h1 class="text-3xl font-bold text-gray-900 mb-2">{{ translateItemField(categoryInfo, 'name', $i18n.locale) }}</h1>
				<p class="text-gray-500 mb-6">{{ translateItemField(categoryInfo, 'description', $i18n.locale) }}</p>

				<div class="relative">
					<SearchForm />
				</div>
			</div>

			<!-- Article Listing -->
			<div v-if="loaded">
				<div v-if="articles.length > 0">
					 <!-- Info -->
					<div class="flex justify-between items-center pb-4 mb-6 border-b border-gray-200">
						<div class="text-sm text-gray-500 font-medium">
							{{ $t('listing.paginationArticle', { from: paginationInfo.from, to: paginationInfo.to, total: paginationInfo.total }) }}
						</div>
					</div>

					<!-- Grid -->
					<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
						<div v-for="article in articles" :key="article.id" class="h-full">
							<ArticleCard :article="article" />
						</div>
					</div>

					<!-- Pagination -->
					<div v-if="pageLinks.length > 0" class="flex justify-center items-center space-x-2 mt-12">
						<div
							v-for="(link, index) in pageLinks"
							:key="index"
							:class="[
								'inline-block transition-colors',
								link.active ? 'text-white bg-blue-600 shadow-md' : 'text-gray-600 hover:bg-gray-100',
								'rounded-lg px-4 py-2 font-medium'
							]"
						>
							<LocalizedLink
								v-if="link.url"
								:to="{
									path: `/blog/category/${route.params.slug}`,
									query: { ...getUrlParams(['page']), page: getUrlParam(link.url, 'page') }
								}"
								class="disabled-active block w-full h-full"
							>
								<span v-html="link.label"></span>
							</LocalizedLink>
							<span v-else class="block w-full h-full" v-html="link.label"></span>
						</div>
					</div>
				</div>
				<div v-else class="text-center py-20">
					<p class="text-gray-500">{{ $t('No articles found in this category.') }}</p>
				</div>
			</div>
			
			<!-- Skeleton -->
			<div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
				<div v-for="i in 6" :key="i" class="bg-white rounded-lg overflow-hidden border border-gray-100 shadow-sm h-96 animate-pulse">
					<div class="h-48 bg-gray-200"></div>
					<div class="p-5 space-y-4">
						<div class="h-6 bg-gray-200 rounded w-3/4"></div>
						<div class="h-4 bg-gray-200 rounded w-full"></div>
						<div class="h-4 bg-gray-200 rounded w-5/6"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>