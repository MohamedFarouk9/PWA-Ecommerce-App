import { useQuery } from 'react-query';
import productService from '../services/productService';

const useProducts = (fetchType = 'all', params = null) => {
  const query = useQuery(
    ['products', fetchType, params],
    async () => {
      let result;
      switch (fetchType) {
        case 'latest':
          result = await productService.getLatestProducts();
          break;
        case 'sections':
          result = await productService.getHomepageSections();
          break;
        case 'category':
          result = await productService.getProductsByCategory(params);
          break;
        case 'remark':
          result = await productService.getProductsByRemark(params);
          break;
        case 'search':
          result = await productService.searchProducts(params);
          break;
        case 'related':
          result = await productService.getRelatedProducts(params);
          break;
        case 'section':
          result = await productService.getProductsBySection(params);
          break;
        default:
          result = await productService.getAllProducts();
      }

      return result?.data !== undefined ? result.data : result;
    },
    {
      enabled: !(['category', 'remark', 'search', 'related', 'section'].includes(fetchType) && !params),
      staleTime: 5 * 60 * 1000,
      cacheTime: 15 * 60 * 1000,
    }
  );

  return {
    data: query.data,
    loading: query.isLoading,
    error: query.error ? (query.error.message || 'Failed to fetch products') : null,
    refetch: query.refetch,
  };
};

export default useProducts;
