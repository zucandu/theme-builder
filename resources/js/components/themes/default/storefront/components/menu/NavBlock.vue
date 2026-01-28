<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

import { useHelpers } from '@/composables/useHelpers';

const { locale } = useI18n();
const { translateItemObj } = useHelpers();

const props = defineProps({
    item: { type: Object, required: true },
    extraClass: { type: String, default: '' }
})

const translation = computed(() => {
	return translateItemObj(props.item, locale.value);
});

const url = computed(() => {
    const cleanUrl = translation.value.url.replace(/^\/+|\/+$/g, '')
    return !['page', 'product', 'banner'].includes(props.item.link)
        ? `/${props.item.link}/${cleanUrl}`
        : `/${cleanUrl}`
});

</script>

<template>
    <section :class="`menu-element menu-element-${item.id} menu-${item.link}-element p-4 border m-2`">
        <figure v-if="item.extra_data && item.extra_data.image" class="mb-2">
            <LocalizedLink
                :class="`inline-block px-2 py-1 text-gray-700 hover:text-blue-600 transition ${extraClass ?? ''}`"
                :to="url"
            >
                <img
                    v-if="item.link === 'banner'"
                    :src="`/storage/${item.extra_data.image}`"
                    :alt="translation.title"
                    class="w-full h-auto object-contain rounded"
                />
                <img
                    v-else
                    :src="`/storage/${zucConfig.medium_image_size}/${item.extra_data.image}`"
                    :alt="translation.title"
                    class="w-full h-auto object-contain rounded"
                />
            </LocalizedLink>
        </figure>

        <h5 class="menu-element__content font-semibold mb-2">
            <LocalizedLink
                :class="`inline-block px-2 py-1 text-gray-700 hover:text-blue-600 transition ${extraClass ?? ''}`"
                :to="url"
            >
                {{ translation.title }}
            </LocalizedLink>
        </h5>
    </section>
</template>
