<script setup>
import { computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useSettingsStore } from '@/stores/settings';

const route = useRoute();
const { locale } = useI18n();
const settingsStore = useSettingsStore();

// Get meta tags and translations based on the current route and locale
let metaTags = settingsStore.getMetatagsByName(route.name);
if(!metaTags) {
    const parentRoute = route.matched.length > 1 ? route.matched[route.matched.length - 2] : null;
    metaTags = settingsStore.getMetatagsByName(parentRoute.name);
}
const translation = settingsStore.transObj(metaTags, locale.value);

const props = defineProps({
    title: {
        type: String,
        required: false,
    },
    description: {
        type: String,
        required: false,
    },
});

// Fallback logic for title and description
const finalTitle = computed(() => props.title || translation?.meta_title || zucConfig.store_name);
const finalDescription = computed(() => props.description || translation?.meta_description || zucConfig.store_name);

// Set meta tags on mount
onMounted(() => {
    document.title = finalTitle.value;
    let metaDescription = document.querySelector('meta[name="description"]');
    if (!metaDescription) {
        metaDescription = document.createElement('meta');
        metaDescription.name = 'description';
        document.head.appendChild(metaDescription);
    }
    metaDescription.content = finalDescription.value;
});
</script>

<template>
    <div></div>
</template>
