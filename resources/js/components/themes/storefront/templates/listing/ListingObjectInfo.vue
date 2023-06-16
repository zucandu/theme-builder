<template>
    <section class="zuc-listing-products__category">
        <template v-if="object">
            <h1 class="text-dark fw-bold mb-4">{{ object.name }}</h1>
            <div v-html="object.description"></div>
        </template>
    </section>
</template>

<script>
export default {
    props: ['object']
}
</script>