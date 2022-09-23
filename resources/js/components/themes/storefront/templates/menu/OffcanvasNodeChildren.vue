<template>
    <ul class="offcanvas__node-children">
        <li v-for="item in items" :key="item.id">
            <offcanvas-link :item="item" :extra-class="item.children && item.children.length > 0 ? `has-sub` : `no-sub`"></offcanvas-link>
            <template v-if="item.children && item.children.length > 0">
                <offcanvas-node-children :items="item.children"></offcanvas-node-children>
            </template>
        </li>
    </ul>
</template>

<script>
import OffcanvasLink from '@theme/storefront/templates/menu/OffcanvasLink'
export default {
    props: ['items'],
    components: { OffcanvasLink },
    name: 'offcanvas-node-children'
}
</script>