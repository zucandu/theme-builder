import { defineStore } from 'pinia';

export const useListingStore = defineStore('listing', {
    state: () => ({
        products: [],
        paginationLinks: [],
        paginationInfo: {},
        filters: {},
        object: {}
    }),

    getters: {
    },

    actions: {

        setPagination(data) {
            const { paginator, filters, object } = data
            this.products = paginator.data
            this.paginationLinks = paginator.links
            this.paginationInfo = { from: paginator.from, to: paginator.to, total: paginator.total }
            this.filters = filters
            this.object = object
        },

        async fetchProductsByManufacturer(slug, params) {
            const queryString = new URLSearchParams(params).toString();
            const response = await axios.get(`/api/v3/storefront/manufacturer/listing/${slug}?${queryString}`);
            this.setPagination(response.data);
        },

        async fetchProductsByCategory(slug, params) {
            const queryString = new URLSearchParams(params).toString();
            const response = await axios.get(`/api/v3/storefront/category/listing/${slug}?${queryString}`);
            this.setPagination(response.data);
        },

        async fetchProductsByKeyword(keyword, params) {
            const queryString = new URLSearchParams(params).toString();
            const response = await axios.get(`/api/v3/storefront/search/result/${keyword}?${queryString}`);
            this.setPagination(response.data);
        },
    }

});