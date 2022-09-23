<template>
    <div class="row justify-content-center">
        <div class="col-12 col-lg-7">
            <form @submit.prevent="submitRequest">
                <div v-if="loaded === true" class="card card-body">
                    <div class="card-title">{{ $t('START A RETURN') }}</div>
                    <table class="table border-light">
                        <thead>
                            <tr>
                                <th class="border-light"></th>
                                <th class="border-light">{{ $t('Name') }}</th>
                                <th class="border-light">{{ $t('Resolution') }}</th>
                                <th class="border-light">{{ $t('Quantity') }}</th>
                                <th class="border-light">{{ $t('Reason') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in order.items" :key="item.product_id">
                                <td class="py-4">
									<img v-if="item.images && item.images.length > 0" :src="`/storage/${storeConfig.small_image_size}/${item.images[0].src}`" alt="" class="img-thumbnail">
                                    <img v-else :src="`/storage/${storeConfig.small_image_size}/store/no-image.png`" alt="" class="img-thumbnail">
								</td>
                                <td class="py-4">
                                    <p>{{ item.name }}</p>
                                </td>
                                <td class="py-4">
                                    <div class="form-check">
                                        <input v-model="formdata.resolution[item.product_id]" class="form-check-input" type="radio" :id="`refund-item${item.product_id}`" value="refund">
                                        <label class="form-check-label" :for="`refund-item${item.product_id}`">{{ $t('Refund') }}</label>
                                    </div>
                                    <div class="form-check">
                                        <input v-model="formdata.resolution[item.product_id]" class="form-check-input" type="radio" :id="`ex-item${item.product_id}`" value="exchange">
                                        <label class="form-check-label" :for="`ex-item${item.product_id}`">{{ $t('Exchange') }}</label>
                                    </div>
                                </td>
                                <td class="py-4">
                                    <template v-if="(item.qty - item.qty_returned)">
                                        <select v-model="formdata.qty[item.product_id]" class="form-select form-select-sm"> 
                                            <option v-for="i in (item.qty - item.qty_returned)" :value="i" :key="i">{{ i }}</option>
                                        </select>
                                    </template>
                                    <template v-else>
                                        <span class="badge bg-success">{{ $t('Returned all') }}</span>
                                    </template>
                                </td>
                                <td class="py-4">
                                    <select :disabled="!(item.qty - item.qty_returned)" v-model="formdata.reason[item.product_id]" class="form-select form-select-sm">
                                        <option :value="undefined">{{ $t('-Please select-') }}</option>
                                        <option v-for="reason in reasonItems" :value="reason" :key="reason">{{ $t(reason) }}</option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="mb-3">
                        <dropzone-storefront-uploader @updateContent="updateContent"></dropzone-storefront-uploader>
                    </div>
                    <div class="row mb-3">
                        <div class="col-lg-12">
                            <label>{{ $t('Additional') }} ({{ $t('Optional') }})</label>
                            <textarea v-model="formdata.additional" class="form-control" :placeholder="$t('Write something...(optional)')"></textarea>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <router-link class="btn btn-link text-decoration-none" to="/account/orders/list">{{ $t('Back to my account') }}</router-link>
                        <button class="btn btn-primary text-uppercase">{{ $t('Submit Request') }}</button>
                    </div>
                </div>
                <div v-else>
                    <div class="card card-body">
                        <div class="rounded bg-gray-200 py-4 w-50 mb-3"></div>
                        <div class="rounded bg-gray-200 py-4 w-100 mb-3"></div>
                        <div class="rounded bg-gray-200 py-3 w-100 mb-2"></div>
                        <div class="rounded bg-gray-200 py-2 w-100 mb-2"></div>
                        <div class="rounded bg-gray-200 py-2 w-100 mb-2"></div>
                    </div>
                </div>
            </form>
            <div v-if="loaded === true" class="card mt-5">
                <template v-if="order.returns && order.returns.length > 0">
                    <div class="card-title h6 fw-bold mt-3 px-3 no-print">{{$t('Returned Item History')}}</div>
                    <div class="card-body">
                        <table class="table table-striped hover no-print">
                            <thead>
                                <tr>
                                    <th>{{$t('Date')}}</th>
                                    <th>{{$t('Type')}}</th>
                                    <th>{{$t('Item')}}</th>
                                    <th>{{$t('Reason')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in order.returns" :key="item.product_id">
                                    <td>{{ item.created_at }}</td>
                                    <td><span class="text-capitalize">{{ item.resolution }}</span></td>
                                    <td><strong>{{ item.qty }}x {{ item.name }}</strong></td>
                                    <td>{{ item.reason }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </template>
            </div>
            <div v-else>
                <div class="card card-body">
                    <div class="rounded bg-gray-200 py-4 w-50 mb-3"></div>
                    <div class="rounded bg-gray-200 py-4 w-100 mb-3"></div>
                    <div class="rounded bg-gray-200 py-3 w-100 mb-2"></div>
                    <div class="rounded bg-gray-200 py-2 w-100 mb-2"></div>
                    <div class="rounded bg-gray-200 py-2 w-100 mb-2"></div>
                </div>
            </div>
        </div>
        <overlay v-if="loading"></overlay>
    </div>
</template>

<script>
import Overlay from '@theme/storefront/templates/element/Overlay'
import DropzoneStorefrontUploader from '@theme/storefront/templates/account/DropzoneStorefrontUploader'
import { mapState, mapGetters } from 'vuex'
export default {
    data: () => ({
        reasonItems: ["Doesn't Fit", "Doesn't Match", "Received Wrong Item", "Arrived Damaged", "Don't like", "Other"],
        formdata: {
            id: undefined,
            order_ref: undefined,
            resolution: {},
            qty: {},
            reason: {},
            additional: undefined,
            attachments: []
        },
        loaded: false,
        loading: false
    }),
    components: { Overlay, DropzoneStorefrontUploader },
    created() {

        // load order from ref
        this.$store.dispatch('orderDetailsByRef', this.$route.params.ref).then(() => {
            if(this.order && this.order.items && this.order.items.length > 0) {
                this.order.items.map(item => {
                    this.formdata.resolution[item.product_id] = `refund`
                    this.formdata.qty[item.product_id] = 1
                    this.formdata.reason[item.product_id] = undefined
                    this.formdata.order_ref = this.order.reference
                })
            }
            this.loaded = true
        }).catch(error => {
            this.$store.commit('setAlert', {
                'color': 'danger', 
                'message': this.$t(error.response.data.message)
            })
            this.$router.push('/return-exchange/form')
        })
    },
    methods: {
        submitRequest() {

            this.loading = true

            this.$store.dispatch('rmaRequest', this.formdata).then(() => {
                this.$store.dispatch('orderDetailsByRef', this.$route.params.ref).then(() => {
                    this.$store.commit('setAlert', {
                        'color': 'success', 
                        'message': this.$t(`We have received your request and we will contact you as soon as possible.`)
                    })
                })
            }).catch(error => {
                this.$store.commit('setAlert', {
                    'color': 'danger', 
                    'message': this.$t(error.response.data.message)
                })
            }).finally(() => {
                this.loading = false
            })
        },
        updateContent(v) {
            
            switch(v.type) {
                case 'add':
                    this.formdata.attachments.push(v.content)
                break;
                case 'remove':
                    const index = this.formdata.attachments.indexOf(v.content)
                    if (index !== -1) {
                        this.formdata.attachments.splice(index, 1)
                    }
                break;
            }
        }
    },
    computed: {
        ...mapGetters(['productPrice', 'displayPrice']),
        ...mapState({
            storeConfig: state => state.setting.storeConfig,
            order: state => state.order.orderFromDb,
            profile: state => state.customer.profile,
        })
    }
}
</script>