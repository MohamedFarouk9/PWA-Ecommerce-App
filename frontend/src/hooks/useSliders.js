import { useQuery } from 'react-query';
import sliderService from '../services/sliderService';

const useSliders = (fetchType = 'active', params = null) => {
  const query = useQuery(
    ['sliders', fetchType, params],
    async () => {
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

      // Normalize response
      const raw = result?.data?.sliders || result?.sliders || result?.data || result;
      if (raw?.data && Array.isArray(raw.data)) {
        return raw.data;
      }
      return Array.isArray(raw) ? raw : [];
    },
    {
      enabled: !(['position', 'search', 'byId'].includes(fetchType) && !params),
      staleTime: 10 * 60 * 1000,
      cacheTime: 30 * 60 * 1000,
    }
  );

  return {
    data: query.data || [],
    loading: query.isLoading,
    error: query.error ? (query.error.message || 'Failed to fetch sliders') : null,
    refetch: query.refetch,
  };
};

export default useSliders;
