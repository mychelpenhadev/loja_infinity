

const STORAGE_KEYS = {
  CART: 'papelaria_cart',
  THEME: 'papelaria_theme',
  PRODUCTS_CACHE: 'papelaria_products_cache',
  PRODUCTS_CACHE_EXP: 'papelaria_products_exp',
  CONFIG_CACHE: 'papelaria_config_cache',
  CONFIG_CACHE_EXP: 'papelaria_config_exp',
  PRODUCT_DETAIL_PREFIX: 'papelaria_prod_'
};
const COLOR_CATEGORIES = ['linhas', 'las', 'croche', 'barbantes', 'bordados'];
const ProductManager = {
  _cache: {},
  getAll: async (params = {}) => {
    const now = Date.now();
    const cacheKey = 'papelaria_prods_list_' + JSON.stringify(params);
    const expKey = cacheKey + '_exp';

    const hasSearch = params.search && params.search.trim().length > 0;
    if (!hasSearch) {
      const cached = sessionStorage.getItem(cacheKey);
      const exp = sessionStorage.getItem(expKey);
      if (cached && exp && now < parseInt(exp)) {
        return JSON.parse(cached);
      }
    }
    try {
      const query = new URLSearchParams(params).toString();
      const response = await fetch(`api/products.php?action=list&${query}`, { credentials: 'include' });
      const data = await response.json();
      if (!hasSearch) {
        try {
          sessionStorage.setItem(cacheKey, JSON.stringify(data));
          sessionStorage.setItem(expKey, (now + 600000).toString());
        } catch (e) { console.warn("Cache quota", e); }
      }
      return data;
    } catch (err) {
      console.error("Erro ao carregar produtos:", err);
      return { products: [], pagination: {} };
    }
  },
  clearCache: () => {
    sessionStorage.removeItem(STORAGE_KEYS.PRODUCTS_CACHE);
    sessionStorage.removeItem(STORAGE_KEYS.PRODUCTS_CACHE_EXP);
  },
  getById: async (id) => {
    if (ProductManager._cache[id]) return ProductManager._cache[id];

    const cached = sessionStorage.getItem(STORAGE_KEYS.PRODUCT_DETAIL_PREFIX + id);
    if (cached) {
      const data = JSON.parse(cached);
      ProductManager._cache[id] = data;
      return data;
    }
    try {
      console.log(`[DB] Buscando produto na API para ID: ${id}`);
      const response = await fetch(`api/products.php?action=get&id=${id}`, { credentials: 'include' });
      const data = await response.json();
      console.log(`[DB] Resposta da API para ID ${id}:`, data);
      if (data) {
        ProductManager._cache[id] = data;
        try {
          sessionStorage.setItem(STORAGE_KEYS.PRODUCT_DETAIL_PREFIX + id, JSON.stringify(data));
        } catch (e) {
          console.warn("[DB] Falha ao salvar no sessionStorage (provavelmente cota excedida):", e);
        }
      } else {
        sessionStorage.removeItem(STORAGE_KEYS.PRODUCT_DETAIL_PREFIX + id);
      }
      return data;
    } catch (err) {
      console.error("[DB] Erro ao buscar produto na API:", err);

      sessionStorage.removeItem(STORAGE_KEYS.PRODUCT_DETAIL_PREFIX + id);
      return null;
    }
  },
  getBatch: async (ids) => {
    if (!ids || ids.length === 0) return [];
    const results = [];
    const missingIds = [];
    ids.forEach(id => {
      if (ProductManager._cache[id]) {
        results.push(ProductManager._cache[id]);
      } else {
        const cached = sessionStorage.getItem(STORAGE_KEYS.PRODUCT_DETAIL_PREFIX + id);
        if (cached) {
          const data = JSON.parse(cached);
          ProductManager._cache[id] = data;
          results.push(data);
        } else {
          missingIds.push(id);
        }
      }
    });
    if (missingIds.length === 0) return results;
    try {
      const response = await fetch(`api/products.php?action=get_batch&ids=${missingIds.join(',')}`);
      const data = await response.json();
      data.forEach(p => {
        ProductManager._cache[p.id] = p;
        try {
          sessionStorage.setItem(STORAGE_KEYS.PRODUCT_DETAIL_PREFIX + p.id, JSON.stringify(p));
        } catch (e) {
          console.warn("[DB] Falha ao salvar item no sessionStorage (lote):", e);
        }
        results.push(p);
      });
      return results;
    } catch (err) {
      console.error("Erro ao buscar produtos em lote:", err);
      return results;
    }
  },
  add: async (product) => {
    try {
      ProductManager.clearCache();
      const response = await fetch('api/products.php?action=save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(product),
        credentials: 'include'
      });
      const text = await response.text();
      const cleanedText = text.trim();
      try {
        return JSON.parse(cleanedText);
      } catch (e) {
        console.error("Resposta não-JSON do servidor:", text);

        const snippet = text.length > 50 ? text.substring(0, 50) + "..." : text;
        return { status: 'error', message: 'Servidor retornou formato inválido. Status: ' + response.status + ' Info: ' + snippet };
      }
    } catch (err) {
      console.error("Erro na requisição fetch:", err);
      return { status: 'error', message: 'Falha na conexão com o servidor.' };
    }
  },
  update: async (id, updatedData) => {
    try {
      ProductManager.clearCache();
      const response = await fetch('api/products.php?action=save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ...updatedData, id }),
        credentials: 'include'
      });
      return await response.json();
    } catch (err) {
      console.error("Erro ao atualizar produto:", err);
      return null;
    }
  },
  remove: async (id) => {
    try {
      ProductManager.clearCache();
      const response = await fetch(`api/products.php?action=delete&id=${id}`, { credentials: 'include' });
      return await response.json();
    } catch (err) {
      console.error("Erro ao remover produto:", err);
      return null;
    }
  }
};
const CartManager = {
  getCartKey: () => {
      return window.userId ? 'papelaria_cart_user_' + window.userId : 'papelaria_cart_guest';
  },
  getCart: () => JSON.parse(localStorage.getItem(CartManager.getCartKey()) || '[]'),
  add: (productId, quantity = 1, color = null) => {
    const key = CartManager.getCartKey();
    const cart = JSON.parse(localStorage.getItem(key) || '[]');

    const existing = cart.find(item =>
      String(item.productId) === String(productId) &&
      item.color === color
    );
    if (existing) {
      existing.quantity += quantity;
    } else {
      cart.push({ productId, quantity, color });
    }
    localStorage.setItem(key, JSON.stringify(cart));
    window.dispatchEvent(new Event('cartUpdated'));
    return true;
  },
  remove: (productId, color = null) => {
    const key = CartManager.getCartKey();
    const cart = JSON.parse(localStorage.getItem(key) || '[]');
    const filtered = cart.filter(item =>
      !(String(item.productId) === String(productId) && item.color === color)
    );
    localStorage.setItem(key, JSON.stringify(filtered));
    window.dispatchEvent(new Event('cartUpdated'));
    return true;
  },
  updateQuantity: (productId, quantity, color = null) => {
    if (quantity <= 0) return CartManager.remove(productId, color);
    const key = CartManager.getCartKey();
    const cart = JSON.parse(localStorage.getItem(key) || '[]');
    const item = cart.find(i =>
      String(i.productId) === String(productId) &&
      i.color === color
    );
    if (item) {
      item.quantity = quantity;
      localStorage.setItem(key, JSON.stringify(cart));
      window.dispatchEvent(new Event('cartUpdated'));
    }
    return true;
  },
  clear: () => {
    localStorage.setItem(CartManager.getCartKey(), JSON.stringify([]));
    window.dispatchEvent(new Event('cartUpdated'));
  },
  getTotalItems: () => {
    const cart = CartManager.getCart();
    return cart.reduce((total, item) => total + item.quantity, 0);
  },
  getTotalPrice: async () => {
    const cart = CartManager.getCart();
    const products = await ProductManager.getAll();
    return cart.reduce((total, item) => {
      const product = products.find(p => String(p.id) === String(item.productId));
      return total + (product ? parseFloat(product.price) * item.quantity : 0);
    }, 0);
  }
};
const OrderManager = {
  getAll: async () => {
    try {
      const response = await fetch('api/orders.php?action=list', { credentials: 'include' });
      return await response.json();
    } catch (err) {
      console.error("Erro ao buscar pedidos:", err);
      return [];
    }
  },
  getByUser: async (userId) => {
    try {
      const response = await fetch(`api/orders.php?action=list_user&user_id=${userId}`, { credentials: 'include' });
      return await response.json();
    } catch (err) {
      console.error("Erro ao buscar pedidos do usuário:", err);
      return [];
    }
  },
  add: async (orderData) => {
    try {
      const response = await fetch('api/orders.php?action=save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(orderData)
      });
      return await response.json();
    } catch (err) {
      console.error("Erro ao registrar pedido:", err);
      return null;
    }
  },
  updateStatus: async (orderId, newStatus) => {
    try {
      const response = await fetch(`api/orders.php?action=update_status&id=${orderId}&status=${newStatus}`);
      return await response.json();
    } catch (err) {
      console.error("Erro ao atualizar status:", err);
      return null;
    }
  },
  remove: async (orderId) => {
    try {
      const response = await fetch(`api/orders.php?action=delete&id=${orderId}`);
      return await response.json();
    } catch (err) {
      console.error("Erro ao remover pedido:", err);
      return null;
    }
  },
  deleteUserOrder: async (orderId, userId) => {
    try {
      const response = await fetch(`api/orders.php?action=delete_user&id=${orderId}`, { credentials: 'include' });
      return await response.json();
    } catch (err) {
      console.error("Erro ao remover pedido:", err);
      return null;
    }
  },
  clearCache: () => {
    sessionStorage.removeItem(STORAGE_KEYS.CONFIG_CACHE);
    sessionStorage.removeItem(STORAGE_KEYS.CONFIG_CACHE_EXP);
  },
  get: (key) => {
    return ConfigManager._cache[key];
  },
  set: async (key, value) => {
    try {
      ConfigManager.clearCache();
      ConfigManager._cache[key] = value;
      await fetch('api/config.php?action=save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ [key]: value }),
        credentials: 'include'
      });
    } catch (err) {
      console.error("Erro ao salvar config:", err);
    }
  }
};

(async () => {
    await ConfigManager.init();
})();
window.ProductManager = ProductManager;
window.CartManager = CartManager;
window.OrderManager = OrderManager;
window.ConfigManager = ConfigManager;
window.COLOR_CATEGORIES = COLOR_CATEGORIES;
window.STORAGE_KEYS = STORAGE_KEYS;
window.formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
};
