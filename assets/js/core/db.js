
// db.js - Versão Refatorada para MySQL
// O Carrinho permanece em localStorage por ser específico do dispositivo do cliente.
// Produtos, Pedidos e Configurações agora são buscados via API.

const STORAGE_KEYS = {
  CART: 'papelaria_cart',
  THEME: 'papelaria_theme'
};

const ProductManager = {
  _cache: null,

  getAll: async () => {
    try {
      const response = await fetch('api/products.php?action=list');
      const data = await response.json();
      ProductManager._cache = data;
      return data;
    } catch (err) {
      console.error("Erro ao buscar produtos:", err);
      throw err;
    }
  },
  
  getById: async (id) => {
    try {
      const response = await fetch(`api/products.php?action=get&id=${id}`);
      return await response.json();
    } catch (err) {
      console.error("Erro ao buscar produto:", err);
      return null;
    }
  },
  
  add: async (product) => {
    try {
      const response = await fetch('api/products.php?action=save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(product)
      });
      
      const text = await response.text();
      const cleanedText = text.trim();
      try {
        return JSON.parse(cleanedText);
      } catch (e) {
        console.error("Resposta não-JSON do servidor:", text);
        // Exibir os primeiros 50 caracteres do texto para o desenvolvedor ver se há lixo
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
      const response = await fetch('api/products.php?action=save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ...updatedData, id })
      });
      return await response.json();
    } catch (err) {
      console.error("Erro ao atualizar produto:", err);
      return null;
    }
  },
  
  remove: async (id) => {
    try {
      const response = await fetch(`api/products.php?action=delete&id=${id}`);
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
  
  add: (productId, quantity = 1) => {
    const key = CartManager.getCartKey();
    const cart = JSON.parse(localStorage.getItem(key) || '[]');
    const existing = cart.find(item => String(item.productId) === String(productId));
    
    if (existing) {
      existing.quantity += quantity;
    } else {
      cart.push({ productId, quantity });
    }
    
    localStorage.setItem(key, JSON.stringify(cart));
    window.dispatchEvent(new Event('cartUpdated'));
    return true;
  },
  
  remove: (productId) => {
    const key = CartManager.getCartKey();
    const cart = JSON.parse(localStorage.getItem(key) || '[]');
    const filtered = cart.filter(item => String(item.productId) !== String(productId));
    localStorage.setItem(key, JSON.stringify(filtered));
    window.dispatchEvent(new Event('cartUpdated'));
    return true;
  },
  
  updateQuantity: (productId, quantity) => {
    if (quantity <= 0) return CartManager.remove(productId);
    
    const key = CartManager.getCartKey();
    const cart = JSON.parse(localStorage.getItem(key) || '[]');
    const item = cart.find(i => String(i.productId) === String(productId));
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
      const response = await fetch('api/orders.php?action=list');
      return await response.json();
    } catch (err) {
      console.error("Erro ao buscar pedidos:", err);
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
  }
};

const ConfigManager = {
  _cache: {},

  init: async () => {
    try {
      const response = await fetch('api/config.php?action=get');
      ConfigManager._cache = await response.json();
    } catch (err) {
      console.error("Erro ao inicializar ConfigManager:", err);
    }
  },

  get: (key) => {
    return ConfigManager._cache[key];
  },

  set: async (key, value) => {
    try {
      ConfigManager._cache[key] = value;
      await fetch('api/config.php?action=save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ [key]: value })
      });
    } catch (err) {
      console.error("Erro ao salvar config:", err);
    }
  }
};

// Inicialização Assíncrona
(async () => {
    await ConfigManager.init();
})();

window.ProductManager = ProductManager;
window.CartManager = CartManager;
window.OrderManager = OrderManager;
window.ConfigManager = ConfigManager;
window.formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
};
