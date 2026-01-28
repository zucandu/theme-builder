<!-- Your theme CSS if you want to customize the Tailwindcss  -->
<style> @import '@theme/storefront/css/style.css';</style>

<script setup>
import LayoutHeader from '@theme/storefront/components/layout/Header.vue'
import LayoutFooter from '@theme/storefront/components/layout/Footer.vue'

import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { onBeforeRouteUpdate } from 'vue-router';

const { locale } = useI18n();

import { useSettingsStore } from '@/stores/settings';
import { useAuthCustomerStore } from '@/stores/auth/customer';
import { useHelpers } from '@/composables/useHelpers';

const settingsStore = useSettingsStore();
const authCustomerStore = useAuthCustomerStore();
const { translateItemObj } = useHelpers();

const loaded = ref(false);

onMounted(async () => {
    await settingsStore.fetchSettings();
    await authCustomerStore.fetchCustomerInfo().finally(() => loaded.value = true);
});

onBeforeRouteUpdate((to, from, next) =>  {
	const meta = settingsStore.getMetatagsByName(to.name);
	if(meta)	document.title = translateItemObj(meta, locale.value)?.meta_title;
	next();
})
</script>

<template>
    <main :id="`main-${$route.name}`" class="main bg-white">
        <template v-if="loaded">
            <layout-header></layout-header>
            <div class="main-content lg:min-h-[calc(100vh-412px)] py-4">
                <div class="container mx-auto">
                    <router-view></router-view>
                </div>
            </div>
            <layout-footer></layout-footer>
        </template>
        <div v-else class="flex items-center justify-center min-h-screen">
            <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-blue-500"></div>
            <p class="mt-4 text-gray-600 ps-4">{{ $t('Loading, please wait...') }}</p>
        </div>
    </main>
</template>