<template>
    <div v-if="images" class="row justify-content-center g-3">
        <div class="col-md-3 text-center order-md-0 order-1">
            <div class="thumbnails">
                <div v-for="(img, index) in images" :key="index" :class="`thumb thumb-${index} mb-md-3 mx-md-0 mx-2 cursor-pointer d-md-block d-inline-block`">
                    <img @click.stop="moveTo(index)" @load="imgloaded" :src="`/storage/${storeConfig.small_image_size}/${img.src}`" :width="storeConfig.small_image_size" :height="storeConfig.small_image_size" :alt="productName" class="img-loading img-fluid">
                </div>
            </div>
        </div>
        <div class="col-md-9 text-center order-md-1 order-0 position-relative">
            <div id="product-image-carousel" class="carousel slide">
                <div class="carousel-inner text-center">
                    <div :class="`carousel-item ${+index === 0 ? 'active' : ''}`" v-for="(img, index) in images" :key="index">
                        <img @click="openImgPopup" @load="imgloaded" :src="`/storage/${storeConfig.large_image_size}/${img.src}`" :width="storeConfig.large_image_size" :height="storeConfig.large_image_size" :alt="productName" class="img-loading img-fluid cursor-pointer"  data-bs-toggle="modal" data-bs-target="#img-popup">
                    </div>
                </div>
                <div v-if="(images && images.length > 1)">
                    <button class="carousel-control-prev" type="button" data-bs-target="#product-image-carousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">{{$t('Previous')}}</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#product-image-carousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">{{$t('Next')}}</span>
                    </button>
                </div>
            </div>
            <button @click="openImgPopup" type="button" class="btn btn-link btn-img-popup text-decoration-none position-absolute bottom-0 start-50 translate-middle text-info opacity-50" data-bs-toggle="modal" data-bs-target="#img-popup">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-zoom-in" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M6.5 12a5.5 5.5 0 1 0 0-11 5.5 5.5 0 0 0 0 11zM13 6.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0z"/>
                    <path d="M10.344 11.742c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1 6.538 6.538 0 0 1-1.398 1.4z"/>
                    <path fill-rule="evenodd" d="M6.5 3a.5.5 0 0 1 .5.5V6h2.5a.5.5 0 0 1 0 1H7v2.5a.5.5 0 0 1-1 0V7H3.5a.5.5 0 0 1 0-1H6V3.5a.5.5 0 0 1 .5-.5z"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Img popup -->
    <div class="modal fade" id="img-popup" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <div id="img-popup-carousel" class="carousel slide">
                        <div class="carousel-inner text-center">
                            <div :class="`carousel-item ${index === 0 ? 'active' : ''}`" v-for="(img, index) in images" :key="index">
                                <img @load="imgloaded" :src="`/storage/${img.src}`" width="1000" height="1000" :alt="productName" class="img-loading img-fluid">
                            </div>
                        </div>
                        <div v-if="(images && images.length > 1)">
                            <button class="carousel-control-prev" type="button" data-bs-target="#img-popup-carousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">{{$t('Previous')}}</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#img-popup-carousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">{{$t('Next')}}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>

<script>
import Modal from 'bootstrap/js/dist/modal';
import Carousel from 'bootstrap/js/dist/carousel';
import { mapState } from 'vuex'
export default {
    data: () => ({
        carousel: undefined,
        imageModal: undefined,
    }),
    mounted() {

        const productImageCarousel = document.querySelector('#product-image-carousel')
        if(productImageCarousel) {
            this.carousel = new Carousel(productImageCarousel, { interval: false })
        }

        const modalEl = document.getElementById('img-popup')
        if(modalEl) {
            this.imageModal = new Modal(modalEl)
        }

        // Press previous button
        const _ = this
        window.addEventListener('popstate', () => _.imageModal.hide())

    },
    methods: {
        imgloaded(e) {
            return e.target.classList.remove('img-loading')
        },
        moveTo(index) {
            this.carousel.to(index)
        },
        openImgPopup() {
            const productImageCarousel2 = document.querySelector('#img-popup-carousel')
            if(productImageCarousel2) {
                document.querySelectorAll('#product-image-carousel .carousel-item').forEach((item, index) => {
                    if(item.classList.contains('active')) {
                        new Carousel(productImageCarousel2, { interval: false }).to(index)
                    }
                })
            }
        }
    },
    props: ['images', 'currentImage', 'productName'],
    computed: {
        ...mapState({
            storeConfig: state => state.setting.storeConfig
        }),
    },
    watch: {
        currentImage(obj) {
            if(obj !== undefined) {
                const index = this.images.map(img => img.src).indexOf(obj.src)
                if(index !== -1) {
                    this.carousel.to(index)
                }
            }
        }
    }
}
</script>