<template>
    <div class="card" v-if="profile">
        <div class="card-header fw-bold">{{$t("Update Your Password")}}</div>
        <div class="card-body">
            <form @submit.prevent="updatePassword()">
                <div class="mb-3">
                    <label for="password" class="form-label">{{$t('Password')}}</label>
                    <input v-model="formdata.password" type="password" class="form-control" id="password" :placeholder="$t('Enter your password')" required>
                </div>
                <div class="mb-3">
                    <label for="passwordconfirm" class="form-label">{{$t('Confirm Password')}}</label>
                    <input v-model="formdata.passwordconfirm" type="password" class="form-control" id="passwordconfirm" :placeholder="$t('Confirm your password')" required>
                </div>
                <button class="btn btn-primary" type="submit">{{$t('Update')}}</button>
            </form>
        </div>
    </div>
</template>


<script>
import { mapState } from 'vuex'
export default {
    data: () => ({
        formdata: {
            password: undefined,
            passwordconfirm: undefined,
        }
    }),
    methods: {
        updatePassword() {

            this.$store.dispatch('updatePassword', this.formdata).then(() => {
                this.$store.commit('setAlert', {
                    'color': 'success', 
                    'message': this.$t('Updated!')
                })
                this.resetForm()
            }).catch(error => {
                this.$store.commit('setAlert', {
                    'color': 'danger', 
                    'message': this.$t(error.response.data.message)
                });
            })
        },
        resetForm() {
            for (const [key, value] of Object.entries(this.formdata)) {
                this.formdata[key] = undefined
            }
        }
    },
    computed: {
        ...mapState({
            profile: state => state.customer.profile,
        }),
    }
}
</script>