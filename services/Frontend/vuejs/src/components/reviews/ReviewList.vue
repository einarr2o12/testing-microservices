<template>
  <div class="review-list">
    <h2>Reviews</h2>
    <button @click="showCreateForm = true" class="btn btn-primary">Add New Review</button>
    
    <div v-if="showCreateForm" class="modal">
      <div class="modal-content">
        <h3>Create Review</h3>
        <form @submit.prevent="createReview">
          <div class="form-group">
            <label>Product:</label>
            <select v-model="newReview.product_id" required class="form-control">
              <option v-for="product in products" :key="product.id" :value="product.id">
                {{ product.name }}
              </option>
            </select>
          </div>
          <div class="form-group">
            <label>Rating:</label>
            <select v-model="newReview.rating" required class="form-control">
              <option v-for="n in 5" :key="n" :value="n">{{ n }} Stars</option>
            </select>
          </div>
          <div class="form-group">
            <label>Comments:</label>
            <textarea v-model="newReview.comment" required class="form-control"></textarea>
          </div>
          <div class="form-group">
            <label>Review Metadata (JSON):</label>
            <textarea v-model="newReview.review_metadata" class="form-control" placeholder="Optional JSON metadata"></textarea>
          </div>
          <button type="submit" class="btn btn-success">Create</button>
          <button type="button" @click="showCreateForm = false" class="btn btn-secondary">Cancel</button>
        </form>
      </div>
    </div>

    <div class="reviews-grid">
      <div v-for="review in reviews" :key="review.id" class="review-card">
        <div class="rating">
          <span v-for="n in 5" :key="n" class="star" :class="{ filled: n <= review.rating }">★</span>
        </div>
        <p class="product-name">Product: {{ getProductName(review.product_id) }}</p>
        <p class="comments">{{ review.comment }}</p>
        <p class="created">Created: {{ new Date(review.created_at).toLocaleDateString() }}</p>
        <div v-if="review.review_metadata && Object.keys(review.review_metadata).length > 0" class="metadata">
          <pre>{{ JSON.stringify(review.review_metadata, null, 2) }}</pre>
        </div>
        <div class="actions">
          <button @click="editReview(review)" class="btn btn-warning">Edit</button>
          <button @click="deleteReview(review.id)" class="btn btn-danger">Delete</button>
        </div>
      </div>
    </div>

    <div v-if="showEditForm" class="modal">
      <div class="modal-content">
        <h3>Edit Review</h3>
        <form @submit.prevent="updateReview">
          <div class="form-group">
            <label>Product:</label>
            <select v-model="editingReview.product_id" required class="form-control">
              <option v-for="product in products" :key="product.id" :value="product.id">
                {{ product.name }}
              </option>
            </select>
          </div>
          <div class="form-group">
            <label>Rating:</label>
            <select v-model="editingReview.rating" required class="form-control">
              <option v-for="n in 5" :key="n" :value="n">{{ n }} Stars</option>
            </select>
          </div>
          <div class="form-group">
            <label>Comments:</label>
            <textarea v-model="editingReview.comment" required class="form-control"></textarea>
          </div>
          <div class="form-group">
            <label>Review Metadata (JSON):</label>
            <textarea v-model="editingReview.review_metadata_string" class="form-control" placeholder="Optional JSON metadata"></textarea>
          </div>
          <button type="submit" class="btn btn-success">Update</button>
          <button type="button" @click="showEditForm = false" class="btn btn-secondary">Cancel</button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { reviewService, productService } from '@/services/api';

