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
    const raw = translation.value.url || ''
    const slug = raw.replace(/^\/|\/$/g, '') // remove leading/trailing slashes
    if (!['page', 'product', 'banner'].includes(props.item.link)) {
        return `/${props.item.link}/${slug}`
    }
    return `/${slug}`
});

const linkClass = computed(() => {
    return [
        `base-link cursor-pointer`,
        props.item.heading === 1 ? 'heading' : '',
        props.extraClass,
        props.item.blocks && props.item.blocks.length > 0 ? 'dropdown-toggle' : ''
    ].join(' ').trim();
});

const showNavbar = () => {
	const navPrimary = document.querySelector('.navbar-primary');
	if(navPrimary) {
		navPrimary.classList.add("click-to-hide");
	}
}
const hideNavbar = () => {
	const navPrimary = document.querySelector('.navbar-primary')
	if(navPrimary) {
		navPrimary.classList.remove("click-to-hide");
	}
}

</script>

<template>
	<!-- Readonly -->
    <span
		@mouseover="showNavbar"
        v-if="item.link === 'readonly'"
        :class="linkClass"
    >
        {{ translation.title }}
    </span>

    <!-- Internal Link (router-link) -->
    <LocalizedLink
		@mouseover="showNavbar"
		@click="hideNavbar"
        v-else-if="item.link !== 'webaddress'"
        :to="url"
        :class="linkClass"
    >
        {{ translation.title }}
    </LocalizedLink>

    <!-- External Link -->
    <a
        v-else
        :href="translation.url"
        target="_blank"
        rel="noopener noreferrer"
        :class="linkClass"
    >
        {{ translation.title }}
    </a>
</template>