<template>
    <header>
        <banner-slideshow v-if="$route.name === `index`"></banner-slideshow>
        <div class="z-home-nav container-fluid px-md-5">
            <div class="row align-items-center mx-0">
                <div class="col-xl-2 col-6 order-0">
                    <router-link to="/">
                        <img :src="`/storage/${storeConfig.fileuploader_store_logo}`" :width="storeConfig.store_logo_width" :height="storeConfig.store_logo_height" :alt="storeConfig.store_name" @load="imgloaded" class="img-loading img-fluid">
                    </router-link>
                </div>
                <div class="col-xl-8 col-12 order-xl-2 order-3">
                    <display-menu menu-key="primary" :responsive="true"></display-menu>
                </div>
                <div class="col-xl-2 col-6 order-xl-3 order-2 navbar-cart-account text-end">
                    <button class="btn btn-sm text-white" data-bs-toggle="modal" data-bs-target="#search-modal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                        </svg>
                    </button>
                    <router-link v-if="isCustomerLogged" to="/account" class="text-white text-decoration-none ms-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                            <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/>
                        </svg>
                    </router-link>
                    <router-link v-else to="/register" class="text-white text-decoration-none ms-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                            <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/>
                        </svg>
                    </router-link>
                    <div class="d-inline-block position-relative ms-3">
                        <router-link to="/cart" class="text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-handbag" viewBox="0 0 16 16">
                                <path d="M8 1a2 2 0 0 1 2 2v2H6V3a2 2 0 0 1 2-2zm3 4V3a3 3 0 1 0-6 0v2H3.36a1.5 1.5 0 0 0-1.483 1.277L.85 13.13A2.5 2.5 0 0 0 3.322 16h9.355a2.5 2.5 0 0 0 2.473-2.87l-1.028-6.853A1.5 1.5 0 0 0 12.64 5H11zm-1 1v1.5a.5.5 0 0 0 1 0V6h1.639a.5.5 0 0 1 .494.426l1.028 6.851A1.5 1.5 0 0 1 12.678 15H3.322a1.5 1.5 0 0 1-1.483-1.723l1.028-6.851A.5.5 0 0 1 3.36 6H5v1.5a.5.5 0 1 0 1 0V6h4z"/>
                            </svg>
                        </router-link>
                        <span class="position-absolute z-mobile-cart-bag bg-success badge rounded-circle">{{ cartNumberOfItems }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="search-modal" tabindex="-1" aria-labelledby="search-modal-label" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- <div class="modal-header">
                        <h1 class="modal-title fs-5" id="search-modal-label">Modal title</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div> -->
                    <div class="modal-body">
                        sdf
                    </div>
                </div>
            </div>
        </div>

    </header>
</template>

<script>
import Dropdown from 'bootstrap/js/dist/dropdown';
import BannerSlideshow from '@theme/storefront/templates/banner/Slideshow'
import DisplayMenu from '@theme/storefront/templates/menu/DisplayMenu'
import SearchForm from '@theme/storefront/templates/header/SearchForm'
import { mapState, mapGetters } from 'vuex'
export default {
    data: () => ({
        selectLanguage: undefined,
        selectCurrency: undefined,
        searchFormStatus: false
    }),
    components: { BannerSlideshow, DisplayMenu, SearchForm },
    mounted() {
        const dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'))
        dropdownElementList.map(function (dropdownToggleEl) {
            new Dropdown(dropdownToggleEl)
        })
    },
    methods: {
        imgloaded(e) {
            return e.target.classList.remove('img-loading')
        }
    },
    computed: {
        ...mapGetters(['selectedLanguage', 'selectedCurrency', 'isCustomerLogged', 'cartNumberOfItems']),
        ...mapState({
            storeConfig: state => state.setting.storeConfig,
            languages: state => state.setting.languages,
            language: state => state.setting.language,
            currencies: state => state.setting.currencies,
            profile: state => state.customer.profile,
        })
    },
    watch: {
        selectedLanguage(v) {
            if(Object.keys(v).length > 0) {
                this.selectLanguage = v.iso_code
            }
        },
        selectLanguage(v) {
            if(v !== undefined) {
                this.$store.dispatch('selectLanguage', v).then(() => this.$i18n.locale = this.language)
            }
        },
        selectedCurrency(v) {
            this.selectCurrency = v.code
        },
        selectCurrency(v) {
            if(v !== undefined) {
                this.$store.commit('setCurrency', { currency: v })
            }
        }
    }
}
</script>