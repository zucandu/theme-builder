<template>
    <div id="slideshow" :class="`carousel slide px-0 ${loading ? `loading` : `loaded`}`" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div :class="`carousel-item position-relative cursor-pointer ${index === 0 ? 'active' : '__'}`" v-for="(banner, index) in slideshow" :key="index" @click.stop="goto(banner)">
                <img @load="imgload" :src="`/storage/${banner.image}`" :alt="banner.translations[0].title" class="img-fluid">
                <div class="position-absolute top-50 start-50 translate-middle text-success text-center col-10 col-lg-8">
                    <h1 class="slideshow__h1 display-3">{{ translation(banner, 'title', $i18n.locale) }}</h1>
                    <div class="d-none d-lg-block" v-if="translation(banner, 'summary', $i18n.locale)" v-html="translation(banner, 'summary', $i18n.locale)"></div>
                    <button class="mt-lg-4 mt-md-2 d-none d-lg-inline-block btn btn-success btn-lg rounded-0 fw-bold px-5 text-white shadow-lg">{{ $t('Get it now') }}</button>
                </div>
            </div>
            <template v-if="banners.length > 1">
                <button class="carousel-control-prev" type="button" data-bs-target="#slideshow" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">{{ $t('Previous') }}</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#slideshow" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">{{ $t('Next') }}</span>
                </button>
            </template>
        </div>
    </div>
</template>

<script>
import Carousel from 'bootstrap/js/dist/carousel';
import { mapGetters, mapState } from 'vuex';
export default {
    data: () => ({
        loading: true
    }),
    mounted() {
        let productImageCarousel = document.querySelector('#slideshow')
        new Carousel(productImageCarousel)
    },
    methods: {
        goto(banner) {
            const url = this.translation(banner, 'url_primary', this.$i18n.locale)
            if(url !== undefined) {
                this.$router.push(`/${url}`)
            }
        },
        imgload() {
            this.loading = false
        }
    },
    computed: {
        ...mapGetters(['translation']),
        ...mapState({
            banners: state => state.banner.banners
        }),
        slideshow() {
            return this.banners.filter(b => b.group === 'slideshow') || []
        }
    }
}
</script>