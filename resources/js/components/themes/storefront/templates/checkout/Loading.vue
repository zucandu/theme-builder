<template>
    <div class="row">
        <div class="col-lg-6 pt-lg-5 pt-3">
            <div class="row">
                <div class="col-12">
                    <div class="h5 mb-3">{{$t('Shipping & Payment Address')}}</div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="h6">{{$t('Shipping Address')}}</div>
                            <div class="col-5 bg-gray-200 rounded py-2 mb-1"></div>
                            <div class="col-10 bg-gray-200 rounded py-2 mb-1"></div>
                            <div class="col-6 bg-gray-200 rounded py-2 mb-1"></div>
                            <div class="col-3 bg-gray-200 rounded py-2 mb-3"></div>
                            <div class="col-2 bg-gray-200 rounded py-2 mb-1"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="h6">{{$t('Billing Address')}}</div>
                            <div class="col-5 bg-gray-200 rounded py-2 mb-1"></div>
                            <div class="col-10 bg-gray-200 rounded py-2 mb-1"></div>
                            <div class="col-6 bg-gray-200 rounded py-2 mb-1"></div>
                            <div class="col-3 bg-gray-200 rounded py-2 mb-3"></div>
                            <div class="col-2 bg-gray-200 rounded py-2 mb-1"></div>
                        </div>
                    </div>
                    <div class="row mt-5">
                        <div class="col-12">
                            <div class="h5 mb-3">{{$t('Shipping Methods')}}</div>
                            <div class="card card-body">
                                <div class="bg-gray-200 rounded py-3 px-5 mb-2 col-lg-6"></div>
                                <div class="d-flex justify-content-between">
                                    <div class="bg-gray-200 rounded py-2 px-5"></div>
                                    <div class="bg-gray-200 rounded py-2 px-5"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-5">
                        <div class="col-12">
                            <div class="h5 mb-3">{{$t('Promotions')}}</div>
                            <div class="card card-body">
                                <div class="bg-gray-200 rounded py-3 px-5 mb-2 col-lg-6"></div>
                                <div class="d-flex justify-content-between">
                                    <div class="bg-gray-200 rounded py-2 px-5"></div>
                                    <div class="bg-gray-200 rounded py-2 px-5"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-5">
                        <div class="col-12">
                            <div class="h5 mb-3">{{$t('Payment Methods')}}</div>
                            <div class="card card-body">
                                <div class="bg-gray-200 rounded py-3 px-5 mb-2 col-lg-6"></div>
                                <div class="d-flex justify-content-between">
                                    <div class="bg-gray-200 rounded py-2 px-5"></div>
                                    <div class="bg-gray-200 rounded py-2 px-5"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5 offset-lg-1 pt-lg-5 pt-3">
            <div class="h5 mb-3">{{ $t('Your shopping cart') }}</div>
            <div class="row">
                <div class="col-12 bg-gray-200 rounded mb-2 py-3"></div>
                <div class="col-12 bg-gray-200 rounded mb-1 py-2"></div>
                <div class="col-12 bg-gray-200 rounded mb-1 py-2"></div>
                <div class="col-12 bg-gray-200 rounded mb-1 py-2"></div>
            </div>
        </div>
    </div>
</template>