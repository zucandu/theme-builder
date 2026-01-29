<script setup>
import SearchForm from '@theme/storefront/components/article/SearchForm.vue'
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useHelpers } from '@/composables/useHelpers';
import { usePostStore } from '@/stores/utils/post';

const route = useRoute();
const { t, locale } = useI18n();
const { translateItemField, translateItemObj } = useHelpers();
const postStore = usePostStore();
const loaded = ref(false);
const article = ref({});

// Query product details by slug
onMounted(async () => {
    article.value = await postStore.retrieveArticleDetails(route.params.slug)
        .finally(() => loaded.value = true);
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
</script>

<template>
    <div class="py-12 bg-gray-50/50 min-h-screen">
        <MetaTags v-if="loaded" :title="articleComputed.translation?.meta_title" :description="articleComputed.translation?.meta_description" />
        <div class="container mx-auto px-4 py-6">
            <div class="grid lg:grid-cols-12 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-8">
                    <div v-if="loaded" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        
                        <!-- Article Image -->
                        <div class="aspect-video w-full overflow-hidden">
                             <img 
                                v-if="article.images && article.images.length > 0" 
                                :src="`${zucConfig.store_url}/storage/${article.images[0]}`" 
                                :alt="articleComputed.translation.title" 
                                class="w-full h-full object-cover" 
                            />
                        </div>

                        <!-- Article Content -->
                        <div class="p-6 md:p-8 lg:p-10">
                            <!-- Categories & Date -->
                            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 mb-6">
                                 <div v-if="article.categories?.length > 0" class="flex gap-2">
                                    <LocalizedLink 
                                        v-for="category in article.categories" 
                                        :key="category.id" 
                                        :to="`/blog/category/${translateItemField(category, 'slug', $i18n.locale)}`" 
                                        class="px-3 py-1 bg-blue-50 text-blue-700 rounded-full hover:bg-blue-100 font-medium transition-colors"
                                    >
                                        {{ translateItemField(category, 'name', $i18n.locale) }}
                                    </LocalizedLink>
                                </div>
                                <span class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ article.date_added }}
                                </span>
                            </div>

                            <!-- Title -->
                            <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-6 leading-tight">
                                {{ articleComputed.translation.title }}
                            </h1>

                            <!-- Author -->
                            <div class="flex items-center gap-3 mb-8 pb-8 border-b border-gray-100">
                                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-bold">
                                    {{ article.author ? article.author.charAt(0) : 'A' }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ article.author }}</p>
                                    <p class="text-xs text-gray-500">{{ $t('Author') }}</p>
                                </div>
                            </div>

                            <!-- Content Body -->
                            <div class="prose prose-lg max-w-none text-gray-700 prose-headings:font-bold prose-headings:text-gray-900 prose-a:text-blue-600 hover:prose-a:text-blue-700 prose-img:rounded-xl" v-html="articleComputed.translation.content"></div>
                            
                            <!-- Back Link -->
                             <div class="mt-12 pt-8 border-t border-gray-100">
                                <LocalizedLink to="/blog/listing" class="inline-flex items-center text-gray-600 hover:text-blue-600 font-medium transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                                    </svg>
                                    {{ $t('Back to Blog') }}
                                </LocalizedLink>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Loading State -->
                    <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden animate-pulse h-screen">
                        <div class="h-64 bg-gray-200"></div>
                        <div class="p-10 space-y-4">
                            <div class="h-8 bg-gray-200 rounded w-3/4"></div>
                            <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                            <div class="space-y-2 pt-8">
                                <div class="h-4 bg-gray-200 rounded w-full"></div>
                                <div class="h-4 bg-gray-200 rounded w-full"></div>
                                <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-4 space-y-8">
                    <div class="sticky top-24">
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                            <h3 class="font-bold text-gray-900 mb-4 text-lg">{{ $t('Search') }}</h3>
                             <SearchForm />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>