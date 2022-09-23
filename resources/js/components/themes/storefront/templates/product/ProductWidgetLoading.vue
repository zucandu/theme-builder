<template>
    <div v-for="i in items" :key="i" class="col-xl col-lg-3 col-sm-4 col-6">
        <div class="card card-body border-0 product-widget">
            <div class="inner__img bg-gray-200 mb-3 rounded w-100 py-5"></div>
            <div class="inner__title bg-gray-200 mb-1 rounded w-75 py-2"></div>
            <div class="inner__rating bg-gray-200 mb-3 rounded w-50 py-2"></div>
            <div class="inner__price bg-gray-200 rounded w-25 py-2"></div>
        </div>
    </div>
</template>

<script>
export default {
    data: () => ({
        items: 6
    }),
    created() {
        const width = window.innerWidth
        if(window.innerWidth >= 1200) {
            this.items = 6
        } else if(window.innerWidth >= 992) {
            this.items = 4
        } else if(window.innerWidth >= 576) {
            this.items = 3
        } else {
            this.items = 2
        }
    }
}
</script>