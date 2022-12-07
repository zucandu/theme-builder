<template>
    <nav :class="`navbar-${menuKey} navbar navbar-expand-lg navbar-light py-0`" v-if="windowWidth >= 992">
        <div class="container-fluid px-0">
            <div v-if="loaded" class="collapse navbar-collapse">
                <ul v-if="__navigation" class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li :class="`nav-item ${item.blocks && item.blocks.length > 0 ? (item.blocks.length > 1 ? `dropdown has-megamenu` : 'dropdown') : ''}`" v-for="(item, index) in __navigation.items" :key="index">
                        <link-menu :item="item"></link-menu>
                        <template v-if="item.blocks && item.blocks.length > 0 && item.submenu === 'dropdown'">
                            <dropdown-menu :items="item.blocks[0].elements"></dropdown-menu>
                        </template>
                        <template v-if="item.blocks && item.blocks.length > 0 && item.submenu === 'megamenu'">
                            <mega-menu :items="item.blocks"></mega-menu>
                        </template>
                    </li>
                </ul>
            </div>
            <div v-else class="spinner-grow text-info" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </nav>
    <nav v-if="responsive" :class="`navbar-${menuKey} navbar navbar-expand-lg navbar-light py-0 showing`">
        <div class="container-fluid px-0 position-relative">
            <button class="btn navbar-toggler text-white border position-absolute" type="button" @click.stop="offcanvas.show()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-filter-left" viewBox="0 0 16 16">
                    <path d="M2 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5zm0-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5zm0-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5z"/>
                </svg>
            </button>
            <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvas-nav">
                <template v-if="windowWidth < 992">
                    <div class="offcanvas-header border-bottom">
                        <div class="offcanvas-title">
                            <img :src="`/storage/${storeConfig.fileuploader_store_logo}`" :width="storeConfig.store_logo_width" :height="storeConfig.store_logo_height" class="d-inline-block align-text-top">
                        </div>
                        <button id="closeNavOffcanvas" type="button" class="btn-close text-reset" @click.stop="offcanvas.hide()"></button>
                    </div>
                    <div class="offcanvas-body" v-if="loaded">
                        <template v-if="__navigation">
                            <offcanvas-menu :items="__navigation.items"></offcanvas-menu>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </nav>
    
</template>

<script>
import Offcanvas from 'bootstrap/js/dist/offcanvas';
import DropdownMenu from '@theme/storefront/templates/menu/DropdownMenu'
import MegaMenu from '@theme/storefront/templates/menu/MegaMenu'
import LinkMenu from '@theme/storefront/templates/menu/LinkMenu'
import OffcanvasMenu from '@theme/storefront/templates/menu/OffcanvasMenu'
import { mapGetters, mapState } from 'vuex'
export default {
    data: () => ({
        loaded: false,
        offcanvas: undefined,
        windowWidth: 0,
    }),
    components: {
        DropdownMenu, MegaMenu, LinkMenu, OffcanvasMenu
    },
    props: ['menuKey', 'responsive'],
    created() {
        this.$store.dispatch('menuDetails', this.menuKey).then(() => {
            this.loaded = true
        })

        this.resizeHandler()
        window.addEventListener("resize", this.resizeHandler)
    },
    mounted() {

        // Bootstrap offcanvas
        const offcanvasEl = document.getElementById('offcanvas-nav')
        if(offcanvasEl) {
            this.offcanvas = new Offcanvas(offcanvasEl)
        }
        
    },
    methods: {
        resizeHandler() {
            this.windowWidth = window.innerWidth
        }
    },
    computed: {
        ...mapState({
            menuDetails: state => state.menu.menuDetails,
            storeConfig: state => state.setting.storeConfig,
        }),
        ...mapGetters(['translation']),
        __navigation() {
            return this.menuDetails[this.menuKey] || undefined
        }
    }
}
</script>