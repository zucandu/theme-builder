<template>
    <div class="dropdown-menu megamenu">
        <div class="row">
            <div :class="`col col-md-${items.length === 1 ? `12` : block.block_width}`" v-for="block in items" :key="block.id">
                <template v-for="(el, index) in block.elements" :key="el.id">
                    <div :class="`text-primary${ index === 0 ? ' link-first' : '' }${ el.heading === 1 ? ' link-heading fw-bold' : '' }`">
                        <link-menu v-if="el.block_type === 'link'" :item="el"></link-menu>
                        <block-menu v-else :item="el"></block-menu>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>

<script>
import LinkMenu from '@theme/storefront/templates/menu/LinkMenu'
import BlockMenu from '@theme/storefront/templates/menu/BlockMenu'
export default {
    components: {
        LinkMenu, BlockMenu
    },
    props: ['items']
}
</script>