<template>
    <div :style="`cursor:pointer;height:${storeConfig.medium_image_size}px; background-image: url('${storeConfig.store_url}/storage/${src}'); background-repeat: no-repeat; background-size: cover; background-position: center;`"></div>
</template>

<script>
import { mapState } from 'vuex'
export default {
    props: ['src', 'height', 'baseUrl'],
    computed: {
        ...mapState({
            storeConfig: state => state.setting.storeConfig,
        })
    }
}
</script>