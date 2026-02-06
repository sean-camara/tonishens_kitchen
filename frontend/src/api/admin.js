import api from './axios';

// Dashboard
export const getDashboard = () => api.get('/admin/dashboard');

// Dishes
export const getAdminDishes = (params) => api.get('/admin/dishes', { params });
export const createDish = (formData) =>
  api.post('/admin/dishes', formData, { headers: { 'Content-Type': 'multipart/form-data' } });
export const updateDish = (id, formData) =>
  api.post(`/admin/dishes/${id}`, formData, { headers: { 'Content-Type': 'multipart/form-data' } });
export const deleteDish = (id) => api.delete(`/admin/dishes/${id}`);

// Categories
export const getAdminCategories = () => api.get('/admin/categories');
export const createCategory = (data) => api.post('/admin/categories', data);
export const updateCategory = (id, data) => api.put(`/admin/categories/${id}`, data);
export const deleteCategory = (id) => api.delete(`/admin/categories/${id}`);

// Orders
export const getAdminOrders = (params) => api.get('/admin/orders', { params });
export const getAdminOrder = (id) => api.get(`/admin/orders/${id}`);
export const updateOrderStatus = (id, status) => api.patch(`/admin/orders/${id}/status`, { status });
export const bulkUpdateOrders = (ids, status) => api.patch('/admin/orders/bulk-status', { order_ids: ids, status });

// Inventory
export const getIngredients = (params) => api.get('/admin/inventory', { params });
export const createIngredient = (data) => api.post('/admin/inventory', data);
export const updateIngredient = (id, data) => api.put(`/admin/inventory/${id}`, data);
export const deleteIngredient = (id) => api.delete(`/admin/inventory/${id}`);
export const getIngredientCategories = () => api.get('/admin/inventory/categories');
export const createIngredientCategory = (data) => api.post('/admin/inventory/categories', data);

// Reports
export const getSalesReport = (params) => api.get('/admin/reports/sales', { params });
export const getTopSelling = (params) => api.get('/admin/reports/top-selling', { params });
export const exportSalesCsv = (params) =>
  api.get('/admin/reports/sales/export', { params, responseType: 'blob' });
export const exportTopSellingCsv = (params) =>
  api.get('/admin/reports/top-selling/export', { params, responseType: 'blob' });

// About CMS
export const getAdminAbout = () => api.get('/admin/about');
export const updateAboutHistory = (data) => api.put('/admin/about/history', data);
export const saveContact = (data) => api.post('/admin/about/contacts', data);
export const deleteContact = (id) => api.delete(`/admin/about/contacts/${id}`);
export const saveSocialLink = (data) => api.post('/admin/about/social-links', data);
export const deleteSocialLink = (id) => api.delete(`/admin/about/social-links/${id}`);
export const saveFaq = (data) => api.post('/admin/about/faqs', data);
export const deleteFaq = (id) => api.delete(`/admin/about/faqs/${id}`);

// Notifications
export const getNotifications = () => api.get('/admin/notifications');

// Admin accounts
export const getAdmins = () => api.get('/admin/admins');
export const createAdmin = (data) => api.post('/admin/admins', data);
export const deleteAdmin = (id) => api.delete(`/admin/admins/${id}`);
