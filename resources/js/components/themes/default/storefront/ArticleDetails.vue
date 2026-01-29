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
    <div>
        <MetaTags v-if="loaded" :title="articleComputed.translation?.meta_title" :description="articleComputed.translation?.meta_description" />
        <div class="py-12">
            <div class="container mx-auto py-6">
                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-8">
                        <div v-if="loaded">
                            
                            <!-- Article Image -->
                            <img v-if="article.images && article.images.length > 0" :src="`${zucConfig.store_url}/storage/${article.images[0]}`" :alt="articleComputed.translation.title" class="w-full h-64 object-cover" />

                            <!-- Article Content -->
                            <div>

                                <!-- Title -->
                                <h1 class="text-2xl font-bold text-gray-800 mb-4">{{ articleComputed.translation.title }}</h1>

                                <!-- Author and Date -->
                                <div class="flex justify-between items-center space-x-4 text-sm text-gray-500 mb-6">
                                    <div>
                                        <span>{{ $t('By') }} {{ article.author }}</span>
                                        <span class="mx-2">&bull;</span>
                                        <span>{{ article.date_added }}</span>
                                    </div>
                                    <div>
                                        <div v-if="article.categories?.length > 0">
                                            <LocalizedLink v-for="category in article.categories" :key="category" :to="`/blog/category/${translateItemField(category, 'slug', $i18n.locale)}`" class="mr-4 hover:underline">{{ translateItemField(category, 'name', $i18n.locale) }}</LocalizedLink>
                                        </div>
                                    </div>
                                </div>

                                <!-- Content -->
                                <div class="text-gray-700 leading-relaxed" v-html="articleComputed.translation.content"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-4">
                        <SearchForm />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>