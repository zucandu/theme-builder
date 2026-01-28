<script setup>
import { computed } from 'vue'

import MmenuLink from '@theme/storefront/components/menu/MmenuLink.vue'
import MmenuSubmenu from '@theme/storefront/components/menu/MmenuSubmenu.vue'

import { useHelpers } from '@/composables/useHelpers';

const props = defineProps({
    items: {
        type: Array,
        required: true
    }
});
const { translateItemField } = useHelpers();

</script>

<template>
    <ul class="ml-4 mt-2 space-y-1">
        <li v-for="el in items" :key="el.id">
            <template v-if="el.children.length > 0">
                <span class="block text-gray-600">
                    {{ translateItemField(el, 'title', $i18n.locale) }}
                </span>
                <MmenuSubmenu :items="el.children" />
            </template>
            <template v-else>
                <MmenuLink :item="el" />
            </template>
        </li>
    </ul>
</template>