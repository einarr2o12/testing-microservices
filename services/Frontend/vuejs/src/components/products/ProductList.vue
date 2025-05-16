<template>
  <div class="product-list">
    <h2>Products</h2>
    <button @click="showCreateForm = true" class="btn btn-primary">Add New Product</button>
    
    <div v-if="showCreateForm" class="modal">
      <div class="modal-content">
        <h3>Create Product</h3>
        <form @submit.prevent="createProduct">
          <div class="form-group">
            <label>Name:</label>
            <input v-model="newProduct.name" required class="form-control">
          </div>
          <div class="form-group">
            <label>Description:</label>
            <textarea v-model="newProduct.description" required class="form-control"></textarea>
          </div>
          <div class="form-group">
            <label>Price:</label>
            <input type="number" v-model="newProduct.price" required class="form-control" step="0.01">
          </div>
          <div class="form-group">
            <label>Category:</label>
            <select v-model="newProduct.categoryId" required class="form-control">
              <option v-for="category in categories" :key="category._id" :value="category._id">
                {{ category.name }}
              </option>
            </select>
          </div>
          <button type="submit" class="btn btn-success">Create</button>
          <button type="button" @click="showCreateForm = false" class="btn btn-secondary">Cancel</button>
        </form>
      </div>
    </div>

    <div class="products-grid">
      <div v-for="product in products" :key="product.id" class="product-card">
        <h3>{{ product.name }}</h3>
        <p>{{ product.description }}</p>
        <p>Price: ${{ product.price }}</p>
        <p>Category: {{ getCategoryName(product.category_id) }}</p>
        <p>Created: {{ new Date(product.created_at).toLocaleDateString() }}</p>
        <div class="actions">
          <button @click="editProduct(product)" class="btn btn-warning">Edit</button>
          <button @click="deleteProduct(product.id)" class="btn btn-danger">Delete</button>
        </div>
      </div>
    </div>

    <div v-if="showEditForm" class="modal">
      <div class="modal-content">
        <h3>Edit Product</h3>
        <form @submit.prevent="updateProduct">
          <div class="form-group">
            <label>Name:</label>
            <input v-model="editingProduct.name" required class="form-control">
          </div>
          <div class="form-group">
            <label>Description:</label>
            <textarea v-model="editingProduct.description" required class="form-control"></textarea>
          </div>
          <div class="form-group">
            <label>Price:</label>
            <input type="number" v-model="editingProduct.price" required class="form-control" step="0.01">
          </div>
          <div class="form-group">
            <label>Category:</label>
            <select v-model="editingProduct.category_id" required class="form-control">
              <option v-for="category in categories" :key="category._id" :value="category._id">
                {{ category.name }}
              </option>
            </select>
          </div>
          <button type="submit" class="btn btn-success">Update</button>
          <button type="button" @click="showEditForm = false" class="btn btn-secondary">Cancel</button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { productService, categoryService } from '@/services/api';

export default {
  name: 'ProductList',
  data() {
    return {
      products: [],
      categories: [],
      showCreateForm: false,
      showEditForm: false,
      newProduct: {
        name: '',
        description: '',
        price: 0,
        category_id: ''
      },
      editingProduct: null
    };
  },
  async created() {
    await Promise.all([
      this.loadProducts(),
      this.loadCategories()
    ]);
  },
  methods: {
    async loadProducts() {
      try {
        const response = await productService.getAll();
        this.products = response.data;
      } catch (error) {
        console.error('Error loading products:', error);
        alert('Failed to load products');
      }
    },
    async loadCategories() {
      try {
        const response = await categoryService.getAll();
        this.categories = response.data;
      } catch (error) {
        console.error('Error loading categories:', error);
        alert('Failed to load categories');
      }
    },
    getCategoryName(categoryId) {
      const category = this.categories.find(c => c._id === categoryId);
      return category ? category.name : 'Unknown';
    },
    async createProduct() {
      try {
        await productService.create(this.newProduct);
        this.showCreateForm = false;
        this.newProduct = {
          name: '',
          description: '',
          price: 0,
          category_id: ''
        };
        await this.loadProducts();
      } catch (error) {
        console.error('Error creating product:', error);
        alert('Failed to create product');
      }
    },
    editProduct(product) {
      this.editingProduct = { ...product };
      this.showEditForm = true;
    },
    async updateProduct() {
      try {
        await productService.update(this.editingProduct.id, this.editingProduct);
        this.showEditForm = false;
        this.editingProduct = null;
        await this.loadProducts();
      } catch (error) {
        console.error('Error updating product:', error);
        alert('Failed to update product');
      }
    },
    async deleteProduct(id) {
      if (confirm('Are you sure you want to delete this product?')) {
        try {
          await productService.delete(id);
          await this.loadProducts();
        } catch (error) {
          console.error('Error deleting product:', error);
          alert('Failed to delete product');
        }
      }
    }
  }
};
</script>

<style scoped>
.product-list {
  padding: 20px;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.product-card {
  border: 1px solid #ddd;
  padding: 15px;
  border-radius: 8px;
  background: white;
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