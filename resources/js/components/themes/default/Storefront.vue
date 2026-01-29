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

<style>
/* Global Resets & Typography */
@import url('https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap');

* {
	font-family: "Lato", sans-serif;
	font-weight: 400;
	font-style: normal;
}

strong u {
	font-weight: bold;
}

.hide-cart-btn .cart-form {
	display: none;
}

.list-disc ul {
	list-style: disc;
    margin-left: 2rem;
    line-height: 28px;
	margin-bottom: 1rem;
}

.invalid {
    color: #f87171; /* Tailwind red-500 */
    opacity: 0.5; /* Dim appearance */
    pointer-events: none; /* Disable interactions */
}

.outofstock {
    color: #9ca3af; /* Tailwind gray-400 */
    opacity: 0.7; /* Slightly dim appearance */
    cursor: not-allowed; /* Change cursor to "not allowed" */
    background-color: rgb(249 250 251)!important;
}

p, figure.image, h3, h4, figure.media {
    margin-bottom: 1rem;
}
h3 {
	font-size: 1.75rem;
}
h4 {
	font-size: 1.5rem;
}
b, strong {
    font-weight: bolder;
}
figure.media {
    overflow: hidden;
    padding-top: 56.25%;
    position: relative;
    width: 100%;
}
.media iframe {
    bottom: 0;
    height: 100%;
    left: 0;
    position: absolute;
    right: 0;
    top: 0;
    width: 100%;
}
.font-bold {
	font-weight: 700;
}

.select-bg {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
    background-size: 16px 12px;
    background-position: right 0.75rem center;
    background-repeat: no-repeat;
}

/* Dropdown Menu Submenu */
.dropdown-menu.submenu {
	display: none;
}

/* Product Card Tooltip */
.product-card-tooltip ul {
	list-style-type: disc;
    padding-left: 20px;
    margin: 0;
}

/* Filter Sidebar */
@media (min-width: 1024px) {
    .filter-hide {
        display: none;
    }
}

@media (max-width: 1023.98px) {
    #filter-sidebar {
        position: fixed !important;
        top: 0;
        right: 0;
        z-index: 9999;
        width: 50%;
        height: 100%;
        background: #fff;
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
        padding: 20px;
        overflow-y: auto;
        transform: translateX(100%);
        transition: transform 0.3s ease-in-out;
    }

    #col-filter.filter-show #filter-sidebar {
        transform: translateX(0);
    }
	
	#col-filter.filter-show #filter-overlay {
        display: block;
    }
	
	#filter-overlay {
        display: none;
        z-index: 9993;
    }
	.checkout-auth-no .right-column {
		display:none;
	}
	.schematic-dialog .product-card-tooltip {
		left: -180px !important;
	}
}

@media (max-width: 575.98px) {
    #filter-sidebar {
        width: 75%;
    }
}

/* Embla Carousel */
.embla {
	overflow: hidden;
}
.embla__container {
	display: flex;
}
.embla__slide {
	flex: 0 0 100%;
	min-width: 0;
}

.embla__dot--selected {
	border-color: #364153;
}

/* Menu Block */
.menu-block-36 {
    background: #fff url("/storage/PODCASTS.jpg");
    height: 2.5rem;
    padding: .5rem !important;
}

/* Blog Sidebar */
.blog-sidebar-menu-section .h3, .blog-sidebar-menu-section .content  {
	padding: .5rem;
}

/* Blog Top Section */
.blog-top-section,
[class^="custom_schematic_"] {
    display: block;
}

.blog-top-section .h3,
[class^="custom_schematic_"] .h3 {
    display: none;
}

.blog-top-section .content,
[class^="custom_schematic_"] .content {
    margin-bottom: 0;
}

.blog-top-section .content div,
[class^="custom_schematic_"] .content div {
    display: inline-block;
}

.blog-top-section .content a,
[class^="custom_schematic_"] .content a {
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
    display: inline-block;
    transition: all 0.2s ease;
    margin-right: 16px;
}

.blog-top-section .content a:hover,
.blog-top-section .content a.router-link-exact-active,
[class^="custom_schematic_"] .content a:hover,
[class^="custom_schematic_"] .content a.router-link-exact-active {
    color: #2563EB;
}

/* Product Description */
.prodDescH1 {
    font-weight: 800;
    font-size: 1.6rem;
    margin-bottom: 1rem!important;
}

.prodDescH2 {
    font-weight: 800;
    font-size: 1.3rem;
    margin-top: 1rem;
}

.account-nav .router-link-active {
    background-color: #2c3e50 !important;
    color: #fff !important;
}

.product-embed iframe {
	width:100%;
	height:100%;
}

.product-unavailable, .count-1 {
	display: none;
}

.braintree-heading {
	display:none;
}

[data-braintree-id="save-card-field-group"] {
    margin-top: 20px !important;
    padding-left: 5px;
}

[data-braintree-id="save-card-field-group"] .braintree-form__label {
    margin-left: 10px !important;
    cursor: pointer;
    font-size: 14px;
}

[data-braintree-id="save-card-field-group"] input[type="checkbox"] {
    transform: scale(1.2);
    cursor: pointer;
}
</style>