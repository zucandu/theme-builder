<template>
    <section id="product-reviews-section" v-if="loadedReviews" class="section-product-reviews">
        <template v-if="productReviews.length > 0">
            <div v-for="review in productReviews" :key="review.id" class="customer-review row mb-5 pb-5 border-bottom">
                <div class="col-md-4">
                    <div class="d-flex">
                        <img :src="`storage/no-avatar.jpg`" width="60" height="60" :alt="review.customer_name" class="customer-review__avatar img-thumbnail rounded-circle me-3">
                        <div class="customer-review__author">
                            <div>{{ review.customer_name }}</div>
                            <div class="text-dark opacity-75">{{ review.created_at }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="d-flex review-content align-items-center h5">{{ review.review_title }}</div>
                    <div class="my-3">
                        <svg v-for="i in review.rating" :key="i" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-star-fill text-info" viewBox="0 0 16 16">
                            <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
                        </svg>
                        <template v-if="5 - review.rating > 0">
                            <svg v-for="i in (5 - review.rating)" :key="i" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#dddddd" class="bi bi-star-fill" viewBox="0 0 16 16">
                                <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
                            </svg>
                        </template>
                    </div>
                    <div class="text-dark opacity-75 mt-3">{{  review.review_text }}</div>
                </div>
            </div>
        </template>
    </section>
</template>

<script>
import { mapState } from 'vuex'
export default {
    data: () => ({
        loadedReviews: false
    }),
    props: ['id', 'average'],
    created() {
        this.$store.dispatch('latestProductReviews', this.id).then(() => {
            this.loadedReviews = true
        })
    },
    computed: {
        ...mapState({
            productReviews: state => state.product.reviews,
        })
    }
}
</script>