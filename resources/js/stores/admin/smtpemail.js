import { defineStore } from 'pinia';

export const useAdminSMTPEmailStore = defineStore('adminSMTPEmail', {
    state: () => ({
        
    }),

    getters: {
        
    },

    actions: {

        async fetchSMTPEmails() {
            try {
                return await axios.get('/api/v3/admin/smtpemail/all', {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                })
            } catch (error) {
                console.error('fetchAdminEmails failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async fetchAdminEmails() {
            try {
                return await axios.get(`/api/v3/admin/user/email/list`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('fetchAdminEmails failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async addUserEmail(formdata) {
            try {
                return await axios.post('/api/v3/admin/smtpemail/create', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('addUserEmail failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async createSMTPEmail(formdata) {
            try {
                return await axios.post('/api/v3/admin/smtpemail/create', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('createSMTPEmail failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async updateSMTPEmail(formdata) {
            try {
                return await axios.post('/api/v3/admin/smtpemail/update', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('updateSMTPEmail failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async deleteSMTP(id) {
            try {
                return await axios.get(`/api/v3/admin/smtpemail/delete/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                })
            } catch (error) {
                console.error('deleteSMTP failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        
    }

});