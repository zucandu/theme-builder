<template>
    <div>
        <div class="bg-gray-200 py-4 rounded w-75 mb-2"></div>
        <div class="d-flex align-items-center">
            <div>
                <svg v-for="i in 5" :key="i" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-star-fill text-dark opacity-25" viewBox="0 0 16 16">
                    <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
                </svg>
            </div>
            <div class="py-2 ms-2 rounded bg-gray-200 px-5"></div>
        </div>
        <div class="mt-3 d-flex">
            <div v-for="i in 3" :key="i" class="bg-gray-200 py-2 px-5 rounded me-3"></div>
        </div>
        <div class="my-3 d-flex">
            <div class="bg-gray-200 py-2 w-50 rounded me-3"></div>
        </div>
        <div v-for="i in 2" :key="i" class="row mt-1">
            <div class="col-2">
                <div class="py-2 w-100 rounded bg-gray-200"></div>
            </div>
            <div class="col-5">
                <div class="py-2 w-100 rounded bg-gray-200"></div>
            </div>
        </div>
        <div class="text-dark opacity-25 h4 mt-3">$____</div>
        <div class="mt-3">
            <div><div class="py-3 px-5 d-inline-block bg-gray-200 rounded mb-2"></div></div>
            <div v-for="i in 3" :key="i" class="d-inline-block py-4 px-5 rounded me-2 bg-gray-200"></div>
        </div>
        <div class="mt-3 d-flex">
            <div class="p-4 bg-gray-200 me-2 rounded"></div>
            <div class="py-4 px-5 bg-gray-200 rounded"></div>
        </div>
    </div>
</template>