export default {
  name: 'ReviewList',
  data() {
    return {
      reviews: [],
      products: [],
      showCreateForm: false,
      showEditForm: false,
      newReview: {
        product_id: '',
        rating: 5,
        comment: '',
        review_metadata: ''
      },
      editingReview: null
    };
  },
  async created() {
    await Promise.all([
      this.loadReviews(),
      this.loadProducts()
    ]);
  },
  methods: {
    async loadReviews() {
      try {
        const response = await reviewService.getAll();
        this.reviews = response.data;
      } catch (error) {
        console.error('Error loading reviews:', error);
        alert('Failed to load reviews');
      }
    },
    async loadProducts() {
      try {
        const response = await productService.getAll();
        this.products = response.data;
      } catch (error) {
        console.error('Error loading products:', error);
        alert('Failed to load products');
      }
    },
    getProductName(productId) {
      const product = this.products.find(p => p.id === productId);
      return product ? product.name : 'Unknown';
    },
    async createReview() {
      try {
        let reviewData = {
          product_id: this.newReview.product_id,
          rating: this.newReview.rating,
          comment: this.newReview.comment
        };
        
        // Handle metadata if provided
        if (this.newReview.review_metadata) {
          try {
            reviewData.review_metadata = JSON.parse(this.newReview.review_metadata);
          } catch (e) {
            alert('Invalid JSON in metadata field');
            return;
          }
        } else {
          reviewData.review_metadata = {};
        }
        
        await reviewService.create(reviewData);
        this.showCreateForm = false;
        this.newReview = {
          product_id: '',
          rating: 5,
          comment: '',
          review_metadata: ''
        };
        await this.loadReviews();
      } catch (error) {
        console.error('Error creating review:', error);
        alert('Failed to create review');
      }
    },
    editReview(review) {
      this.editingReview = {
        ...review,
        review_metadata_string: JSON.stringify(review.review_metadata || {}, null, 2)
      };
      this.showEditForm = true;
    },
    async updateReview() {
      try {
        let reviewData = {
          id: this.editingReview.id,
          product_id: this.editingReview.product_id,
          rating: this.editingReview.rating,
          comment: this.editingReview.comment
        };

        // Handle metadata if provided
        if (this.editingReview.review_metadata_string) {
          try {
            reviewData.review_metadata = JSON.parse(this.editingReview.review_metadata_string);
          } catch (e) {
            alert('Invalid JSON in metadata field');
            return;
          }
        } else {
          reviewData.review_metadata = {};
        }
        
        await reviewService.update(this.editingReview.id, reviewData);
        this.showEditForm = false;
        this.editingReview = null;
        await this.loadReviews();
      } catch (error) {
        console.error('Error updating review:', error);
        alert('Failed to update review');
      }
    },
    async deleteReview(id) {
      if (confirm('Are you sure you want to delete this review?')) {
        try {
          await reviewService.delete(id);
          await this.loadReviews();
        } catch (error) {
          console.error('Error deleting review:', error);
          alert('Failed to delete review');
        }
      }
    }
  }
};
</script>

<style scoped>
.review-list {
  padding: 20px;
}

.reviews-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.review-card {
  border: 1px solid #ddd;
  padding: 15px;
  border-radius: 8px;
  background: white;
}

.rating {
  margin-bottom: 10px;
}

.star {
  color: #ddd;
  font-size: 20px;
}

.star.filled {
  color: #ffd700;
}

.product-name {
  font-weight: bold;
  margin-bottom: 10px;
}

.comments {
  margin-bottom: 10px;
}

.created {
  color: #666;
  font-size: 0.9em;
  margin-bottom: 10px;
}

.metadata {
  background: #f5f5f5;
  padding: 10px;
  border-radius: 4px;
  margin: 10px 0;
  font-family: monospace;
  font-size: 0.9em;
}

.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 20px;
  border-radius: 8px;
  width: 90%;
  max-width: 500px;
}

.form-group {
  margin-bottom: 15px;
}

.form-control {
  width: 100%;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.btn {
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  margin-right: 8px;
}

.btn-primary { background: #007bff; color: white; }
.btn-success { background: #28a745; color: white; }
.btn-warning { background: #ffc107; color: black; }
.btn-danger { background: #dc3545; color: white; }
.btn-secondary { background: #6c757d; color: white; }
</style>