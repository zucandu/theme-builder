<template>
    <div v-if="item.icon" class="block-element__icon">
        <img :src="`/storage/${item.icon}`" width="40" height="40" :alt="translation.title">
    </div>
    <div v-if="item.extra_data.image" class="block-element__img">
        <router-link v-if="item.extra_data.no_link !== 1" :to="url">
            <img v-if="imgType === `original`" :src="`/storage/${item.extra_data.image}`" :alt="translation.title" class="img-fluid">
            <img v-else :src="`/storage/${imgSize}/${item.extra_data.image}`" :alt="translation.title" class="img-fluid">
        </router-link>
        <template v-else>
            <img v-if="imgType === `original`" :src="`/storage/${item.extra_data.image}`" :alt="translation.title" class="img-fluid">
            <img v-else :src="`/storage/${imgSize}/${item.extra_data.image}`" :alt="translation.title" class="img-fluid">
        </template>
    </div>
    <div :class="`block-element__content index-${index}`">
        <div :class="`title ${item.heading === 1 ? 'heading' : ''}`">
            <router-link v-if="item.extra_data.no_link !== 1" :to="url">{{ translation.title }}</router-link>
            <template v-else>{{ translation.title }}</template>
        </div>
        <div v-if="translation.content" v-html="translation.content" class="text"></div>
    </div>
</template>

<script>
import { mapGetters } from 'vuex'
export default {
    props: ['item', 'index', 'imgSize', 'imgType'],
    computed: {
        ...mapGetters(['transObj']),
        translation() {
            return this.transObj(this.item, this.$i18n.locale)
        },
        url() {
            return ['page', 'product', 'banner'].includes(this.item.link) === false ? `/${this.item.link}/${this.translation.url}` : `/${_.trim(this.translation.url, '/')}`
        }

    }
}
</script>