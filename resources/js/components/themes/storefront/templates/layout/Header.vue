<template>
    <header>
        <div class="header__top bg-secondary py-2 text-center border-bottom text-white d-none d-md-block">
            <block-element menu-key="tertiary" :block-loading="2"></block-element>
        </div>
        <div class="header__main mb-5">
            <div class="container position-relative">
                <div class="row g-3 my-3 align-items-center">
                    <div class="col-lg-2 col-md-5">
                        <router-link class="navbar-brand flex-shrink-0" to="/">
                            <img :src="`/storage/${storeConfig.fileuploader_store_logo}`" :width="storeConfig.store_logo_width" :height="storeConfig.store_logo_height" :alt="storeConfig.store_name" @load="imgloaded" class="img-loading img-fluid">
                        </router-link>
                    </div>
                    <div class="col-lg-7 d-lg-block d-none">
                        <display-menu menu-key="secondary"></display-menu>
                    </div>
                    <div class="col-lg-3 col-md-7 d-md-flex d-none align-items-center justify-content-end">
                        <router-link v-if="isCustomerLogged" to="/account" class="z-nav-link text-decoration-none d-md-inline d-none">{{ $t('My account') }}</router-link>
                        <router-link v-else to="/register" class="z-nav-link text-decoration-none d-md-inline d-none">{{ $t('Register') }}</router-link>
                        <router-link v-if="isCustomerLogged" to="/logout" class="z-nav-link ms-3 text-decoration-none d-md-inline d-none">{{ $t('Log out') }}</router-link>
                        <router-link v-else to="/login" class="z-nav-link ms-3 text-decoration-none d-md-inline d-none">{{ $t('Login') }}</router-link>
                        <router-link to="/account" class="btn d-md-none me-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                                <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/>
                            </svg>
                        </router-link>
                        <div class="position-relative d-inline d-md-none">
                            <router-link to="/cart">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-handbag" viewBox="0 0 16 16">
                                    <path d="M8 1a2 2 0 0 1 2 2v2H6V3a2 2 0 0 1 2-2zm3 4V3a3 3 0 1 0-6 0v2H3.36a1.5 1.5 0 0 0-1.483 1.277L.85 13.13A2.5 2.5 0 0 0 3.322 16h9.355a2.5 2.5 0 0 0 2.473-2.87l-1.028-6.853A1.5 1.5 0 0 0 12.64 5H11zm-1 1v1.5a.5.5 0 0 0 1 0V6h1.639a.5.5 0 0 1 .494.426l1.028 6.851A1.5 1.5 0 0 1 12.678 15H3.322a1.5 1.5 0 0 1-1.483-1.723l1.028-6.851A.5.5 0 0 1 3.36 6H5v1.5a.5.5 0 1 0 1 0V6h4z"/>
                                </svg>
                            </router-link>
                            <span class="position-absolute z-mobile-cart-bag bg-success badge rounded-circle">{{ cartNumberOfItems }}</span>
                        </div>
                        <template v-if="(languages && languages.length > 0) && (currencies && currencies.length > 0)">
                            <div v-if="languages.length > 1" class="btn-group d-md-inline-flex d-none">
                                <a href="#" class="z-nav-link dropdown-toggle text-decoration-none ms-3" data-bs-toggle="dropdown" aria-expanded="false">{{ languages.find(l => l.iso_code === language).name }}</a>
                                <ul class="dropdown-menu">
                                    <li v-for="(lang, index) in languages" :key="index"><a @click="changeLanguage(lang.iso_code)" href="#" class="z-nav-link dropdown-item">{{ lang.name }}</a></li>
                                </ul>
                            </div>
                            <div v-if="currencies.length > 1 && $route.name !== `checkout`" class="btn-group d-md-inline-flex d-none">
                                <a href="#" class="z-nav-link dropdown-toggle text-decoration-none ms-3" data-bs-toggle="dropdown" aria-expanded="false">{{ currencies.find(c => c.code === currency).name }}</a>
                                <ul class="dropdown-menu">
                                    <li v-for="(curr, index) in currencies" :key="index"><a @click="changeCurrency(curr.code)" href="#" class="z-nav-link dropdown-item">{{ curr.name }}</a></li>
                                </ul>
                            </div>
                        </template>
                        <div v-else class="btn-group d-md-inline-flex d-none">
                            <span class="d-inline-block bg-light px-4 py-2 opacity-50 rounded ms-3"></span>
                        </div>
                    </div>
                </div>
                <div class="row g-3 mt-3">
                    <div class="col-12 d-flex align-items-center">
                        <button id="z-button-hamburger" class="btn btn-success shadow-sm text-white d-flex align-items-center" data-bs-toggle="offcanvas" data-bs-target="#offcanvas-menu" aria-controls="offcanvas-menu">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-list me-0 me-lg-3" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/>
                            </svg>
                            <span class="d-none d-lg-inline">{{ $t('Shop by Department') }}</span>
                        </button>
                        <div class="flex-fill ms-3 me-0 me-md-3">
                            <header-search-form></header-search-form>
                        </div>
                        <cart-modal></cart-modal>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Menu -->
        <offcanvas-menu menu-key="primary"></offcanvas-menu>
        
    </header>
</template>

<script>
import Dropdown from 'bootstrap/js/dist/dropdown';
import HeaderSearchForm from '@theme/storefront/templates/header/SearchForm'
import CartModal from '@theme/storefront/templates/header/CartModal'
import DisplayMenu from '@theme/storefront/templates/menu/DisplayMenu'
import OffcanvasMenu from '@theme/storefront/templates/menu/OffcanvasMenu'
import BlockElement from '@theme/storefront/templates/menu/BlockElement'
import { mapState, mapGetters } from 'vuex'
export default {
    components: { HeaderSearchForm, DisplayMenu, CartModal, OffcanvasMenu, BlockElement },
    mounted() {
        const dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'))
        dropdownElementList.map(function (dropdownToggleEl) {
            new Dropdown(dropdownToggleEl)
        })
    },
    methods: {
        imgloaded(e) {
            return e.target.classList.remove('img-loading')
        },
		changeLanguage(isoCode) {
			this.$store.dispatch('selectLanguage', isoCode).then(() => this.$i18n.locale = this.language)
		},
		changeCurrency(code) {
			this.$store.commit('setCurrency', { currency: code })
		}
    },
    computed: {
        ...mapGetters(['isCustomerLogged', 'cartNumberOfItems']),
        ...mapState({
            storeConfig: state => state.setting.storeConfig,
            languages: state => state.setting.languages,
            language: state => state.setting.language,
            currencies: state => state.setting.currencies,
			currency: state => state.setting.currency,
            profile: state => state.customer.profile,
        })
    }
}
</script>
