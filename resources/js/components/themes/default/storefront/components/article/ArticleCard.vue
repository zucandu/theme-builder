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
    <article class="flex items-start">
        <!-- Image Section -->
        <div class="w-24 h-24 shrink-0 mr-4">
            <img v-if="article.images && article.images.length > 0" :src="`${zucConfig.store_url}/storage/${article.images[0]}`" :alt="translateItemField(article, 'title', $i18n.locale)" class="w-full h-full object-cover rounded" />
            <div v-else class="w-full h-full bg-gray-200 rounded"></div>
        </div>

        <!-- Content Section -->
        <div class="flex-1">
            <h2 class="text-lg font-semibold text-gray-800 hover:text-indigo-600">
                {{ translateItemField(article, 'title', $i18n.locale) }}
            </h2>
            <div
                class="text-sm text-gray-600 mt-2"
                v-html="translateItemField(article, 'summary', $i18n.locale)"
            ></div>
<!--
            <!-- Author -->
            <div class="text-sm text-gray-500 mt-2">
                <span class="font-semibold inline-block mr-2">{{ $t('Author') }}: </span>
                <LocalizedLink :to="`/blog/author/${article.author.toLowerCase().replace(/\s+/g, '').replace(/[^a-z0-9]/g, '')}`" class="text-blue-600 disabled-active">
                    {{ article.author }}
                </LocalizedLink>
            </div>

            <!-- Categories -->
            <div v-if="article.categories && article.categories.length > 0" class="text-sm text-gray-500 mt-2">
                <span class="font-semibold inline-block mr-2">{{ $t('Categories') }}:</span> 
                <LocalizedLink v-for="(category, index) in article.categories" :key="index" :to="`/blog/category/${translateItemField(category, 'slug', $i18n.locale)}`" class="text-blue-600 disabled-active">
                    {{ translateItemField(category, 'name', $i18n.locale) }}
                    <span v-if="index < article.categories.length - 1">,</span>
                </LocalizedLink>
            </div>
-->
            <LocalizedLink :to="`/article/${translateItemField(article, 'slug', $i18n.locale)}`" class="text-indigo-500 hover:underline mt-2 inline-block">
                {{ $t('Read more') }}
            </LocalizedLink>
        </div>
    </article>
</template>