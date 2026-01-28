<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

import { useHelpers } from '@/composables/useHelpers';

const props = defineProps({
    item: { type: Object, required: true },
    extraClass: { type: String, default: '' }
})

const { locale } = useI18n();
const { translateItemObj } = useHelpers();


const translation = computed(() => {
	return translateItemObj(props.item, locale.value);
});

const url = computed(() => {
	const cleanUrl = translation.value.url.replace(/^\/+|\/+$/g, '')
	return !['page', 'product', 'banner'].includes(props.item.link)
		? `/${props.item.link}/${cleanUrl}`
		: `/${cleanUrl}`
});

const closeMmenu = () => {
	const btnMmenu = document.getElementById('close-mmenu')
	if(btnMmenu) btnMmenu.click();
}
</script>

<template>
    <template v-if="item.link === 'readonly'">
        <span class="text-gray-600 cursor-default">{{ translation.title }}</span>
    </template>
    <template v-else>
        <LocalizedLink
            v-if="item.link !== 'webaddress'"
            :to="url"
            @click="closeMmenu"
            class="text-gray-600 hover:underline hover:text-gray-800"
        >
            {{ translation.title }}
        </LocalizedLink>
        <a
            v-else
            :href="translation.url"
            target="_blank"
            class="text-gray-600 hover:underline hover:text-gray-800"
        >
            {{ translation.title }}
        </a>
    </template>
</template>
