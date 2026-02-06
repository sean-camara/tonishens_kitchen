import api from './axios';

export const getDishes = (params) => api.get('/menu', { params });
export const getDish = (id) => api.get(`/menu/${id}`);
export const getCategories = () => api.get('/categories');
