<template>
    <div class="row justify-content-center">
        <div class="col-5">
            <div class="card">
                <div class="card-body">
                    <div class="card-title h5">{{$t('Password Forgotten')}}</div>
                    <form @submit.prevent="resetPassword()">
                        <div class="mb-3">
                            <label for="password" class="form-label">{{$t('Password')}}</label>
                            <input v-model="formdata.password" type="password" class="form-control" id="password" :placeholder="$t('Password')" required>
                        </div>
                        <div class="mb-3">
                            <label for="password-confirm" class="form-label">{{$t('Confirm Password')}}</label>
                            <input v-model="formdata.password_confirmation" type="password" class="form-control" id="password-confirm" :placeholder="$t('Password Confirmation')" required>
                            <div class="text-danger small" v-if="passwordNotMatch">{{$t('Please make sure your password match')}}</div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <router-link class="btn btn-link px-0" to="/login">{{$t('Back to login')}}</router-link>
                            <button class="btn btn-primary" type="submit" :disabled="passwordNotMatch">{{$t('Reset')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data: () => ({
        formdata: {
            password: undefined,
            password_confirmation: undefined,
            token: undefined
        }
    }),
    created() {
        this.formdata.token = this.$route.params.token
    },
    methods: {
        resetPassword() {
            this.$store.dispatch('resetPassword', this.formdata).then(() => {
                this.$store.commit('setAlert', {
                    'color': 'success', 
                    'message': this.$t('Your password has been reset. Please login and happy shopping!')
                });
                this.$router.push('/login')
            }).catch(error => {
                this.$store.commit('setAlert', {
                    'color': 'danger', 
                    'message': this.$t(error.response.data.message)
                });
            })
        },
    },
    computed: {
        passwordNotMatch() {
            return (this.formdata.password && (this.formdata.password !== this.formdata.password_confirmation))
        }
    }
}
</script>