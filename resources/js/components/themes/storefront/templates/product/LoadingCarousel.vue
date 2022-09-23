<template>
    <div class="row justify-content-center g-3">
        <div class="col-md-3 text-center order-md-0 order-1">
            <div class="thumbnails">
                <div :class="`thumb thumb-0 mb-md-3 mx-md-0 mx-2 cursor-pointer d-md-block d-inline-block`">
                    <img @load="imgloaded" :src="`/storage/store/no-image.png`" :width="smallSize" :height="smallSize" alt="image loading" class="img-loading img-fluid">
                </div>
            </div>
        </div>
        <div class="col-md-9 text-center order-md-1 order-0">
            <div id="product-image-carousel" class="carousel slide">
                <div class="carousel-inner text-center">
                    <div :class="`carousel-item active`">
                        <img @load="imgloaded" :src="`/storage/store/no-image.png`" :width="largeSize" :height="largeSize" alt="image loading" class="img-loading img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['smallSize', 'largeSize'],
    methods: {
        imgloaded: function (e) {
            e.target.classList.remove('img-loading')
        }
    }
}
</script>