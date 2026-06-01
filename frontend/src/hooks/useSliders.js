import { useState, useEffect } from 'react';
import sliderService from '../services/sliderService';

const useSliders = (fetchType = 'active', params = null) => {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    // Skip fetching if fetch type requires params but params is not provided
    if (['position', 'search', 'byId'].includes(fetchType) && !params) {
      setLoading(false);
      return;
    }

    const fetchData = async () => {
      try {
        setLoading(true);
        setError(null);

        let result;
        switch (fetchType) {
          case 'all':
            result = await sliderService.getAllSliders();
            break;
          case 'featured':
            result = await sliderService.getFeaturedSliders();
            break;
          case 'position':
            result = await sliderService.getSlidersByPosition(params);
            break;
          case 'search':
            result = await sliderService.searchSliders(params);
            break;
          case 'byId':
            result = await sliderService.getSliderById(params);
            break;
          default:
            result = await sliderService.getActiveSliders();
        }

        setData(result);
      } catch (err) {
        setError(err.message);
        console.error('Error fetching sliders:', err);
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [fetchType, params]);

  return { data, loading, error };
};

export default useSliders;
