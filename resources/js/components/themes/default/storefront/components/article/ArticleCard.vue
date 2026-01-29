<script setup>
import { useHelpers } from '@/composables/useHelpers';
const { translateItemField, getUrlParams, getUrlParam } = useHelpers();

// Define props
defineProps({
    article: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <article class="flex flex-col h-full bg-white rounded-lg shadow-sm hover:shadow-lg transition-shadow duration-300 overflow-hidden border border-gray-100">
        <!-- Image Section -->
        <LocalizedLink :to="`/article/${translateItemField(article, 'slug', $i18n.locale)}`" class="block relative aspect-video overflow-hidden">
            <img 
                v-if="article.images && article.images.length > 0" 
                :src="`${zucConfig.store_url}/storage/${article.images[0]}`" 
                :alt="translateItemField(article, 'title', $i18n.locale)" 
                class="w-full h-full object-cover transition-transform duration-500 hover:scale-105" 
            />
            <div v-else class="w-full h-full bg-gray-100 flex items-center justify-center text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <!-- Date Badge -->
             <div v-if="article.date_added" class="absolute top-2 right-2 bg-white/90 backdrop-blur-sm px-2 py-1 text-xs font-semibold text-gray-700 rounded shadow-sm">
                {{ article.date_added }}
            </div>
        </LocalizedLink>

        <!-- Content Section -->
        <div class="flex flex-col flex-1 p-5">
            <!-- Categories -->
            <div v-if="article.categories && article.categories.length > 0" class="mb-2 flex flex-wrap gap-2 text-xs">
                 <LocalizedLink v-for="(category, index) in article.categories" :key="index" :to="`/blog/category/${translateItemField(category, 'slug', $i18n.locale)}`" class="text-blue-600 hover:text-blue-800 font-medium uppercase tracking-wider">
                    {{ translateItemField(category, 'name', $i18n.locale) }}
                </LocalizedLink>
            </div>

            <LocalizedLink :to="`/article/${translateItemField(article, 'slug', $i18n.locale)}`" class="block group">
                <h2 class="text-xl font-bold text-gray-900 group-hover:text-blue-600 transition-colors line-clamp-2 mb-3">
                    {{ translateItemField(article, 'title', $i18n.locale) }}
                </h2>
            </LocalizedLink>
            
            <div class="text-gray-600 text-sm line-clamp-3 mb-4 flex-1" v-html="translateItemField(article, 'summary', $i18n.locale)"></div>

            <div class="mt-auto pt-4 border-t border-gray-100 flex items-center justify-between text-sm">
                <div class="flex items-center text-gray-500">
                    <span class="mr-1">{{ $t('By') }}</span>
                    <span class="font-medium text-gray-900">{{ article.author || 'Admin' }}</span>
                </div>
                 <LocalizedLink :to="`/article/${translateItemField(article, 'slug', $i18n.locale)}`" class="text-blue-600 hover:text-blue-700 font-semibold flex items-center gap-1 group">
                    {{ $t('Read more') }}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform group-hover:translate-x-1 transition-transform" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l5 5a1 1 0 010 1.414l-5 5a1 1 0 11-1.414-1.414L13.586 10 10.293 6.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h10a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                    </svg>
                </LocalizedLink>
            </div>
        </div>
    </article>
</template>