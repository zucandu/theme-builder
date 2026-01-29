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
            const response = await import('../../../../data/product_listing.json');
            this.setPagination(response.default || response);
        },

        async fetchProductsByCategory(slug, params) {
            const response = await import('../../../../data/product_listing.json');
            this.setPagination(response.default || response);
        },

        async fetchProductsByKeyword(keyword, params) {
            const response = await import('../../../../data/product_listing.json');
            this.setPagination(response.default || response);
        },
    }

});