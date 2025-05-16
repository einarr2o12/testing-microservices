import axios from 'axios';
import { mockCategories, mockProducts, mockReviews } from './mockData';

const USE_MOCK = false; // Set this to false to use real API
const API_PREFIX = '/api';

const api = axios.create({
  baseURL: API_PREFIX,
  headers: {
    'Content-Type': 'application/json'
  }
});

// Mock API responses
const mockApi = {
  categories: {
    getAll: () => Promise.resolve({ data: mockCategories }),
    getById: (id) => Promise.resolve({ data: mockCategories.find(c => c._id === id) }),
    create: (category) => {
      const newCategory = {
        ...category,
        _id: String(mockCategories.length + 1),
        createdAt: new Date().toISOString()
      };
      mockCategories.push(newCategory);
      return Promise.resolve({ data: newCategory });
    },
    update: (id, category) => {
      const index = mockCategories.findIndex(c => c._id === id);
      if (index !== -1) {
        mockCategories[index] = { ...mockCategories[index], ...category };
        return Promise.resolve({ data: mockCategories[index] });
      }
      return Promise.reject(new Error('Category not found'));
    },
    delete: (id) => {
      const index = mockCategories.findIndex(c => c._id === id);
      if (index !== -1) {
        mockCategories.splice(index, 1);
        return Promise.resolve({ data: { success: true } });
      }
      return Promise.reject(new Error('Category not found'));
    }
  },
  products: {
    getAll: () => Promise.resolve({ data: mockProducts }),
    getById: (id) => Promise.resolve({ data: mockProducts.find(p => p._id === id) }),
    create: (product) => {
      const newProduct = {
        ...product,
        _id: String(mockProducts.length + 1),
        createdAt: new Date().toISOString()
      };
      mockProducts.push(newProduct);
      return Promise.resolve({ data: newProduct });
    },
    update: (id, product) => {
      const index = mockProducts.findIndex(p => p._id === id);
      if (index !== -1) {
        mockProducts[index] = { ...mockProducts[index], ...product };
        return Promise.resolve({ data: mockProducts[index] });
      }
      return Promise.reject(new Error('Product not found'));
    },
    delete: (id) => {
      const index = mockProducts.findIndex(p => p._id === id);
      if (index !== -1) {
        mockProducts.splice(index, 1);
        return Promise.resolve({ data: { success: true } });
      }
      return Promise.reject(new Error('Product not found'));
    },
    getByCategory: (categoryId) => Promise.resolve({
      data: mockProducts.filter(p => p.categoryId === categoryId)
    })
  },
  reviews: {
    getAll: () => Promise.resolve({ data: mockReviews }),
    getById: (id) => Promise.resolve({ data: mockReviews.find(r => r._id === id) }),
    create: (review) => {
      const newReview = {
        ...review,
        _id: String(mockReviews.length + 1),
        createdAt: new Date().toISOString()
      };
      mockReviews.push(newReview);
      return Promise.resolve({ data: newReview });
    },
    update: (id, review) => {
      const index = mockReviews.findIndex(r => r._id === id);
      if (index !== -1) {
        mockReviews[index] = { ...mockReviews[index], ...review };
        return Promise.resolve({ data: mockReviews[index] });
      }
      return Promise.reject(new Error('Review not found'));
    },
    delete: (id) => {
      const index = mockReviews.findIndex(r => r._id === id);
      if (index !== -1) {
        mockReviews.splice(index, 1);
        return Promise.resolve({ data: { success: true } });
      }
      return Promise.reject(new Error('Review not found'));
    },
    getByProduct: (productId) => Promise.resolve({
      data: mockReviews.filter(r => r.productId === productId)
    })
  }
};

// Export the appropriate API based on USE_MOCK flag
export const categoryService = USE_MOCK ? mockApi.categories : {
  getAll: () => api.get('/categories'),
  getById: (id) => api.get(`/categories/${id}`),
  create: (category) => api.post('/categories', category),
  update: (id, category) => api.put(`/categories/${id}`, category),
  delete: (id) => api.delete(`/categories/${id}`)
};

export const productService = USE_MOCK ? mockApi.products : {
  getAll: () => api.get('/products'),
  getById: (id) => api.get(`/products/${id}`),
  create: (product) => api.post('/products', product),
  update: (id, product) => api.put(`/products/${id}`, product),
  delete: (id) => api.delete(`/products/${id}`),
  getByCategory: (categoryId) => api.get(`/products?categoryId=${categoryId}`)
};

export const reviewService = USE_MOCK ? mockApi.reviews : {
  getAll: () => api.get('/reviews'),
  getById: (id) => api.get(`/reviews/${id}`),
  create: (review) => api.post('/reviews', review),
  update: (id, review) => api.put(`/reviews/${id}`, review),
  delete: (id) => api.delete(`/reviews/${id}`),
  getByProduct: (productId) => api.get(`/reviews?productId=${productId}`)
}; 