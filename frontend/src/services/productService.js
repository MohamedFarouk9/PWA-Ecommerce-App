import apiClient from './apiClient';

const productService = {
  // Get all published products
  getAllProducts: async () => {
    try {
      const response = await apiClient.get('/products');
      return response.data;
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to fetch products');
    }
  },

  // Get product by ID
  getProductById: async (id) => {
    try {
      const response = await apiClient.get(`/product/${id}`);
      return response.data;
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to fetch product');
    }
  },

  // Get product details with reviews and related info
  getProductDetails: async (id) => {
    try {
      const response = await apiClient.get(`/product-details/${id}`);
      return response.data;
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to fetch product details');
    }
  },

  // Get latest products
  getLatestProducts: async () => {
    try {
      const response = await apiClient.get('/latest-products');
      return response.data;
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to fetch latest products');
    }
  },

  // Get homepage sections with products
  getHomepageSections: async () => {
    try {
      const response = await apiClient.get('/products/homepage-sections');
      // API returns: { status: 'success', data: { sections: { ... } } }
      return response.data.data?.sections || {};
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to fetch homepage sections');
    }
  },

  // Get products by category
  getProductsByCategory: async (slug) => {
    if (!slug) {
      throw new Error('Category slug is required');
    }
    try {
      const response = await apiClient.get(`/products/category/${slug}`);
      return response.data;
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to fetch products by category');
    }
  },

  // Get products by remark
  getProductsByRemark: async (remark) => {
    if (!remark) {
      throw new Error('Remark is required');
    }
    try {
      const response = await apiClient.get(`/products/remark/${remark}`);
      return response.data;
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to fetch products by remark');
    }
  },

  // Search products
  searchProducts: async (query) => {
    if (!query) {
      throw new Error('Search query is required');
    }
    try {
      const response = await apiClient.get(`/products/search/${query}`);
      return response.data;
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to search products');
    }
  },

  // Get related products
  getRelatedProducts: async (productId) => {
    if (!productId) {
      throw new Error('Product ID is required');
    }
    try {
      const response = await apiClient.get(`/related-product/${productId}`);
      return response.data;
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to fetch related products');
    }
  },

  // Get products by section
  getProductsBySection: async (sectionId) => {
    if (!sectionId) {
      throw new Error('Section ID is required');
    }
    try {
      const response = await apiClient.get(`/products/section/${sectionId}`);
      return response.data;
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to fetch products by section');
    }
  },
};

export default productService;
