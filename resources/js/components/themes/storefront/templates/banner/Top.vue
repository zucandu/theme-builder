<template>
    <transition name="fade">
        <section v-if="banners && banners.length > 0" class="container mt-lg-5 mt-3">
            <div class="row">
                <div v-for="banner in bannersTop" :key="banner.id" class="col-md-4 col-12 mb-md-0 mb-3">
                    <router-link :to="`/${translation(banner, 'url_primary', $i18n.locale)}`">
                        <img :src="`/storage/${banner.image}`" :alt="translation(banner, 'title', $i18n.locale)" @load="imgloaded" class="img-fluid img-loading">
                    </router-link>
                </div>
            </div>
        </section>
    </transition>
    <div v-if="banners.length === 0" class="container mt-lg-5 mt-3">
        <div class="row g-3">
            <div v-for="i in 3" :key="i" class="col-md-4 col-12 mb-md-0 mb-3">
                <div class="p-5 bg-gray-200 rounded"><br><br></div>
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
        bannersTop() {
            return this.banners.filter(b => b.group === 'top') || []
        }
    }
}
</script>