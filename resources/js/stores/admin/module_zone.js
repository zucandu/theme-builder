import { defineStore } from 'pinia';

export const useAdminModuleZoneStore = defineStore('adminModuleZone', {
    state: () => ({
        moduleZones: []
    }),

    getters: {
        
    },

    actions: {

        async fetchModuleZones() {
            try {
                const response = await axios.get('/api/v3/admin/modulezone/all', {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                this.setModuleZones(response.data.zones);
            } catch (error) {
                console.error('fetchModuleZones failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async editModuleZone(id) {
            try {
                const response = await axios.get(`/api/v3/admin/appearance/banner/edit/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.banner;
            } catch (error) {
                console.error('editModuleZone failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async deleteModuleZone(id) {
            try {
                const response = await axios.get(`/api/v3/admin/modulezone/delete/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                this.setModuleZones(response.data.zones);
            } catch (error) {
                console.error('deleteModuleZone failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async createModuleZone(formdata) {
            try {
                const response = await axios.post('/api/v3/admin/modulezone/create', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                this.setModuleZones(response.data.zones);
            } catch (error) {
                console.error('createModuleZone failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async updateModuleZone(formdata) {
            try {
                const response = await axios.post('/api/v3/admin/modulezone/update', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                this.setModuleZones(response.data.zones);
            } catch (error) {
                console.error('updateModuleZone failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        setModuleZones(zones) {
            this.moduleZones = zones;
        }
        
    }

});