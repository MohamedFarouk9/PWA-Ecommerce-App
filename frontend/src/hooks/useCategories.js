import { useQuery } from 'react-query';
import categoryService from '../services/categoryService';

const useCategories = (fetchType = 'all', params = null) => {
  const query = useQuery(
    ['categories', fetchType, params],
    async () => {
      let result;
      switch (fetchType) {
        case 'slug':
          result = await categoryService.getCategoryBySlug(params);
          break;
        case 'products':
          result = await categoryService.getCategoryProducts(params);
          break;
        case 'breadcrumb':
          result = await categoryService.getCategoryBreadcrumb(params);
          break;
        case 'search':
          result = await categoryService.searchCategories(params);
          break;
        default:
          result = await categoryService.getAllCategories();
      }

      // Normalize response structure
      if (result?.data?.categories) {
        return result.data.categories;
      }
      if (result?.categories) {
        return result.categories;
      }
      if (result?.data && Array.isArray(result.data)) {
        return result.data;
      }
      return result?.data || result || [];
    },
    {
      enabled: !(['slug', 'products', 'breadcrumb', 'search'].includes(fetchType) && !params),
      staleTime: 10 * 60 * 1000, // 10 minutes cache
      cacheTime: 30 * 60 * 1000,
    }
  );

  return {
    data: query.data,
    loading: query.isLoading,
    error: query.error ? (query.error.message || 'Failed to fetch categories') : null,
    refetch: query.refetch,
  };
};

export default useCategories;
