

const STORAGE_KEYS = {
  PRODUCTS: 'papelaria_products',
  CART: 'papelaria_cart',
  THEME: 'papelaria_theme',
  ORDERS: 'papelaria_orders',
  CONFIG: 'papelaria_config'
};

const DEFAULT_PRODUCTS = [
  {
    id: 'p1',
    name: 'Caderno Inteligente Tons Pastéis',
    description: 'Caderno de discos com folhas reposicionáveis. Capa em tons pastéis com acabamento premium e elástico de fechamento.',
    price: 89.90,
    category: 'cadernos',
    image: 'https://images.unsplash.com/photo-1531346878377-a541e4ab0eaf?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
    video: '',
    rating: 5
  },
  {
    id: 'p2',
    name: 'Kit Canetas Gel Pastel',
    description: 'Conjunto com 6 cores incríveis com ponta fina 0.5mm. Desliza fácil e não mancha o verso da folha.',
    price: 34.50,
    category: 'canetas',
    image: 'https://images.unsplash.com/photo-1585336261022-680e295ce3fe?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
    video: '',
    rating: 4.5
  },
  {
    id: 'p3',
    name: 'Mochila Escolar Minimalista Lilás',
    description: 'Mochila espaçosa com compartimento para notebook. Feita em material resistente à água e design super limpo e moderno.',
    price: 159.90,
    category: 'mochilas',
    image: 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
    video: '',
    rating: 4.8
  },
  {
    id: 'p4',
    name: 'Estojo Box Organizador',
    description: 'Estojo com grande capacidade e divisórias elásticas internas para organizar até 100 lápis/canetas.',
    price: 45.00,
    category: 'materiais escolares',
    image: 'https://images.unsplash.com/photo-1583485088034-697b5bc54ccd?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
    video: '',
    rating: 4
  },
  {
    id: 'p5',
    name: 'Planner Anual Argolado',
    description: 'Chega de perder compromissos! Planner sem data, visões semanais e mensais com design colorido e motivador.',
    price: 110.00,
    category: 'cadernos',
    image: 'https://images.unsplash.com/photo-1506784983877-45594efa4cbe?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
    video: '',
    rating: 5
  },
  {
    id: 'p6',
    name: 'Marca-texto Nuvem (Kit com 4)',
    description: 'Cores suaves que não atravessam a folha, formato ergonômico e estiloso para os estudos renderem mais.',
    price: 28.90,
    category: 'canetas',
    image: 'https://images.unsplash.com/photo-1527748842146-cdec8a1d720b?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
    video: '',
    rating: 4.2
  },
  {
    id: 'prod-009',
    name: 'Linha Amigurumi Círculo 254m',
    category: 'Costura & Bordados',
    brand: 'amigurumi',
    price: 18.50,
    rating: 4.9,
    image: 'https://images.tcdn.com.br/img/img_prod/606359/fio_amigurumi_circulo_100_algodao_125g_3901_1_25eec91e573e65eedfcfd93d5082bdde.jpg',
    description: 'Fio de algodão mercerizado ideal para crochê e tricô de bonecos tridimensionais (Amigurumis).'
  },
  {
    id: 'prod-010',
    name: 'Fio Anne 500 Círculo',
    category: 'Costura & Bordados',
    brand: 'anne',
    price: 21.90,
    rating: 4.7,
    image: 'https://images.tcdn.com.br/img/img_prod/606359/fio_anne_500m_circulo_439_1_20201211151621.jpg',
    description: 'Fio 100% algodão mercerizado, proporciona acabamento cintilante e toque macio ao bordado.'
  },
  {
    id: 'prod-011',
    name: 'Fio Duna Multicolor Círculo',
    category: 'Costura & Bordados',
    brand: 'duna',
    price: 19.90,
    rating: 4.8,
    image: 'https://images.tcdn.com.br/img/img_prod/606359/fio_duna_circulo_170m_100g_4615_1_20201211171804.jpg',
    description: 'Com espessura ideal, oferece rendimento e caimento perfeito em peças de vestuário e decoração.'
  }
];


