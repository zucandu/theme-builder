import { defineStore } from 'pinia';

export const useAdminMenuStore = defineStore('adminMenu', {
    state: () => ({
        submenuAction: {}
    }),

    getters: {
        
    },

    actions: {

        async allMenus() {
            try {
                const response = await axios.get('/api/v3/admin/menu/all', {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.menus;
            } catch (error) {
                console.error('allMenus failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async createMenu(formdata) {
            try {
                return await axios.post('/api/v3/admin/menu/create', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('createMenu failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async updateMenu(formdata) {
            try {
                return await axios.post('/api/v3/admin/menu/update', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('updateMenu failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async deleteMenu(id) {
            try {
                return await axios.get(`/api/v3/admin/menu/delete/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('updateMenu failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async getMenuItems(key) {
            try {
                const response = await axios.get(`/api/v3/admin/menu/${key}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.items;
            } catch (error) {
                console.error('getMenuItems failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async deleteMenuItem(id) {
            try {
                return await axios.get(`/api/v3/admin/menu/item/delete/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('deleteMenuItem failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async createMenuItem(formdata) {
            try {
                return await axios.post('/api/v3/admin/menu/item/create', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('createMenuItem failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async updateMenuItem(formdata) {
            try {
                return await axios.post('/api/v3/admin/menu/item/update', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('updateMenuItem failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async createMenuItemElement(formdata) {
            try {
                return await axios.post('/api/v3/admin/menu/item/element/create', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('createMenuItemElement failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        
        async updateMenuItemElement(formdata) {
            try {
                return await axios.post('/api/v3/admin/menu/item/element/update', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('updateMenuItemElement failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async deleteMenuItemElement(id) {
            try {
                return await axios.get(`/api/v3/admin/menu/item/element/delete/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('deleteMenuItemElement failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        }, 

        async createMenuBlock(formdata) {
            try {
                return await axios.post('/api/v3/admin/menu/item/block/create', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('createMenuBlock failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        }, 

        async updateMenuBlock(formdata) {
            try {
                return await axios.post('/api/v3/admin/menu/item/block/update', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('updateMenuBlock failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        }, 

        async deleteMenuBlock(id) {
            try {
                return await axios.get(`/api/v3/admin/menu/item/block/delete/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('deleteMenuBlock failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        }, 

        setSubmenuAction(item) {
            this.submenuAction = item;
        }
    }

});