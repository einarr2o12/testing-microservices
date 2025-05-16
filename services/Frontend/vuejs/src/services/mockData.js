// Mock data for testing
export const mockCategories = [
  {
    _id: '1',
    name: 'Electronics',
    description: 'Electronic devices and accessories',
    createdAt: '2024-01-01T00:00:00.000Z'
  },
  {
    _id: '2',
    name: 'Clothing',
    description: 'Fashion and apparel',
    createdAt: '2024-01-02T00:00:00.000Z'
  },
  {
    _id: '3',
    name: 'Books',
    description: 'Books and publications',
    createdAt: '2024-01-03T00:00:00.000Z'
  }
];

export const mockProducts = [
  {
    _id: '1',
    name: 'Smartphone',
    description: 'Latest model smartphone',
    price: 999.99,
    categoryId: '1',
    createdAt: '2024-01-04T00:00:00.000Z'
  },
  {
    _id: '2',
    name: 'T-Shirt',
    description: 'Cotton t-shirt',
    price: 29.99,
    categoryId: '2',
    createdAt: '2024-01-05T00:00:00.000Z'
  },
  {
    _id: '3',
    name: 'Programming Book',
    description: 'Learn programming',
    price: 49.99,
    categoryId: '3',
    createdAt: '2024-01-06T00:00:00.000Z'
  }
];

export const mockReviews = [
  {
    _id: '1',
    productId: '1',
    rating: 5,
    comments: 'Great smartphone!',
    review_metadata: {
      verified_purchase: true,
      helpful_votes: 10
    },
    createdAt: '2024-01-07T00:00:00.000Z'
  },
  {
    _id: '2',
    productId: '2',
    rating: 4,
    comments: 'Good quality t-shirt',
    review_metadata: null,
    createdAt: '2024-01-08T00:00:00.000Z'
  },
  {
    _id: '3',
    productId: '3',
    rating: 5,
    comments: 'Excellent book for beginners',
    review_metadata: {
      verified_purchase: true,
      helpful_votes: 5,
      reading_level: 'beginner'
    },
    createdAt: '2024-01-09T00:00:00.000Z'
  }
]; 