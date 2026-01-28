<script setup lang="ts">
import SearchForm from '@theme/storefront/components/article/SearchForm.vue'
import DisplayBlock from '@theme/storefront/components/menu/DisplayBlock.vue'
import Schematic from '@theme/storefront/components/article/Schematic.vue'

import { ref, onMounted, computed } from 'vue';
import { useRoute, onBeforeRouteUpdate } from 'vue-router';
import { useI18n } from 'vue-i18n';

import { useHelpers } from '@/composables/useHelpers';
import { useRedirect } from '@/composables/useRedirect';

import { usePostStore } from '@/stores/utils/post';
import { useMenuStore } from '@/stores/utils/menu';

const route = useRoute();
const { t, locale } = useI18n();
const { translateItemField, translateItemObj } = useHelpers();
const { redirectTo } = useRedirect();

const postStore = usePostStore();
const menuStore = useMenuStore();

const loaded = ref(false);
const keyword = ref();
const article = ref({});
const loadedTop = ref(false);
const blogTopMenu = ref([]);
const customMenu = ref({});

// Query product details by slug
onMounted(async () => {

    article.value = await postStore.retrieveArticleDetails(route.params.slug);
	loaded.value = true;
	
	// Get custom menu if available
	customMenu.value = await menuStore.fetchMenuByType(article.value.meta?.schematic_menu);
	
	blogTopMenu.value = await menuStore.fetchMenuByType('blog-top');
	loadedTop.value = true;
});

// Perform actions when the route changes
onBeforeRouteUpdate(async (to, from, next) => {
	if(to.params.slug !== route.params.slug) {
		article.value = await postStore.retrieveArticleDetails(to.params.slug);
		loaded.value = true
	}
    next(); // Always call next() to resolve navigation
});

const articleComputed = computed(() => {
    // Check if article is loaded before accessing its fields
    if (article.value && Object.keys(article.value).length > 0) {
        const translation = translateItemObj(article.value, locale.value);
        return { translation };
    } else {
        // Return a default value or empty object if not loaded
        return { translation: {} };
    }
});

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
		<template v-if="+article.meta?.no_sidebox !== 1 || !article.meta?.no_sidebox">
		<!--	<h1 class="text-3xl font-bold text-gray-700 mb-4">{{ $t(`Library`) }}</h1>
			<nav v-if="loadedTop" class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-4 border-b border-gray-200 sticky top-[94px] bg-white/95 backdrop-blur py-2 z-10"> -->
			<nav v-if="loadedTop" class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-4 border-b border-gray-200 bg-white/95 backdrop-blur py-2 z-10 sticky top-[94px]">
				<div class="overflow-x-auto lg:whitespace-normal whitespace-nowrap scroll-smooth">
					<template v-if="customMenu?.id > 0">
						<DisplayBlock :block="customMenu" :menu-key="article.meta?.schematic_menu" :show-title="false" />
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
		</template>

		<article>
			<h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white mb-4">{{ articleComputed.translation.title }}</h1>
			<div
				class="prose prose-gray max-w-none dark:prose-invert prose-img:rounded-lg prose-a:text-blue-600 dark:prose-a:text-blue-400"
				v-html="articleComputed.translation.content"
			></div>
		   <Schematic v-if="loaded" :article="article" />
			<div v-if="article.categories?.length" class="flex flex-wrap gap-2 my-4">
			<span class="text-2xl tracking-tight text-gray-900 dark:text-white">{{ $t('Found in these categories:  ') }} </span>
				<LocalizedLink
					v-for="(category, idx) in article.categories"
					:key="category.id || translateItemField(category,'slug',$i18n.locale) || idx"
					:to="`/blog/category/${translateItemField(category,'slug',$i18n.locale)}`"
					class="group inline-flex items-center gap-1.5 px-3 py-1 rounded-full
						   bg-gray-100 text-gray-700 hover:bg-gray-200
						   dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700
						   border border-transparent hover:border-gray-300 dark:hover:border-gray-600
						   transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
					aria-label="Category tag"
				>
					<!-- Tag icon -->
					<svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 opacity-70 group-hover:opacity-100" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
						<path d="M6 4.5A1.5 1.5 0 1 1 3 4.5 1.5 1.5 0 0 1 6 4.5zm-1 0a.5.5 0 1 0-1 0 .5.5 0 0 0 1 0z"/>
						<path d="M2 1h4.586a1 1 0 0 1 .707.293l7 7a1 1 0 0 1 0 1.414L9.414 14.293a1 1 0 0 1-1.414 0l-7-7A1 1 0 0 1 1 6.586V2a1 1 0 0 1 1-1zm0 5.586 7 7L13.586 9l-7-7H2v4.586z"/>
					</svg>

					<span class="truncate">
						{{ translateItemField(category, 'name', $i18n.locale) }}
					</span>
				</LocalizedLink>
			</div>
		</article>		
	</div>
</template>