<template>
    <ul class="dropdown-menu">
        <li :class="`nav-item ${el.children.length > 0 ? 'dropdown has-sub' : 'nav-nosub'}`" v-for="el in items" :key="el.id">
            <link-menu v-if="el.block_type === 'link'" :item="el" extra-class="dropdown-item"></link-menu>
            <block-menu v-else :item="el" extra-class="dropdown-item"></block-menu>
            <template  v-if="el.children.length > 0">
                <dropdown-submenu :items="el.children"></dropdown-submenu>
            </template>
        </li>
    </ul>
</template>

<script>
import LinkMenu from '@theme/storefront/templates/menu/LinkMenu'
import BlockMenu from '@theme/storefront/templates/menu/BlockMenu'
import DropdownSubmenu from '@theme/storefront/templates/menu/DropdownSubmenu'
export default {
    components: {
        LinkMenu, BlockMenu, DropdownSubmenu
    },
    props: ['items'],
}
</script>