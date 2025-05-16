<template>
  <div id="app">
    <nav class="navbar">
      <div class="nav-brand">Product Management</div>
      <div class="nav-links">
        <button @click="currentView = 'categories'" class="nav-link" :class="{ active: currentView === 'categories' }">
          Categories
        </button>
        <button @click="currentView = 'products'" class="nav-link" :class="{ active: currentView === 'products' }">
          Products
        </button>
        <button @click="currentView = 'reviews'" class="nav-link" :class="{ active: currentView === 'reviews' }">
          Reviews
        </button>
      </div>
    </nav>

    <main class="main-content">
      <component :is="currentComponent"></component>
    </main>
  </div>
</template>

<script>
import CategoryList from './components/categories/CategoryList.vue';
import ProductList from './components/products/ProductList.vue';
import ReviewList from './components/reviews/ReviewList.vue';

export default {
  name: 'App',
  components: {
    CategoryList,
    ProductList,
    ReviewList
  },
  data() {
    return {
      currentView: 'categories'
    };
  },
  computed: {
    currentComponent() {
      switch (this.currentView) {
        case 'categories':
          return 'CategoryList';
        case 'products':
          return 'ProductList';
        case 'reviews':
          return 'ReviewList';
        default:
          return 'CategoryList';
      }
    }
  }
};
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: Arial, sans-serif;
  line-height: 1.6;
  background-color: #f8f9fa;
}

#app {
  min-height: 100vh;
}

.navbar {
  background-color: #343a40;
  padding: 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: white;
}

.nav-brand {
  font-size: 1.5rem;
  font-weight: bold;
}

.nav-links {
  display: flex;
  gap: 1rem;
}

.nav-link {
  background: none;
  border: none;
  color: white;
  padding: 0.5rem 1rem;
  cursor: pointer;
  border-radius: 4px;
  transition: background-color 0.3s;
}

.nav-link:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.nav-link.active {
  background-color: #007bff;
}

.main-content {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}
</style> 