import { useState, useEffect } from 'react';
import productService from '../services/productService';

const useProducts = (fetchType = 'all', params = null) => {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    // Skip fetching if fetch type requires params but params is not provided
    if (
      ['category', 'remark', 'search', 'related', 'section'].includes(fetchType) &&
      !params
    ) {
      setLoading(false);
      return;
    }

    const fetchData = async () => {
      try {
        setLoading(true);
        setError(null);

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

        console.log(`useProducts (${fetchType}):`, result);
        setData(result);
      } catch (err) {
        setError(err.message);
        console.error('Error fetching products:', err);
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [fetchType, params]);

  return { data, loading, error };
};

export default useProducts;
