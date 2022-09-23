<template>
    <nav :class="`navbar navbar-expand-lg navbar-${menuKey} py-lg-0`">
        <template v-if="loaded === true">
            <ul v-if="__navigation" class="navbar-nav list-unstyled">
                <li :class="`nav-item ${item.blocks && item.blocks.length > 0 ? (item.blocks.length > 1 ? `dropdown has-megamenu` : 'dropdown') : ''}`" v-for="(item, index) in __navigation.items" :key="index">
                    <link-menu :item="item"></link-menu>
                </li>
            </ul>
        </template>
        <div v-else>____</div>
    </nav>
    
</template>

<script>
import LinkMenu from '@theme/storefront/templates/menu/LinkMenu'
import { mapGetters, mapState } from 'vuex'
export default {
    data: () => ({
        loaded: false,
    }),
    props: ['menuKey'],
    components: { LinkMenu },
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