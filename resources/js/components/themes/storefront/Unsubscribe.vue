<template>
    <div class="row justify-content-center">
        <div class="col-12 col-lg-6">
            <div class="card card-body">
                <div class="card-title h5">{{ $t("We're sorry to see you go!") }}</div>
                <p>{{ $t("Was it something we said? We're sorry that you've decided to leave us. Please enter your email address from below form which you'd like to unsubscribe.") }}</p>
                <form @submit.prevent="unsubscribeNewsletter()">
                    <div class="mb-3">
                        <label for="email-address" class="form-label">{{$t('Email address')}}</label>
                        <input v-model="formdata.email" type="email" class="form-control" id="email-address" placeholder="name@example.com" required>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button class="btn btn-primary" type="submit">{{ $t("It's over!") }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data: () => ({
        formdata: {
            email: undefined,
        }
    }),
    methods: {
        unsubscribeNewsletter() {
            this.$store.dispatch('unsubscribeNewsletter', this.formdata).then(() => {
                this.$store.commit('setAlert', {
                    'color': 'success', 
                    'message': this.$t("You've been unsubscribed from our newsletter."),
                })
                this.$router.push('/');
            }).catch(error => {
                this.$store.commit('setAlert', {
                    'color': 'danger', 
                    'message': error.response.data.message,
                })
            })
        }
    },
}
</script>