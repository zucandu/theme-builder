<template>
    <div class="h4 fw-bold mb-3 w-50 py-3 bg-gray-200"></div>
    <div class="row">
        <div v-for="i in 3" :key="i" class="col-12 mb-4">
            <div class="section-crosssells__info d-flex">
                <div><img :src="`/storage/store/no-image.png`" :width="smallImgSize" :height="smallImgSize" alt="image loading" class="img-fluid img-loading"></div>
                <div class="text ms-3">
                    <div class="bg-gray-200" style="width:200px;height:12px;"></div>
                    <div class="bg-gray-200 mt-3" style="width:70px;height:12px;"></div>
                    <div class="bg-gray-200 mt-3" style="width:50px;height:12px;"></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props:['smallImgSize']
}
</script>