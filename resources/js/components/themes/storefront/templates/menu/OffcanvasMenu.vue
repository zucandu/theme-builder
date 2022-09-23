<template>
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvas-menu" aria-labelledby="offcanvas-menu-label">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvas-menu-label">{{ $t('Shop by Department') }}</h5>
            <button id="btn-offcanvas-close" type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <nav :class="`navbar-${menuKey}`">
                <template v-if="loaded">
                    <ul v-if="__navigation" class="list-unstyled">
                        <li v-for="(item, index) in __navigation.items" :key="index">
                            <offcanvas-link :item="item" :extra-class="item.blocks && item.blocks.length > 0 ? `has-sub` : `no-sub`"></offcanvas-link>
                            <template v-if="item.blocks && item.blocks.length > 0">
                                <offcanvas-node-parent :items="item.blocks"></offcanvas-node-parent>
                            </template>
                        </li>
                    </ul>
                </template>
                <div v-else class="spinner-grow text-info" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </nav>
        </div>
    </div>
</template>

<script>
import Offcanvas from 'bootstrap/js/dist/offcanvas';
import OffcanvasLink from '@theme/storefront/templates/menu/OffcanvasLink'
import OffcanvasNodeParent from '@theme/storefront/templates/menu/OffcanvasNodeParent'
import { mapState, mapGetters } from 'vuex'
export default {
    data: () => ({
        loaded: false,
    }),
    props: ['menuKey'],
    components: { OffcanvasLink, OffcanvasNodeParent },
    created() {
        this.$store.dispatch('menuDetails', this.menuKey).then(() => {
            this.loaded = true
        })
    },
    mounted() {
        new Offcanvas(document.getElementById('offcanvas-menu'))
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