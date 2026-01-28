import { defineStore } from 'pinia';

export const useAdminOrderStore = defineStore('adminOrder', {
    state: () => ({
        orderStatuses: []
    }),

    getters: {
        
    },

    actions: {
        
        async fetchOrders(params) {
            try {
                const queryString = new URLSearchParams(params).toString();
                return await axios.get(`/api/v3/admin/order/all?${queryString}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('fetchOrders failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        async getOrderDetails(id) {
            try {
                return await axios.get(`/api/v3/admin/order/edit/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                })
            } catch (error) {
                console.error('viewOrderDetails failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        
        async retrieveOrderStatuses() {
            try {
                const response = await axios.get('/api/v3/admin/order/statuses', {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                this.setOrderStatuses(response.data.statuses);
            } catch (error) {
                console.error('retrieveOrderStatuses failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async updateOrderStatus(formdata) {
            try {
                return await axios.post('/api/v3/admin/order/update-order-status', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('updateOrderStatus failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async updateOrder(formdata) {
            try {
                return axios.post('/api/v3/admin/order/update-order', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('updateOrder failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async retrieveAllPaymentMethods() {
            try {
                return axios.get('/api/v3/admin/order/payments', {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('retrieveAllPaymentMethods failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        async retrieveAllShippingMethods() {
            try {
                return axios.get('/api/v3/admin/order/shippings', {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('retrieveAllShippingMethods failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async retrieveMultipleOrders(formdata) {
            try {
                return axios.post('/api/v3/admin/order/multiple-orders', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('retrieveMultipleOrders failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        // Display the shipping methods for admin new order
        async fetchShippingMethods(formdata) {
            
            try {
                const response = await axios.post('/api/v3/admin/order/shipping-methods', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.shipping_methods;
            } catch (error) {
                console.error('getShippingMethods failed:', error);
                throw error; // Optionally propagate the error
            }
        },

        // Create a new order
        async createOrder(formdata) {
            
            try {
                return await axios.post(`/api/v3/admin/order/create`, formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('createOrder failed:', error);
                throw error; // Optionally propagate the error
            }
        },

        // Delete selected orders
        async deleteSelectedOrders(ids) {
            
            try {
                return await axios.post(`/api/v3/admin/order/delete-selected-orders`, ids, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                })
            } catch (error) {
                console.error('deleteSelectedOrders failed:', error);
                throw error; // Optionally propagate the error
            }
        },

        // Delete order status
        async deleteOrderStatus(id) {
            
            try {
                return await axios.get(`/api/v3/admin/order/status/delete/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('deleteOrderStatus failed:', error);
                throw error; // Optionally propagate the error
            }
        },

        // Delete selected orders
        async retrieveOrderDetails(id) {
            try {
                const response = await axios.get(`/api/v3/admin/order/edit/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.order;
            } catch (error) {
                console.error('retrieveOrderDetails failed:', error);
                throw error; // Optionally propagate the error
            }
        },

        async fetchAbandonedOrders(params) {
            try {
                const queryString = new URLSearchParams(params).toString();
                return await axios.get(`/api/v3/admin/order/abandoned/all?${queryString}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('fetchAbandonedOrders failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async sendAbandonedOrderEmail(customerIds) {
            try {
                return await axios.post(`/api/v3/admin/order/abandoned/email`, customerIds, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                })
            } catch (error) {
                console.error('sendAbandonedOrderEmail failed:', error);
                throw error; // Optionally propagate the error
            }
        },

        async deleteAbandonedOrders(customerIds) {
            try {
                return await axios.post(`/api/v3/admin/order/abandoned/delete`, customerIds, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                })
            } catch (error) {
                console.error('deleteAbandonedOrders failed:', error);
                throw error; // Optionally propagate the error
            }
        },

        async createOrderStatusType(formdata) {
            try {
                return await axios.post(`/api/v3/admin/order/status/create`, formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('createOrderStatusType failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async updateOrderStatusType(formdata) {
            try {
                return await axios.post(`/api/v3/admin/order/status/update`, formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('updateOrderStatusType failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async approveReturnItems(formdata) {
            try {
                return await axios.post(`/api/v3/admin/order/accept-return-items`, formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('approveReturnItems failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async updateReturnItems(formdata) {
            try {
                return await axios.post(`/api/v3/admin/order/update-return-items`, formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('updateReturnItems failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async handlePayPalOrderCapture(formdata) {
            try {
                return await axios.post(`/api/v3/admin/order/paypal-payment-confirmation`, formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('handlePayPalOrderCapture failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async paypalRequest(formdata) {
            try {
                return await axios.post(`/api/v3/admin/order/create-payment-request`, formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('paypalRequest failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async refundbyPaypal(formdata) {
            try {
                return await axios.post(`/api/v3/admin/order/paypal-refund`, formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('refundbyPaypal failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async refundBySquare(formdata) {
            try {
                return await axios.post(`/api/v3/admin/order/square-refund`, formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('refundBySquare failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async paymentRequest(formdata) {
            try {
                return await axios.post(`/api/v3/admin/order/payment-request`, formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('paymentRequest failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        
        setOrderStatuses(data) {
            this.orderStatuses = data;
        }
        
    }

});