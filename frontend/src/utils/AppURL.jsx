const apiHost = process.env.REACT_APP_API_BASE_URL || "http://127.0.0.1:8000";
const BaseURL = apiHost.endsWith('/api') ? apiHost : `${apiHost}/api`;

const AppURL = {
  BaseURL: BaseURL,
  PostContact: `${BaseURL}/post-contact`,
  Content: `${BaseURL}/content`,
  
  getContent(type) {
    return `${this.BaseURL}/content/${type}`;
  },

  updateContent(type) {
    return `${this.BaseURL}/content/${type}`;
  },

  // Settings routes
  getSettings: `${BaseURL}/settings`,
  updateSettings: `${BaseURL}/settings`,
  
  // Category routes
  CategoryDetails: `${BaseURL}/categories`,
  CategorySearch: `${BaseURL}/categories/search`,
  CategoryProducts(slug) {
    return `${this.BaseURL}/categories/${slug}/products`;
  },
  CategoryBreadcrumb(slug) {
    return `${this.BaseURL}/categories/${slug}/breadcrumb`;
  },

  Sliders: `${BaseURL}/sliders`,
  Notifications: `${BaseURL}/notifications`,
  UserLogin: `${BaseURL}/login`,
  UserProfile: `${BaseURL}/user`,
  UserRegister: `${BaseURL}/register`,
  ForgetPassword: `${BaseURL}/forget-password`,
  PasswordReset: `${BaseURL}/password-reset`,

  // Cart
  addToCart: `${BaseURL}/cart/add`,
  getCart: `${BaseURL}/cart`,
  updateCart(id) {
    return `${this.BaseURL}/cart/update/${id}`;
  },
  removeFromCart(id) {
    return `${this.BaseURL}/cart/remove/${id}`;
  },

  // Orders & Payment
  Orders: `${BaseURL}/orders`,
  OrderDetails(id) {
    return `${this.BaseURL}/orders/${id}`;
  },
  ApplyCoupon: `${BaseURL}/coupons/apply`,
  PaymentCharge: `${BaseURL}/payment/charge`,
  PaymentWebhook: `${BaseURL}/payment/webhook`,

  // Products
  productByRemark(remark) {
    return `${this.BaseURL}/products/remark/${remark}`;
  },

  ProductByCategory(slug) {
    return `${this.BaseURL}/products/category/${slug}`;
  },

  ProductBySubCategory(category_slug, subcategory_slug) {
    return `${this.BaseURL}/product/${category_slug}/${subcategory_slug}`;
  },

  ProductDetails(product_id) {
    return `${this.BaseURL}/product-details/${product_id}`;
  },

  ProductBySearch(query) {
    return `${this.BaseURL}/products/search/${query}`;
  },

  relatedProduct(product_id) {
    return `${this.BaseURL}/related-product/${product_id}`;
  },

  reviews(product_id) {
    return `${this.BaseURL}/products/${product_id}/reviews`;
  },

  HomepageSections: `${BaseURL}/products/homepage-sections`,

  // Admin Product Images
  adminProductImages(productId) {
    return `${this.BaseURL}/admin/products/${productId}/images`;
  },

  adminProductImagesUpload(productId) {
    return `${this.BaseURL}/admin/products/${productId}/images`;
  },

  adminProductImageUpdate(productId, imageId) {
    return `${this.BaseURL}/admin/products/${productId}/images/${imageId}`;
  },

  adminProductImageDelete(productId, imageId) {
    return `${this.BaseURL}/admin/products/${productId}/images/${imageId}`;
  },

  adminProductImagesReorder(productId) {
    return `${this.BaseURL}/admin/products/${productId}/images/reorder`;
  },

  adminProductImageSetCover(productId, imageId) {
    return `${this.BaseURL}/admin/products/${productId}/images/${imageId}/cover`;
  },
};

export default AppURL;
