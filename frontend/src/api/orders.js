import api from './axios';

export const getMyOrders = () => api.get('/orders');
export const getOrder = (id) => api.get(`/orders/${id}`);
export const placeOrder = (data) => api.post('/checkout', data);
export const cancelOrder = (id) => api.post(`/orders/${id}/cancel`);
export const submitFeedback = (orderId, data) => api.post(`/orders/${orderId}/feedback`, data);
