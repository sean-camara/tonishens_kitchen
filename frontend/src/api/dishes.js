import api from './axios';

export const getDishes = (params) => api.get('/dishes', { params });
export const getDish = (id) => api.get(`/dishes/${id}`);
export const getCategories = () => api.get('/categories');
