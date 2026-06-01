import apiClient from './apiClient';

const sliderService = {
  // Get all sliders
  getAllSliders: async () => {
    try {
      const response = await apiClient.get('/sliders');
      return response.data;
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to fetch sliders');
    }
  },

  // Get active sliders
  getActiveSliders: async () => {
    try {
      const response = await apiClient.get('/sliders/active');
      return response.data;
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to fetch active sliders');
    }
  },

  // Get featured sliders
  getFeaturedSliders: async () => {
    try {
      const response = await apiClient.get('/sliders/featured');
      return response.data;
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to fetch featured sliders');
    }
  },

  // Get sliders by position
  getSlidersByPosition: async (position) => {
    if (!position) {
      throw new Error('Position is required');
    }
    try {
      const response = await apiClient.get(`/sliders/position/${position}`);
      return response.data;
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to fetch sliders by position');
    }
  },

  // Search sliders
  searchSliders: async (query) => {
    if (!query) {
      throw new Error('Search query is required');
    }
    try {
      const response = await apiClient.get('/sliders/search', {
        params: { q: query },
      });
      return response.data;
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to search sliders');
    }
  },

  // Get slider by ID
  getSliderById: async (id) => {
    if (!id) {
      throw new Error('Slider ID is required');
    }
    try {
      const response = await apiClient.get(`/sliders/${id}`);
      return response.data;
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to fetch slider');
    }
  },
};

export default sliderService;
