import { defineStore } from 'pinia';

export const useAdminBackupStore = defineStore('adminBackup', {
    state: () => ({
        
    }),

    getters: {
        
    },

    actions: {

        async allBackup() {
            try {
                return await axios.get('/api/v3/admin/backup/index', {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('allBackup failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        

        async deleteBackup(id) {
            try {
                return await axios.get(`/api/v3/admin/backup/delete/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('deleteBackups failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async createBackup(formdata) {
            try {
                return await axios.post('/api/v3/admin/backup/create', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('createBackup failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        
        async restoreConfirmation() {
            try {
                return await axios.get('/api/v3/admin/backup/restore-verification', {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('restoreConfirmation failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async restoreDatabaseNow(formdata) {
            try {
                return await axios.post('/api/v3/admin/backup/restore', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('restoreDatabaseNow failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        
    }

});