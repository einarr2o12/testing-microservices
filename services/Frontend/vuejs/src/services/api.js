import axios from 'axios';

const API_PREFIX = '/api';

const api = axios.create({
  baseURL: API_PREFIX,
  headers: {
    'Content-Type': 'application/json'
  }
});

// Use environment variables for service base URLs (with fallbacks)
const CATEGORY_SERVICE_URL = process.env.VUE_APP_CATEGORY_SERVICE_URL || '';
const PRODUCT_SERVICE_URL = process.env.VUE_APP_PRODUCT_SERVICE_URL || '';
const REVIEW_SERVICE_URL = process.env.VUE_APP_REVIEW_SERVICE_URL || '';

// Export the appropriate API based on USE_MOCK flag
export const categoryService = {
  getAll: () => api.get(`${CATEGORY_SERVICE_URL}/categories`),
  getById: (id) => api.get(`${CATEGORY_SERVICE_URL}/categories/${id}`),
  create: (category) => api.post(`${CATEGORY_SERVICE_URL}/categories`, category),
  update: (id, category) => api.put(`${CATEGORY_SERVICE_URL}/categories/${id}`, category),
  delete: (id) => api.delete(`${CATEGORY_SERVICE_URL}/categories/${id}`)
};

export const productService = {
  getAll: () => api.get(`${PRODUCT_SERVICE_URL}/products`),
  getById: (id) => api.get(`${PRODUCT_SERVICE_URL}/products/${id}`),
  create: (product) => api.post(`${PRODUCT_SERVICE_URL}/products`, product),
  update: (id, product) => api.put(`${PRODUCT_SERVICE_URL}/products/${id}`, product),
  delete: (id) => api.delete(`${PRODUCT_SERVICE_URL}/products/${id}`),
  getByCategory: (categoryId) => api.get(`${PRODUCT_SERVICE_URL}/products?categoryId=${categoryId}`)
};

export const reviewService = {
  getAll: () => api.get(`${REVIEW_SERVICE_URL}/reviews`),
  getById: (id) => api.get(`${REVIEW_SERVICE_URL}/reviews/${id}`),
  create: (review) => api.post(`${REVIEW_SERVICE_URL}/reviews`, review),
  update: (id, review) => api.put(`${REVIEW_SERVICE_URL}/reviews/${id}`, review),
  delete: (id) => api.delete(`${REVIEW_SERVICE_URL}/reviews/${id}`),
  getByProduct: (productId) => api.get(`${REVIEW_SERVICE_URL}/reviews?productId=${productId}`)
};