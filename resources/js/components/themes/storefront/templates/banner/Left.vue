<template>
    <transition name="fade">
        <div class="container">
            <section v-if="banners && banners.length > 0" class="row">
                <div v-for="banner in bannersLeft" :key="banner.id" class="col-12 mb-3">
                    <router-link :to="`/${translation(banner, 'url_primary', $i18n.locale)}`">
                        <img :src="`/storage/${banner.image}`" :alt="translation(banner, 'title', $i18n.locale)" @load="imgloaded" class="img-fluid img-loading">
                    </router-link>
                </div>
            </section>
        </div>
    </transition>
    <div v-if="banners.length === 0" class="container">
        <div class="row mb-3">
            <div v-for="i in 3" :key="i" class="col-12">
                <div class="p-5 bg-gray-200 rounded mb-3"><br><br><br></div>
            </div>
        </div>
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