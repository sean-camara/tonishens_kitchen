import api from './axios';

export const getCart = () => api.get('/cart');
export const addToCart = (dish_id, quantity = 1) => api.post('/cart', { dish_id, quantity });
export const updateCartItem = (dish_id, quantity) => api.put(`/cart/${dish_id}`, { quantity });
export const removeFromCart = (dish_id) => api.delete(`/cart/${dish_id}`);
export const clearCart = () => api.delete('/cart');
