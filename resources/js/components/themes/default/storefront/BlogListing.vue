<script setup>
import SearchForm from '@theme/storefront/components/article/SearchForm.vue'
import DisplayBlock from '@theme/storefront/components/menu/DisplayBlock.vue'

import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';

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
const loadedSidebar1 = ref(false);
const blogSidebarMenu1 = ref([]);
const loadedTop = ref(false);
const blogTopMenu = ref([]);
const categories = ['events', 'interviews', 'shop-talk', 'chops', 'grooves', 'independence', 'rudiments', 'podcasts'];

// state
const categoryInfo = ref({});
const categoryArticles = ref({});
const catLoading = ref({})
const catError = ref({})

const setCategoryData = (slug, res) => {
    const list = res && res.paginator && Array.isArray(res.paginator.data)
        ? res.paginator.data.slice(0, 4)
        : []
    categoryArticles.value[slug] = list
	categoryInfo.value[slug] = res.object;
}

const loadCategoryArticles = async (slugs, postStore) => {
    slugs.forEach(s => { catLoading.value[s] = true; catError.value[s] = null })

    const settled = await Promise.allSettled(
        slugs.map(slug => postStore.fetchPosts({ category: slug, sort: 'new', page: 1 }))
    )

    slugs.forEach((slug, i) => {
        const r = settled[i]
        if (r.status === 'fulfilled') {
            setCategoryData(slug, r.value)
        } else {
            categoryArticles.value[slug] = [];
			categoryInfo.value[slug] = "";
            catError.value[slug] = (r.reason && r.reason.message) || 'Failed to load'
            console.error('Load category failed:', slug, r.reason)
        }
        catLoading.value[slug] = false
    })
}

onMounted(async () => {
    await getPaginatedArticles({ sort: 'new' });
	loaded.value = true;
	
	blogSidebarMenu1.value = await menuStore.fetchMenuByType('blog-sidebar1');
	loadedSidebar1.value = true;
	
	blogTopMenu.value = await menuStore.fetchMenuByType('blog-top');
	loadedTop.value = true;
	
	await loadCategoryArticles(categories, postStore);
	
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

const latestArticles = computed(() => {
    if (!articles.value) return [];

    return articles.value
        .filter(article => {
			// Check and only get articles are not schematics
            const isSchematic = article.categories.some(cat => 
                cat.translations.some(t => t.slug === 'schematics')
            );
            return !isSchematic;
        })
        .slice(0, 4);
});

</script>

<template>
    <div class="lg:w-9/12 w-full mx-auto px-4 py-12">
		<h1 class="text-3xl font-bold text-gray-700 mb-4">{{ $t(`Articles & Videos`) }}</h1>
		<!-- <nav v-if="loadedTop" class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-4 border-b border-gray-200 sticky top-[110px] bg-white/95 backdrop-blur py-2 z-10"> -->
		   <nav v-if="loadedTop" class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-4 border-b border-gray-200 bg-white/95 backdrop-blur py-2 z-10 sticky top-[110px]">
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
		
		<div class="flex flex-col gap-6">
			<article
				v-if="latestArticles[0]"
			>
				<div class="grid lg:grid-cols-12">
					<!-- Left: Image -->
					<div class="lg:col-span-6">
						<LocalizedLink
							:to="`/article/${translateItemField(latestArticles[0], 'slug', $i18n.locale)}`"
							class="block group h-full"
						>
							<div class="h-full">
								<div class="aspect-[16/9] overflow-hidden"> 
									<img
										class="h-full w-full object-cover transition-transform duration-300 rounded-xl"
										:src="`/storage/${latestArticles[0].image}`"
										:alt="translateItemField(latestArticles[0], 'title', $i18n.locale)"
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
								v-if="latestArticles[0].categories?.[0]"
								class="rounded-full bg-gray-100 px-2.5 py-1 font-medium text-gray-700"
							>
								{{ translateItemField(latestArticles[0].categories[0], 'name', $i18n.locale) }}
							</span>
							<span v-if="latestArticles[0].date_added">{{ latestArticles[0].date_added }}</span>
						</div> -->

						<LocalizedLink
							:to="`/article/${translateItemField(latestArticles[0], 'slug', $i18n.locale)}`"
							class="block"
						>
							<h3 class="text-2xl lg:text-3xl font-extrabold leading-snug tracking-tight text-gray-900">
								{{ translateItemField(latestArticles[0], 'title', $i18n.locale) }}
							</h3>
						</LocalizedLink>

						<div
							v-if="translateItemField(latestArticles[0], 'summary', $i18n.locale)"
							class="mt-3 text-base text-gray-600 line-clamp-4"
							v-html="translateItemField(latestArticles[0], 'summary', $i18n.locale)"
						></div>

						<div class="mt-5">
							<LocalizedLink
								:to="`/article/${translateItemField(latestArticles[0], 'slug', $i18n.locale)}`"
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
				<template v-for="(item, idx) in latestArticles" :key="item.id">
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
		</div> <!-- latest -->
		
		<div v-if="Object.keys(categoryArticles).length > 0">
			<div v-for="(data, slug) in categoryArticles" :key="slug" class="flex flex-col gap-6">
				<div class="flex justify-between items-center border-b border-gray-100 pb-2 mb-2 mt-6 uppercase">
					<div class="font-bold">{{ translateItemField(categoryInfo[slug], 'name', $i18n.locale) }}</div>
					<LocalizedLink :to="`/blog/category/${slug}`">{{ $t('More') }} {{ translateItemField(categoryInfo[slug], 'name', $i18n.locale) }}</LocalizedLink>
				</div>
				<article
					v-if="data[0]"
				>
					<div class="grid lg:grid-cols-12">
						<!-- Left: Image -->
						<div class="lg:col-span-6">
							<LocalizedLink
								:to="`/article/${translateItemField(data[0], 'slug', $i18n.locale)}`"
								class="block group h-full"
							>
								<div class="h-full">
									<div class="aspect-[16/9] overflow-hidden">
										<img
											class="h-full w-full object-cover transition-transform duration-300 rounded-xl"
											:src="`/storage/${data[0].image}`"
											:alt="translateItemField(data[0], 'title', $i18n.locale)"
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
									v-if="data[0].categories?.[0]"
									class="rounded-full bg-gray-100 px-2.5 py-1 font-medium text-gray-700"
								>
									{{ translateItemField(data[0].categories[0], 'name', $i18n.locale) }}
								</span>
								<span v-if="data[0].date_added">{{ data[0].date_added }}</span>
							</div> -->

							<LocalizedLink
								:to="`/article/${translateItemField(data[0], 'slug', $i18n.locale)}`"
								class="block"
							>
								<h3 class="text-2xl lg:text-3xl font-extrabold leading-snug tracking-tight text-gray-900">
									{{ translateItemField(data[0], 'title', $i18n.locale) }}
								</h3>
							</LocalizedLink>

							<div
								v-if="translateItemField(data[0], 'summary', $i18n.locale)"
								class="mt-3 text-base text-gray-600 line-clamp-4"
								v-html="translateItemField(data[0], 'summary', $i18n.locale)"
							></div>

							<div class="mt-4 text-sm text-gray-500">
								<span v-if="data[0].author">By {{ data[0].author }}</span>
							</div>

							<div class="mt-5">
								<LocalizedLink
									:to="`/article/${translateItemField(data[0], 'slug', $i18n.locale)}`"
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
					<template v-for="(item, idx) in data" :key="data.id">
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
			</div>
		</div>
    </div>
</template>