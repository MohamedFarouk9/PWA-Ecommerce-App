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
 * HomePage Component - Optimized landing page with centralized caching & prop-sharing
 */
const HomePage = () => {
  // Fetch unified data with React Query caching (prevents duplicate waterfalls)
  const {
    data: sectionsData,
    loading: sectionsLoading,
    error: sectionsError,
  } = useProducts('sections');
  const { data: categoriesData } = useCategories('all');
  const { data: slidersData } = useSliders('active');

  // Scroll to top on mount
  useEffect(() => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }, []);

  /**
   * Render desktop navigation and hero section
   */
  const renderDesktopLayout = () => (
    <div className="Desktop">
      <Suspense fallback={<PageLoadingFallback />}>
        <NavMenuDesktop categories={categoriesData} />
        <HomeTop categories={categoriesData} sliders={slidersData} />
      </Suspense>
    </div>
  );

  /**
   * Render mobile navigation and hero section
   */
  const renderMobileLayout = () => (
    <div className="Mobile">
      <Suspense fallback={<PageLoadingFallback />}>
        <NavMenuMobile categories={categoriesData} />
        <HomeTopMobile sliders={slidersData} />
      </Suspense>
    </div>
  );

  /**
   * Render categories section
   */
  const renderCategoriesSection = () => (
    <Suspense fallback={null}>
      <Categories categories={categoriesData} />
    </Suspense>
  );

  /**
   * Render dynamic product sections from backend
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
