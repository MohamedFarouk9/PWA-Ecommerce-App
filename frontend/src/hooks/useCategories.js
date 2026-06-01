import { useState, useEffect } from 'react';
import categoryService from '../services/categoryService';

const useCategories = (fetchType = 'all', params = null) => {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    // Skip fetching if fetch type requires params but params is not provided
    if (['slug', 'products', 'breadcrumb', 'search'].includes(fetchType) && !params) {
      setLoading(false);
      return;
    }

    const fetchData = async () => {
      try {
        setLoading(true);
        setError(null);

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

        setData(result);
      } catch (err) {
        setError(err.message);
        console.error('Error fetching categories:', err);
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [fetchType, params]);

  return { data, loading, error };
};

export default useCategories;
