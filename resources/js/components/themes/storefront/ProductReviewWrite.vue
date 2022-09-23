<template>
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <transition name="fade">
                <form v-if="loadedProduct" @submit.prevent="submitProductReview">
                    <div class="card card-body">
                        <div class="card-title h5">{{ $t('Write Your Own Review') }}</div>
                        <p><router-link :to="`/${productTranslation.slug}`">{{ productTranslation.name }}</router-link> {{ productTranslation.sku }}</p>
                        <div class="mb-3">
                            <label class="form-label d-block">{{$t('Please choose rating')}}</label>
                            <div class="form-check form-check-inline" v-for="rating in ratingItems" :key="rating">
                                <input v-model="formdata.rating" class="form-check-input" type="radio" :id="`rrating${rating}`" :value="rating">
                                <label class="form-check-label" :for="`rrating${rating}`">{{rating}} <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#ffc107" class="bi bi-star-fill mb-1" viewBox="0 0 16 16"><path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/></svg></label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="rtitle" class="form-label">{{$t('Review Title')}} {{$t('(Optional)')}}</label>
                            <input v-model="formdata.review_title" class="form-control" id="rtitle" :placeholder="$t('Enter your review title')">
                        </div>
                        <div class="mb-3">
                            <label for="rtext" class="form-label">{{$t('Let Us Know Your Thoughts?')}}</label>
                            <textarea v-model="formdata.review_text" class="form-control" id="rtext" :placeholder="$t('Write something...')" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="rnickname" class="form-label">{{$t('Nickname')}}</label>
                            <input v-model="formdata.customer_name" class="form-control" id="rnickname" :placeholder="$t('Enter your nickname')">
                        </div>
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-primary" type="submit">{{$t('Submit Review')}}</button>
                        </div>
                    </div>
                </form>
            </transition>
            <div v-if="!loadedProduct" class="card card-body">
                <div class="spinner-grow text-light m-auto" style="width: 5rem; height: 5rem;" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapGetters, mapState } from 'vuex'
export default {
    data: () => ({
        ratingItems: 5,
        formdata: {
            rating: 5,
            review_title: undefined,
            review_text: undefined,
            customer_name: undefined,
            grecaptcha_token: undefined
        },
        realProduct: {},
        loadedProduct: false
    }),
    created() {

        this.$store.dispatch('productDetails', this.$route.params.productslug).then(() => {

            // Meta tags
            document.title = `${this.$i18n.t('Write a review for')} ${this.productTranslation.meta_title}`
            document.querySelector('meta[name="description"]').setAttribute("content", this.productTranslation.meta_description)

        }).finally(() => {
            this.loadedProduct = true
        })

        this.formdata.customer_name = this.profile ? this.profile.firstname : undefined
    },
    methods: {
        async submitProductReview() {
            
            // Get Google reCAPTCHA token if the site key is set
            const _ = this
            if(_.storeConfig && _.storeConfig.recaptcha_site_key) {
                grecaptcha.ready(function() {
                    grecaptcha.execute(_.storeConfig.recaptcha_site_key, { action: 'submit' }).then(function(token) {
                        _.formdata.grecaptcha_token = token
                    })
                })
                while(_.formdata.grecaptcha_token === undefined) {
                    await new Promise(r => setTimeout(r, 100))
                }
            }

            // Submit review
            this.$store.dispatch('addProductReview', { ...this.formdata, locale: this.$i18n.locale, product_id: this.productDetails.id }).then(() => {
                
                this.resetForm()
                this.$store.commit('setAlert', {
                    'color': 'success', 
                    'message': this.$t('Thank you for the review. We will review and publish it as soon as possible.')
                })

                // Redirect to product page
                this.$router.push(`/${this.$route.params.productslug}`)

            }).catch(error => {
                this.$store.commit('setAlert', {
                    'color': 'danger', 
                    'message': this.$t(error.response.data.message)
                })
            })
        },
        resetForm() {
            this.formdata = {
                review_title: undefined,
                review_text: undefined,
            }
        }
    },
    computed: {
        ...mapGetters(['isCustomer', 'transObj', 'translation']),
        ...mapState({
            productDetails: state => state.product.productDetails,
            storeConfig: state => state.setting.storeConfig,
            profile: state => state.customer.profile,
        }),
        productTranslation() {
            return this.transObj(this.productDetails, this.$i18n.locale)
        }
    },
    watch: {
        profile(v) {
            if(v) {
                this.formdata.customer_name = v.firstname
            }
        }
    }
}
</script>