import { defineStore } from 'pinia';

export const useAdminThemeStore = defineStore('adminTheme', {
    state: () => ({
        themes: [],
        currentSelectedFile: undefined
    }),

    getters: {
        
    },

    actions: {

        async getAll() {
            try {
                const response = await axios.get('/api/v3/admin/appearance/theme/list', {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                this.setThemes(response.data.themes);
            } catch (error) {
                console.error('getAll failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async downloadThemeFiles(themeName) {
            try {
                const response = await axios.get(`/api/v3/admin/appearance/theme/download/${themeName}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.zip;
            } catch (error) {
                console.error('downloadThemeFiles failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async downloadHookFiles() {
            try {
                const response = await axios.get(`/api/v3/admin/appearance/theme/download-hook-files`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.zip;
            } catch (error) {
                console.error('downloadThemeFiles failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async installTheme(filename) {
            try {
                const response = await axios.get('/api/v3/admin/appearance/theme/installation?zipfile=' + filename, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                this.setThemes(response.data.themes);
            } catch (error) {
                console.error('installTheme failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        async activeTheme(name) {
            try {
                const response = await axios.get(`/api/v3/admin/appearance/theme/active/${name}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                this.setThemes(response.data.themes);

            } catch (error) {
                console.error('activeTheme failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        async createLanguageJson(formdata) {
            try {
                await axios.post('/api/v3/admin/appearance/theme/create-lang', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('createLanguageJson failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async getAllLanguagesJson() {
            try {
                const response = await axios.get('/api/v3/admin/appearance/theme/languages', {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.files;
            } catch (error) {
                console.error('getAllLanguagesJson failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async getLanguageContent(formdata) {
            try {
                const response = await axios.post('/api/v3/admin/appearance/theme/lang', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.content;
            } catch (error) {
                console.error('getLanguageContent failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async updateLanguageContent(formdata) {
            try {
                return await axios.post('/api/v3/admin/appearance/theme/update-lang', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('updateLanguageContent failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async getThemeFiles() {
            try {
                const response = await axios.get('/api/v3/admin/appearance/theme/files', {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data;
            } catch (error) {
                console.error('getThemeFiles failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async getThemeContent(formdata) {
            try {
                const response = await axios.post('/api/v3/admin/appearance/theme/file', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.content;
            } catch (error) {
                console.error('getThemeContent failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async updateTemplateContent(formdata) {
            try {
                const response = await axios.post('/api/v3/admin/appearance/theme/update-file', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.content;
            } catch (error) {
                console.error('updateTemplateContent failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async createHookFile(formdata) {
            try {
                return await axios.post('/api/v3/admin/appearance/theme/create-hook-file', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('createHookFile failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async connectThemeFromMarketplace(id) {
            try {
                return await axios.get(`/api/v3/admin/appearance/theme/marketplace/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('connectThemeFromMarketplace failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async downloadThemeFromMarketplace(id) {
            try {
                return await axios.get(`/api/v3/admin/appearance/theme/marketplace/download/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('downloadThemeFromMarketplace failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        setThemes(themes) {
            this.themes = themes;
        },

        selectedFile(fileName) {
            this.currentSelectedFile = fileName;
        }
        
    }

});