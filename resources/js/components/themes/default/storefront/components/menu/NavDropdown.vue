<script setup>
import NavBlock from '@theme/storefront/components/menu/NavBlock.vue'
import NavDropdownSubmenu from '@theme/storefront/components/menu/NavDropdownSubmenu.vue'
import BaseLink from '@theme/storefront/components/menu/BaseLink.vue'

const props = defineProps({
    items: {
        type: Array,
        required: true
    }
})
</script>

<template>
    <ul class="dropdown-menu min-w-48 bg-white rounded shadow-lg border border-gray-100">
        <li
            v-for="el in items"
            :key="el.id"
            :class="`nav-item ${el.children.length > 0 ? 'dropdown has-sub' : 'nav-nosub'}`"
        >
            <BaseLink
                v-if="el.block_type === `link`"
                :item="el"
                extra-class="dropdown-item px-2 py-1 block text-gray-600 relative"
            />
			<NavBlock
                v-else
                :item="el"
                extra-class="dropdown-item"
            />
            <NavDropdownSubmenu
                v-if="el.children.length > 0"
                :items="el.children"
            />
        </li>
    </ul>
</template>
