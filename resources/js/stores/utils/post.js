import { defineStore } from 'pinia';

export const usePostStore = defineStore('post', {
    state: () => ({
        latestPosts: []
    }),

    getters: {
    },

    actions: {

        /* setLatestPosts(posts) {
            this.latestPosts = posts;
        }, */

        async fetchPosts(params) {
            const queryString = new URLSearchParams(params).toString();
            const response = await axios.get(`/api/v3/storefront/article/listing?${queryString}`);
            return response.data;
            // this.setLatestPosts(response.data.paginator.data);
        },

        async retrieveArticleDetails(slug) {
            try {
                const response = await axios.get(`/api/v3/storefront/article/${slug}`);
                return response.data.post;
            } catch (error) {
                console.error('retrieveArticleDetails failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
            
        }
    }

});