function initializeDB() {
  if (!localStorage.getItem(STORAGE_KEYS.PRODUCTS)) {
    localStorage.setItem(STORAGE_KEYS.PRODUCTS, JSON.stringify(DEFAULT_PRODUCTS));
  }
  if (!localStorage.getItem(STORAGE_KEYS.CART)) {
    localStorage.setItem(STORAGE_KEYS.CART, JSON.stringify([]));
  }
}

const ProductManager = {
  getAll: () => JSON.parse(localStorage.getItem(STORAGE_KEYS.PRODUCTS) || '[]'),
  
  getById: (id) => {
    const products = ProductManager.getAll();
    return products.find(p => p.id === id);
  },
  
  add: (product) => {
    const products = ProductManager.getAll();
    const newProduct = {
      ...product,
      id: 'p' + Date.now().toString(),
      rating: 5
    };
    products.push(newProduct);
    localStorage.setItem(STORAGE_KEYS.PRODUCTS, JSON.stringify(products));
    return newProduct;
  },
  
  update: (id, updatedData) => {
    const products = ProductManager.getAll();
    const index = products.findIndex(p => p.id === id);
    if (index !== -1) {
      products[index] = { ...products[index], ...updatedData };
      localStorage.setItem(STORAGE_KEYS.PRODUCTS, JSON.stringify(products));
      return true;
    }
    return false;
  },
  
  remove: (id) => {
    const products = ProductManager.getAll();
    const filtered = products.filter(p => p.id !== id);
    localStorage.setItem(STORAGE_KEYS.PRODUCTS, JSON.stringify(filtered));
    return true;
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
    const existing = cart.find(item => item.productId === productId);
    
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
    const filtered = cart.filter(item => item.productId !== productId);
    localStorage.setItem(key, JSON.stringify(filtered));
    window.dispatchEvent(new Event('cartUpdated'));
    return true;
  },
  
  updateQuantity: (productId, quantity) => {
    if (quantity <= 0) return CartManager.remove(productId);
    
    const key = CartManager.getCartKey();
    const cart = JSON.parse(localStorage.getItem(key) || '[]');
    const item = cart.find(i => i.productId === productId);
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
  
  getTotalPrice: () => {
    const cart = CartManager.getCart();
    const products = ProductManager.getAll();
    return cart.reduce((total, item) => {
      const product = products.find(p => p.id === item.productId);
      return total + (product ? product.price * item.quantity : 0);
    }, 0);
  }
};

const OrderManager = {
  getAll: () => JSON.parse(localStorage.getItem(STORAGE_KEYS.ORDERS) || '[]'),
  
  add: (orderData) => {
    const orders = OrderManager.getAll();
    const newOrder = {
      id: 'ORD' + Math.floor(Math.random() * 1000000),
      date: new Date().toISOString(),
      status: 'pendente',
      ...orderData
    };
    orders.unshift(newOrder); // Adds to beginning so newest is first
    localStorage.setItem(STORAGE_KEYS.ORDERS, JSON.stringify(orders));
    return newOrder;
  },
  
  updateStatus: (orderId, newStatus) => {
    const orders = OrderManager.getAll();
    const index = orders.findIndex(o => o.id === orderId);
    if (index !== -1) {
      orders[index].status = newStatus;
      localStorage.setItem(STORAGE_KEYS.ORDERS, JSON.stringify(orders));
      return true;
    }
    return false;
  }
};

const ConfigManager = {
  get: (key) => {
    const config = JSON.parse(localStorage.getItem(STORAGE_KEYS.CONFIG) || '{}');
    return config[key];
  },
  set: (key, value) => {
    const config = JSON.parse(localStorage.getItem(STORAGE_KEYS.CONFIG) || '{}');
    config[key] = value;
    localStorage.setItem(STORAGE_KEYS.CONFIG, JSON.stringify(config));
  }
};


initializeDB();

window.ProductManager = ProductManager;
window.CartManager = CartManager;
window.OrderManager = OrderManager;
window.ConfigManager = ConfigManager;
window.STORAGE_KEYS = STORAGE_KEYS;
