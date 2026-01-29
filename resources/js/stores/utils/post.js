import { defineStore } from 'pinia';

export const usePostStore = defineStore('post', {
    state: () => ({
        latestPosts: []
    }),

    getters: {
    },

    actions: {

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
                const response = await import(`../../../../data/articles/${slug}.json`);
                return response.default || response;
            } catch (error) {
                console.error('retrieveArticleDetails failed:', error);
                throw error;
            }

        }
    }

});