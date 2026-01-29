<script setup>
import SearchForm from '@theme/storefront/components/article/SearchForm.vue'
import ArticleCard from '@theme/storefront/components/article/ArticleCard.vue'
import { ref, onMounted } from 'vue';
import { onBeforeRouteUpdate, useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useHelpers } from '@/composables/useHelpers';
import { usePostStore } from '@/stores/utils/post';

const route = useRoute();
const { t, locale } = useI18n();
const { translateItemField, getUrlParams, getUrlParam } = useHelpers();
const postStore = usePostStore();
const loaded = ref(false);

// 
const articles = ref([]);
const pageLinks = ref([]);
const paginationInfo = ref({});
onMounted(async () => {
    getPaginatedArticles({
        sort: 'new'
    });
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

</script>

<template>
    <div>
        <div class="py-12">
            <div class="container mx-auto py-6">
                <!-- Header & Search -->
                <div class="mb-12 text-center max-w-2xl mx-auto">
                     <h1 class="text-3xl font-bold text-gray-900 mb-6">{{ $t('Our Blog') }}</h1>
                    <div class="relative">
                        <SearchForm />
                    </div>
                </div>

                <div class="flex flex-col gap-8">
                     <!-- Sort & Info -->
                    <div class="flex justify-between items-center pb-4 border-b border-gray-100">
                        <div class="text-sm text-gray-500 font-medium">
                            {{ $t('listing.paginationArticle', { from: paginationInfo.from, to: paginationInfo.to, total: paginationInfo.total }) }}
                        </div>
                    </div>

                    <!-- Articles Grid -->
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
                            :class="['inline-block transition-colors', link.active ? 'text-white bg-blue-600 shadow-md' : 'text-gray-600 hover:bg-gray-100', 'rounded-lg px-4 py-2 font-medium']"
                        >
                            <LocalizedLink 
                                v-if="link.url"
                                :to="{ 
                                    path: `/article/listing`, 
                                    query: { ...getUrlParams(['page']), page: getUrlParam(link.url, 'page') } 
                                }" 
                                class="disabled-active block w-full h-full"
                            >
                                <span v-html="link.label"></span>
                            </LocalizedLink>
                            <span v-else v-html="link.label" class="opacity-50 cursor-not-allowed block w-full h-full"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>