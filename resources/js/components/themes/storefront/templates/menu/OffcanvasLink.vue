<template>
    <template v-if="(item.children && item.children.length) || (item.blocks && item.blocks.length)">
        <a @click.stop="_toggle($event)" href="javascript:void(0);" :class="`text-decoration-none px-3 d-block ${item.heading === 1 ? `heading` : ``} ${ extraClass !== undefined ? extraClass : '' }`">
            {{ translation.title }}
        </a>
    </template>
    <template v-else>
        <router-link v-if="item.link !== `webaddress`" @click="closeOffcanvas" :to="url" :class="`text-decoration-none px-3 d-block ${item.heading === 1 ? `heading` : ``} ${ extraClass !== undefined ? extraClass : '' }`">
            {{ translation.title }}
        </router-link>
        <a v-else :href="translation.url" target="_blank" :class="`text-decoration-none px-3 d-block ${item.heading === 1 ? `heading` : ``} ${ extraClass !== undefined ? extraClass : '' }`">{{ translation.title }}</a>
    </template>
</template>

<script>
import { mapGetters } from 'vuex'
export default {
    props: ['item', 'extraClass'],
    methods: {
        _toggle(e) {
            e.target.classList.toggle('active')
            e.target.nextElementSibling.classList.toggle('show')
        },
        closeOffcanvas() {
            document.getElementById('btn-offcanvas-close').click()
        },
    },
    computed: {
        ...mapGetters(['transObj', 'trim']),
        translation() {
            return this.transObj(this.item, this.$i18n.locale)
        },
        url() {
            return ['page', 'product', 'banner'].includes(this.item.link) === false ? `/${this.item.link}/${this.translation.url}` : `/${_.trim(this.translation.url, '/')}`
        }
    }
}
</script>