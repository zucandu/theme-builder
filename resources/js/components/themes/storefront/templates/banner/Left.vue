<template>
    <transition name="fade">
        <section v-if="banners && banners.length > 0" class="mb-3">
            <router-link v-for="banner in bannersLeft" :key="banner.id"  :to="`/${translation(banner, 'url_primary', $i18n.locale)}`">
                <img :src="`/storage/${banner.image}`" :alt="translation(banner, 'title', $i18n.locale)" @load="imgloaded" class="img-fluid img-loading">
            </router-link>
        </section>
    </transition>
    <div v-if="banners.length === 0" class="mb-3">
        <div v-for="i in 3" :key="i" class="p-5 bg-gray-200 rounded mb-3"><br><br><br></div>
    </div>
</template>


<script>
import { mapGetters, mapState } from 'vuex';
export default {
    methods: {
        imgloaded(e) {
            return e.target.classList.remove('img-loading')
        }
    },
    computed: {
        ...mapGetters(['translation']),
        ...mapState({
            banners: state => state.banner.banners
        }),
        bannersLeft() {
            return this.banners.filter(b => b.group === 'left') || []
        }
    }
}
</script>