const BaseURL = "http://127.0.0.1:8000/api";

const AppURL = {
  BaseURL: BaseURL,
  PostContact: `${BaseURL}/post-contact`,
  Content: `${BaseURL}/content`,
  //content route
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
  //cart
  addToCart: `${BaseURL}/cart/add`,
  getCart: `${BaseURL}/cart`,
  updateCart(id) {
    return `${this.BaseURL}/cart/update/${id}`;
  },
  removeFromCart(id) {
    return `${this.BaseURL}/cart/remove/${id}`;
  },

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
    return `${this.BaseURL}/search/${query}`;
  },

  relatedProduct(product_id) {
    return `${this.BaseURL}/related-product/${product_id}`;
  },

  reviews(product_id) {
    return `${this.BaseURL}/products/${product_id}/reviews`;
  },

  HomepageSections: `${BaseURL}/products/homepage-sections`,
};

export default AppURL;
