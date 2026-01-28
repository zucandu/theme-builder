import { defineStore } from 'pinia';

export const useAdminUsersStore = defineStore('adminUsers', {
    state: () => ({
        users: []
    }),

    getters: {
    },

    actions: {

        async userList() {
            try {
                const response = await axios.get('/api/v3/admin/user/list', {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                this.setAdminUsers(response.data.users);
            } catch (error) {
                console.error('userList failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
            
        },

        async deleteUser(id) {
            try {
                const response = await axios.get(`/api/v3/admin/user/delete/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                this.setAdminUsers(response.data.users);
            } catch (error) {
                console.error('userList failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async userRoles() {
            try {
                const response = await axios.get(`/api/v3/admin/user/roles`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.roles;
            } catch (error) {
                console.error('userRoles failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async createUser(formdata) {
            try {
                const response = await axios.post(`/api/v3/admin/user/create`, formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                this.setAdminUsers(response.data.users);
            } catch (error) {
                console.error('createUser failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async updatePassword(formdata) {
            try {
                const response = await axios.post(`/api/v3/admin/user/set-password`, formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data;
            } catch (error) {
                console.error('updatePassword failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async setPermissions(formdata) {
            try {
                const response = await axios.post(`/api/v3/admin/user/set-permissions`, formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data;
            } catch (error) {
                console.error('setPermissions failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        setAdminUsers(users) {
            this.users = users;
        }
        
    }
    
});