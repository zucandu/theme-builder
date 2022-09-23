<template>
    <ul class="dropdown-menu submenu">
        <li :class="`nav-item ${el.children.length > 0 ? 'dropdown has-sub' : 'nav-nosub'}`" v-for="el in items" :key="el.id">
            <link-menu v-if="el.block_type === 'link'" :item="el" extra-class="dropdown-item"></link-menu>
            <block-menu v-else :item="el" extra-class="dropdown-item"></block-menu>
            <dropdown-submenu :items="el.children"></dropdown-submenu>
        </li>
    </ul>
</template>

<script>
import LinkMenu from '@theme/storefront/templates/menu/LinkMenu'
import BlockMenu from '@theme/storefront/templates/menu/BlockMenu'
export default {
    name: 'dropdown-submenu',
    props: ['items'],
    components: {
        LinkMenu, BlockMenu
    }
}
</script>