<template>
  <div class="category-list">
    <h2>Categories</h2>
    <button @click="showCreateForm = true" class="btn btn-primary">Add New Category</button>
    
    <div v-if="showCreateForm" class="modal">
      <div class="modal-content">
        <h3>Create Category</h3>
        <form @submit.prevent="createCategory">
          <div class="form-group">
            <label>Name:</label>
            <input v-model="newCategory.name" required class="form-control">
          </div>
          <div class="form-group">
            <label>Description:</label>
            <textarea v-model="newCategory.description" required class="form-control"></textarea>
          </div>
          <button type="submit" class="btn btn-success">Create</button>
          <button type="button" @click="showCreateForm = false" class="btn btn-secondary">Cancel</button>
        </form>
      </div>
    </div>

    <div class="categories-grid">
      <div v-for="category in categories" :key="category._id" class="category-card">
        <h3>{{ category.name }}</h3>
        <p>{{ category.description }}</p>
        <p>Created: {{ new Date(category.createdAt).toLocaleDateString() }}</p>
        <div class="actions">
          <button @click="editCategory(category)" class="btn btn-warning">Edit</button>
          <button @click="deleteCategory(category._id)" class="btn btn-danger">Delete</button>
        </div>
      </div>
    </div>

    <div v-if="showEditForm" class="modal">
      <div class="modal-content">
        <h3>Edit Category</h3>
        <form @submit.prevent="updateCategory">
          <div class="form-group">
            <label>Name:</label>
            <input v-model="editingCategory.name" required class="form-control">
          </div>
          <div class="form-group">
            <label>Description:</label>
            <textarea v-model="editingCategory.description" required class="form-control"></textarea>
          </div>
          <button type="submit" class="btn btn-success">Update</button>
          <button type="button" @click="showEditForm = false" class="btn btn-secondary">Cancel</button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { categoryService } from '@/services/api';

export default {
  name: 'CategoryList',
  data() {
    return {
      categories: [],
      showCreateForm: false,
      showEditForm: false,
      newCategory: {
        name: '',
        description: ''
      },
      editingCategory: null
    };
  },
  async created() {
    await this.loadCategories();
  },
  methods: {
    async loadCategories() {
      try {
        const response = await categoryService.getAll();
        this.categories = response.data;
      } catch (error) {
        console.error('Error loading categories:', error);
        alert('Failed to load categories');
      }
    },
    async createCategory() {
      try {
        await categoryService.create(this.newCategory);
        this.showCreateForm = false;
        this.newCategory = { name: '', description: '' };
        await this.loadCategories();
      } catch (error) {
        console.error('Error creating category:', error);
        alert('Failed to create category');
      }
    },
    editCategory(category) {
      this.editingCategory = { ...category };
      this.showEditForm = true;
    },
    async updateCategory() {
      try {
        await categoryService.update(this.editingCategory._id, this.editingCategory);
        this.showEditForm = false;
        this.editingCategory = null;
        await this.loadCategories();
      } catch (error) {
        console.error('Error updating category:', error);
        alert('Failed to update category');
      }
    },
    async deleteCategory(id) {
      if (confirm('Are you sure you want to delete this category?')) {
        try {
          await categoryService.delete(id);
          await this.loadCategories();
        } catch (error) {
          console.error('Error deleting category:', error);
          alert('Failed to delete category');
        }
      }
    }
  }
};
</script>

<style scoped>
.category-list {
  padding: 20px;
}

.categories-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.category-card {
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