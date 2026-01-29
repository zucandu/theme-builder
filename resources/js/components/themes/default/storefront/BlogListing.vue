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
                <div class="grid grid-cols-12 gap-4">
                    <!-- Articles List -->
                    <div class="col-span-8">
                        <div class="space-y-4">
                            <!-- Display items text and sort -->
                            <div class="flex justify-between mb-4 items-center">
                                <div class="showing">
                                    {{ $t('listing.paginationArticle', { from: paginationInfo.from, to: paginationInfo.to, total: paginationInfo.total }) }}
                                </div>
                                <!-- <div class="flex items-center space-x-2">
                                    <label for="sort-by" class="text-sm font-medium text-gray-700">{{ $t('Sort By') }}</label>
                                    <select id="sort-by" v-model="sortBy" class="w-64 px-4 py-2 bg-white border rounded shadow-sm focus:outline-hidden focus:ring-2 focus:ring-blue-500">
                                        <option v-for="item in sortByItems" :key="item.field" :value="item.field">{{ item.name }}</option>
                                    </select>
                                </div> -->
                            </div>
                            <!-- Articles Loop -->
                            <div v-for="article in articles" :key="article.id" class="flex items-start p-4 border rounded-lg shadow-xs hover:shadow-md">
                                <ArticleCard :article="article" />
                            </div>
                        </div>

                        <!-- Pagination Links -->
                        <div class="flex justify-center items-center space-x-2 mt-6">
                            <div 
                                v-for="(link, index) in pageLinks" 
                                :key="index" 
                                :class="['inline-block', link.active ? 'text-white bg-blue-500' : 'text-blue-500 hover:bg-blue-100', 'rounded px-4 py-2']"
                            >
                                <LocalizedLink 
                                    :to="{ 
                                        path: `/article/listing`, 
                                        query: { ...getUrlParams(['page']), page: getUrlParam(link.url, 'page') } 
                                    }" 
                                    class="disabled-active"
                                >
                                    <span v-html="link.label"></span>
                                </LocalizedLink>
                            </div>
                        </div>
                    </div>

                    <!-- Search Form -->
                    <div class="col-span-4">
						
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>