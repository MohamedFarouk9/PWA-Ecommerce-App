# Frontend Architecture Guide

## Overview
This document outlines the senior-level architecture patterns implemented in the frontend application.

---

## Directory Structure

```
src/
├── services/                 # API service layer
│   ├── apiClient.js         # Axios instance with interceptors
│   ├── productService.js    # Product API calls
│   ├── categoryService.js   # Category API calls
│   └── sliderService.js     # Slider API calls
├── hooks/                    # Custom React hooks
│   ├── useProducts.js       # Products data hook
│   ├── useCategories.js     # Categories data hook
│   ├── useSliders.js        # Sliders data hook
│   ├── useSiteContent.js    # (Existing) Site content hook
│   └── useSiteSettings.js   # (Existing) Site settings hook
├── components/
│   ├── Products/
│   │   ├── ProductCard.jsx  # Reusable product card component
│   │   ├── ProductCard.css
│   │   └── ProductSection.jsx # Reusable section component
│   ├── ui/
│   │   ├── LoadingStates.jsx   # Loading skeleton components
│   │   └── ErrorStates.jsx     # Error and empty state components
│   ├── common/              # (Existing) Nav, Footer components
│   ├── home/                # (Existing) Home page sections
│   └── ...
├── pages/
│   ├── HomePage.jsx         # Refactored main landing page
│   ├── ProductDetailsPage.jsx
│   ├── ProfilePage.jsx
│   └── SearchPage.jsx
├── utils/
│   ├── AppURL.jsx
│   ├── AuthContext.js
│   └── CartContext.js
└── ...
```

---

## Architecture Patterns

### 1. **Service Layer Pattern**
All API calls are centralized in the `services/` folder.

**Benefits:**
- Separation of concerns
- Easy to mock for testing
- Centralized error handling
- Reusable across components

**Example:**
```javascript
// services/productService.js
const productService = {
  getHomepageSections: async () => {
    const response = await apiClient.get('/products/homepage-sections');
    return response.data;
  },
  // ... more methods
};
```

### 2. **Custom Hooks Pattern**
Each data-fetching requirement has a corresponding custom hook.

**Benefits:**
- Reusable data-fetching logic
- Clean component code
- Automatic caching potential
- Handles loading and error states

**Example:**
```javascript
// hooks/useProducts.js
const useProducts = (fetchType = 'all', params = null) => {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    // Fetch logic here
  }, [fetchType, params]);

  return { data, loading, error };
};

// Usage in component
const { data, loading, error } = useProducts('sections');
```

### 3. **API Client Interceptors**
Centralized axios instance with:
- Auto token injection
- Error handling
- Request/response interceptors

**Example:**
```javascript
// services/apiClient.js
apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});
```

### 4. **Component Composition**
Reusable UI components that accept data as props.

**Example:**
```javascript
<ProductSection
  title="Featured Products"
  products={products}
  loading={loading}
  error={error}
  onRetry={handleRetry}
/>
```

---

## Key Components

### ProductCard Component
Displays a single product with image, title, price, and discount badge.

```javascript
<ProductCard product={{ id: 1, title: 'Product', image: '...', price: 99 }} />
```

### ProductSection Component
Reusable section that displays multiple products with loading and error states.

```javascript
<ProductSection
  title="New Arrivals"
  products={productArray}
  loading={false}
  error={null}
/>
```

### Loading States
- `ProductSkeleton` - Skeleton loaders for products
- `SectionSkeleton` - Skeleton for entire sections
- `PageLoadingFallback` - Full page loader

### Error States
- `ErrorAlert` - Error message with retry button
- `EmptyState` - Empty content display

---

## Data Flow

```
HomePage Component
    ↓
Custom Hooks (useProducts, useCategories, useSliders)
    ↓
Services (productService, categoryService, sliderService)
    ↓
API Client (apiClient with interceptors)
    ↓
Backend API Endpoints
```

### Example Flow:
1. **HomePage** calls `useProducts('sections')`
2. **useProducts** calls `productService.getHomepageSections()`
3. **productService** calls `apiClient.get('/products/homepage-sections')`
4. **apiClient** adds auth token via interceptor
5. Response is returned and displayed using `ProductSection` component

---

## Benefits of This Architecture

✅ **Scalability** - Easy to add new features and API endpoints
✅ **Maintainability** - Clear separation of concerns
✅ **Reusability** - Services and hooks are DRY (Don't Repeat Yourself)
✅ **Testability** - Services can be easily mocked for unit tests
✅ **Performance** - Lazy loading and code splitting
✅ **Error Handling** - Centralized error management
✅ **Code Organization** - Logical folder structure

---

## Adding New Features

### To add a new API endpoint:

1. **Create method in service:**
```javascript
// services/productService.js
getNewEndpoint: async () => {
  const response = await apiClient.get('/new-endpoint');
  return response.data;
}
```

2. **Create custom hook (if needed):**
```javascript
// hooks/useNewData.js
const useNewData = () => {
  const [data, setData] = useState(null);
  // ... fetch logic
  return { data, loading, error };
};
```

3. **Use in component:**
```javascript
const { data, loading, error } = useNewData();
```

---

## Best Practices

✅ Always use custom hooks for data fetching
✅ Keep components focused on UI rendering
✅ Handle loading and error states
✅ Use lazy loading for route-based components
✅ Keep services pure and side-effect free
✅ Document complex logic with comments
✅ Use proper TypeScript types (when applicable)
✅ Test services independently from components

---

## Migration Notes

The old `Sections.jsx` component directly fetched data. The new approach:
- Uses `useProducts('sections')` hook
- Displays via reusable `ProductSection` component
- Has better error handling and loading states
- Follows DRY principle

---

## Common Use Cases

### Fetch and Display Products
```javascript
const { data: products, loading, error } = useProducts('sections');
return <ProductSection title="Products" products={products} loading={loading} />;
```

### Fetch with Parameters
```javascript
const { data: related } = useProducts('related', productId);
```

### Handle Errors with Retry
```javascript
const handleRetry = () => {
  // Refetch logic
};
return <ErrorAlert message={error} onRetry={handleRetry} />;
```

---

For questions or improvements, refer to the component/service files for additional documentation.
