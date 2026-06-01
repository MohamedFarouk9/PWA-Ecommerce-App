import apiClient from './apiClient';

const categoryService = {
  // Get all categories
  getAllCategories: async () => {
    try {
      const response = await apiClient.get('/categories');
      return response.data;
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to fetch categories');
    }
  },

  // Get category by slug
  getCategoryBySlug: async (slug) => {
    if (!slug) {
      throw new Error('Category slug is required');
    }
    try {
      const response = await apiClient.get(`/categories/${slug}`);
      return response.data;
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to fetch category');
    }
  },

  // Get category products
  getCategoryProducts: async (slug) => {
    if (!slug) {
      throw new Error('Category slug is required');
    }
    try {
      const response = await apiClient.get(`/categories/${slug}/products`);
      return response.data;
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to fetch category products');
    }
  },

  // Get category breadcrumb
  getCategoryBreadcrumb: async (slug) => {
    if (!slug) {
      throw new Error('Category slug is required');
    }
    try {
      const response = await apiClient.get(`/categories/${slug}/breadcrumb`);
      return response.data;
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to fetch breadcrumb');
    }
  },

  // Search categories
  searchCategories: async (query) => {
    if (!query) {
      throw new Error('Search query is required');
    }
    try {
      const response = await apiClient.get('/categories/search', {
        params: { q: query },
      });
      return response.data;
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to search categories');
    }
  },
};

export default categoryService;
