<template>
    <ul v-for="block in items" :key="block.id" class="offcanvas__node-parent">
        <li v-for="item in block.elements" :key="item.id">
            <offcanvas-link :item="item" :extra-class="item.children && item.children.length > 0 ? `has-sub` : `no-sub`"></offcanvas-link>
            <template v-if="item.children && item.children.length > 0">
                <offcanvas-node-children :items="item.children"></offcanvas-node-children>
            </template>
        </li>
    </ul>
</template>

<script>
import OffcanvasLink from '@theme/storefront/templates/menu/OffcanvasLink'
import OffcanvasNodeChildren from '@theme/storefront/templates/menu/OffcanvasNodeChildren'
export default {
    props: ['items'],
    components: { OffcanvasLink, OffcanvasNodeChildren },
}
</script>