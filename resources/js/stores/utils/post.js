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
            try {
                const response = await import('../../../../data/posts_listing.json');
                return response.default || response;
            } catch (error) {
                console.error('fetchPosts failed:', error);
                throw error;
            }
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