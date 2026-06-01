import React, { lazy, Suspense, useEffect } from 'react';
import useProducts from '../hooks/useProducts';
import useSliders from '../hooks/useSliders';
import useCategories from '../hooks/useCategories';
import { PageLoadingFallback } from '../components/ui/LoadingStates';
import ProductSection from '../components/Products/ProductSection';

// Layout Components - Lazy loaded with error handling
const NavMenuDesktop = lazy(() =>
  import('../components/common/NavMenuDesktop').catch((err) => {
    console.error('Error loading NavMenuDesktop:', err);
    return { default: () => <div>Error loading navigation</div> };
  })
);
const NavMenuMobile = lazy(() =>
  import('../components/common/NavMenuMoblie').catch((err) => {
    console.error('Error loading NavMenuMobile:', err);
    return { default: () => <div>Error loading navigation</div> };
  })
);
const FooterDesktop = lazy(() =>
  import('../components/common/FooterDesktop').catch((err) => {
    console.error('Error loading FooterDesktop:', err);
    return { default: () => null };
  })
);
const FooterMobile = lazy(() =>
  import('../components/common/FooterMobile').catch((err) => {
    console.error('Error loading FooterMobile:', err);
    return { default: () => null };
  })
);

// Home Components - Lazy loaded with error handling
const HomeTop = lazy(() =>
  import('../components/home/HomeTop').catch((err) => {
    console.error('Error loading HomeTop:', err);
    return { default: () => <div>Error loading hero section</div> };
  })
);
const HomeTopMobile = lazy(() =>
  import('../components/home/HomeTopMobile').catch((err) => {
    console.error('Error loading HomeTopMobile:', err);
    return { default: () => <div>Error loading hero section</div> };
  })
);
const Categories = lazy(() =>
  import('../components/home/Categories').catch((err) => {
    console.error('Error loading Categories:', err);
    return { default: () => null };
  })
);

/**
 * HomePage Component - Main landing page with product sections from backend
 * Architecture:
 * - Uses custom hooks (useProducts, useSliders, useCategories) for data fetching
 * - Implements service layer pattern for API calls
 * - Lazy loads components for performance optimization
 * - Proper error handling and loading states
 * - Reusable ProductSection component for dynamic sections
 */
const HomePage = () => {
  // Fetch data using custom hooks
  const {
    data: sectionsData,
    loading: sectionsLoading,
    error: sectionsError,
  } = useProducts('sections');
  const { data: categoriesData, loading: categoriesLoading } = useCategories();
  const { data: slidersData } = useSliders('active');

  // Track visitor on mount
  useEffect(() => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
    // Optional: Add visitor tracking here if backend endpoint exists
  }, []);

  /**
   * Render desktop navigation and hero section
   */
  const renderDesktopLayout = () => (
    <div className="Desktop">
      <Suspense fallback={<PageLoadingFallback />}>
        <NavMenuDesktop />
        <HomeTop />
      </Suspense>
    </div>
  );

  /**
   * Render mobile navigation and hero section
   */
  const renderMobileLayout = () => (
    <div className="Mobile">
      <Suspense fallback={<PageLoadingFallback />}>
        <NavMenuMobile />
        <HomeTopMobile />
      </Suspense>
    </div>
  );

  /**
   * Render categories section
   */
  const renderCategoriesSection = () => (
    <Suspense fallback={null}>
      <Categories />
    </Suspense>
  );

  /**
   * Render dynamic product sections from backend
   * Maps through all sections returned from API and displays them using ProductSection component
   * Each section contains products grouped by category/section name
   */
  const renderProductSections = () => {
    if (sectionsError) {
      console.error('Sections error:', sectionsError);
      return null;
    }

    if (!sectionsData || Object.keys(sectionsData).length === 0) {
      return null;
    }

    return Object.entries(sectionsData).map(([sectionName, products]) => {
      // Ensure products is an array
      const productArray = Array.isArray(products) ? products : [];
      
      return (
        <ProductSection
          key={sectionName}
          title={sectionName
            .replace(/_/g, ' ')
            .replace(/\b\w/g, (l) => l.toUpperCase())}
          products={productArray}
          loading={sectionsLoading}
          error={sectionsError}
        />
      );
    });
  };

  /**
   * Render footer section
   */
  const renderFooter = () => (
    <>
      <div className="Desktop">
        <Suspense fallback={null}>
          <FooterDesktop />
        </Suspense>
      </div>
      <div className="Mobile">
        <Suspense fallback={null}>
          <FooterMobile />
        </Suspense>
      </div>
    </>
  );

  return (
    <>
      {renderDesktopLayout()}
      {renderMobileLayout()}
      {renderCategoriesSection()}
      {renderProductSections()}
      {renderFooter()}
    </>
  );
};

export default HomePage;