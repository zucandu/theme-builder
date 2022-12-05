<template>
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div v-if="!customerAccessToken" class="card card-body">
                <div class="card-title h5">{{$t('Login')}}</div>
                <form @submit.prevent="login()">
                    <div class="mb-3">
                        <label for="email-address" class="form-label">{{$t('Email address')}}</label>
                        <input v-model="formdata.email" type="email" class="form-control" id="email-address" placeholder="name@example.com" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">{{$t('Password')}}</label>
                        <input v-model="formdata.password" type="password" class="form-control" id="password" :placeholder="$t('Password')" required>
                    </div>
                    <div class="d-flex justify-content-between">
                        <router-link class="btn btn-link px-0" to="/forgot-password">{{$t('Forgot Password')}}</router-link>
                        <button class="btn btn-primary" type="submit">{{$t('Login')}}</button>
                    </div>
                    <hr class="border-light">
                    <p class="text-center">{{$t("Don't have an account?")}} <router-link to="/register">{{$t('Sign Up')}}</router-link></p>
                </form>
            </div>
            <div v-else class="card card-body">
                <p>{{ $t('You are already logged in, you need to log out before logging in as different customer.') }}</p>
                <div class="d-flex">
                    <router-link to="/account/orders/list" class="btn btn-primary me-3">{{ $t('My account') }}</router-link>
                    <router-link to="/logout" class="btn">{{ $t('Log out') }}</router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapGetters } from 'vuex'
export default {
    data: () => ({
        formdata: {
            email: undefined,
            password: undefined
        }
    }),
    methods: {
        login() {

            this.$store.dispatch('login', this.formdata).then(() => {
                this.$store.dispatch('account').then(() => {
                    if(this.$route.query.redirect) {
                        this.$router.push(this.$route.query.redirect)
                    } else {
                        this.$router.push('/account');
                    }
                })
            }).catch(error => {
                this.$store.commit('setAlert', {
                    'color': 'danger', 
                    'message': this.$t(error.response.data.message)
                })
            })
        }
    },
    computed: {
        ...mapGetters(['customerAccessToken'])
    }
}
</script>