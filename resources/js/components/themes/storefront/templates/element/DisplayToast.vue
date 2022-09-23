<template>
    <div class="toast-container position-fixed p-3 top-0 end-0">
        <div :class="`toast align-items-center text-white ${alert.color ? `bg-${alert.color}` : ''} border-0 fade show`" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex" v-if="alert.message">
                <div class="toast-body">{{alert.message}}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
</template>

<script>
import Toast from 'bootstrap/js/dist/toast';
import { mapState } from 'vuex';
export default {
    computed: {
        ...mapState({
            alert: state => state.setting.alert
        })
    },
    watch: {
        alert(v) {
            const toast = document.querySelector('.toast')
            new Toast(toast).show()
        }
    }
}
</script>