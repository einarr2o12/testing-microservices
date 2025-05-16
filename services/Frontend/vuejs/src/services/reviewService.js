import api from './api';

export const reviewService = {
  getAll() {
    return api.get('/reviews');
  },
  
  getById(id) {
    return api.get(`/reviews/${id}`);
  },
  
  create(review) {
    return api.post('/reviews', review);
  },
  
  update(id, review) {
    return api.put(`/reviews/${id}`, review);
  },
  
  delete(id) {
    return api.delete(`/reviews/${id}`);
  },
  
  getByProduct(productId) {
    return api.get(`/reviews?productId=${productId}`);
  }
}; 