<template>
    <nav :class="`navbar-${menuKey} navbar navbar-expand-lg navbar-light`">
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
    </nav>
    
</template>

<script>
import DropdownMenu from '@theme/storefront/templates/menu/DropdownMenu'
import MegaMenu from '@theme/storefront/templates/menu/MegaMenu'
import LinkMenu from '@theme/storefront/templates/menu/LinkMenu'
import { mapGetters, mapState } from 'vuex'
export default {
    data: () => ({
        loaded: false,
    }),
    components: { DropdownMenu, MegaMenu, LinkMenu },
    props: ['menuKey'],
    created() {
        this.$store.dispatch('menuDetails', this.menuKey).then(() => {
            this.loaded = true
        })
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