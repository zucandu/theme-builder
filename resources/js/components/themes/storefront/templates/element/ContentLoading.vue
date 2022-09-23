<template>
    <div class="card card-body shadow-sm">
        <div class="panel-effect" :style="`${height !== undefined ? `height:${height}` : ''}`">
            <div class="fake-effect fe-1"></div>
            <div class="fake-effect fe-2"></div>
            <div class="fake-effect fe-3"></div>
            <div class="fake-effect fe-4"></div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['height']
}
</script>