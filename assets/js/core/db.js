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
        try { return JSON.parse(cached); } catch(e) { sessionStorage.removeItem(cacheKey); }
      }
    }
    try {
      const query = new URLSearchParams(params).toString();
      const response = await fetch(`api/products.php?action=list&${query}&_=${Date.now()}`, { credentials: 'include' });
      const data = await response.json();
      if (!hasSearch) {
        try {
          const cacheData = JSON.parse(JSON.stringify(data));
          if (cacheData.products) {
            cacheData.products = cacheData.products.map(p => ({
              ...p,
              image: (p.image && p.image.startsWith('data:image')) ? 'assets/img/logoPNG.png' : p.image
            }));
          }
          const json = JSON.stringify(cacheData);
          if (json.length < 2000000) {
            sessionStorage.setItem(cacheKey, json);
            sessionStorage.setItem(expKey, (now + 600000).toString());
          }
        } catch (e) {
          ProductManager.clearCache();
          console.warn("Cache quota", e);
        }
      }
      return data;
    } catch (err) {
      console.error("Erro ao carregar produtos:", err);
      return { products: [], pagination: {} };
    }
  },
  clearCache: () => {
    const keysToRemove = [];
    for (let i = 0; i < sessionStorage.length; i++) {
      const key = sessionStorage.key(i);
      if (key && (key.startsWith('papelaria_prods_list_') || key.startsWith('papelaria_prod_') || key === STORAGE_KEYS.PRODUCTS_CACHE || key === STORAGE_KEYS.PRODUCTS_CACHE_EXP)) {
        keysToRemove.push(key);
      }
    }
    keysToRemove.forEach(k => sessionStorage.removeItem(k));
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
          const json = JSON.stringify(data);
          if (json.length < 500000) {
            sessionStorage.setItem(STORAGE_KEYS.PRODUCT_DETAIL_PREFIX + id, json);
          }
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
          const json = JSON.stringify(p);
          if (json.length < 500000) {
            sessionStorage.setItem(STORAGE_KEYS.PRODUCT_DETAIL_PREFIX + p.id, json);
          }
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
  },
  mergeGuestCart: () => {
    const guestCart = JSON.parse(localStorage.getItem('papelaria_cart_guest') || '[]');
    if (guestCart.length === 0) return;
    const userId = window.userId || null;
    if (!userId) return;
    
    const userKey = 'papelaria_cart_user_' + userId;
    const userCart = JSON.parse(localStorage.getItem(userKey) || '[]');
    
    guestCart.forEach(gItem => {
        const existing = userCart.find(uItem => 
            String(uItem.productId) === String(gItem.productId) && 
            uItem.color === gItem.color
        );
        if (existing) {
            existing.quantity += gItem.quantity;
        } else {
            userCart.push(gItem);
        }
    });
    
    localStorage.setItem(userKey, JSON.stringify(userCart));
    localStorage.setItem('papelaria_cart_guest', JSON.stringify([]));
    window.dispatchEvent(new Event('cartUpdated'));
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
        credentials: 'include',
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
  }
};
const ConfigManager = {
  _cache: {},
  init: async () => {
    try {
      const response = await fetch('api/config.php?action=get', { credentials: 'include' });
      const data = await response.json();
      ConfigManager._cache = data || {};
    } catch (err) {
      console.error("Erro ao inicializar ConfigManager:", err);
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
      const resp = await fetch('api/config.php?action=save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ [key]: value }),
        credentials: 'include'
      });
      const result = await resp.json();
      return result;
    } catch (err) {
      console.error("Erro ao salvar config:", err);
      return { status: 'error', message: err.message };
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

// Autocomplete compartilhado - carrega produtos uma única vez
window.SearchAutocomplete = {
    _products: null,
    _loading: false,
    _callbacks: [],
    
    load: async function() {
        if (this._products) return this._products;
        if (this._loading) {
            return new Promise(resolve => this._callbacks.push(resolve));
        }
        
        this._loading = true;
        try {
            const data = await window.ProductManager.getAll({ limit: 50, slim: 1 });
            this._products = (data.products || []).map(p => ({
                id: p.id,
                name: p.name,
                price: p.price,
                image: (p.image && p.image.startsWith('data:image')) ? 'assets/img/logoPNG.png' : p.image
            }));
            this._callbacks.forEach(cb => cb(this._products));
            this._callbacks = [];
            return this._products;
        } catch (e) {
            console.warn('SearchAutocomplete load error:', e);
            return [];
        } finally {
            this._loading = false;
        }
    },
    
    setup: function(inputEl, resultsEl, options = {}) {
        if (!inputEl || !resultsEl) return;
        
        const onSelect = options.onSelect || ((product) => {
            window.location.href = 'detalhes.html?id=' + product.id;
        });
        
        inputEl.addEventListener('input', async () => {
            const query = inputEl.value.trim().toLowerCase();
            if (query.length < 2) {
                resultsEl.style.display = 'none';
                return;
            }
            
            const products = await this.load();
            const matches = products.filter(p => 
                p.name.toLowerCase().includes(query) ||
                (p.category && p.category.toLowerCase().includes(query))
            ).slice(0, 8);
            
            if (matches.length === 0) {
                resultsEl.innerHTML = '<div style="padding:0.6rem; color:var(--clr-text-light); font-size:0.85rem;">Nenhum produto encontrado</div>';
            } else {
                resultsEl.innerHTML = matches.map(p =>
                    `<div class="search-item" data-id="${p.id}" style="padding:0.5rem 0.7rem; cursor:pointer; font-size:0.85rem; border-bottom:1px solid var(--clr-border, #eee); display:flex; align-items:center; gap:0.5rem;">
                        <img src="${p.image}" style="width:35px; height:35px; object-fit:cover; border-radius:4px;" loading="lazy" onerror="this.style.display='none'">
                        <div style="flex:1; min-width:0;">
                            <div style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${p.name}</div>
                            <div style="color:var(--clr-primary, #e11d48); font-weight:600;">${window.formatCurrency(p.price)}</div>
                        </div>
                    </div>`
                ).join('');
            }
            resultsEl.style.display = 'block';
            
            resultsEl.querySelectorAll('.search-item').forEach(el => {
                el.addEventListener('click', () => {
                    const id = el.dataset.id;
                    const product = products.find(p => p.id == id);
                    if (product) {
                        resultsEl.style.display = 'none';
                        inputEl.value = '';
                        onSelect(product);
                    }
                });
            });
        });
        
        inputEl.addEventListener('focus', () => {
            if (inputEl.value.trim().length >= 2) {
                inputEl.dispatchEvent(new Event('input'));
            }
        });
        
        document.addEventListener('click', (e) => {
            if (!inputEl.parentElement.contains(e.target)) {
                resultsEl.style.display = 'none';
            }
        });
    }
};

function initLogin() {
    const btnLogout = document.getElementById('btn-logout');
    if(btnLogout) {
        btnLogout.onclick = async (e) => {
            e.preventDefault();
            if(confirm("Tem certeza que deseja sair de sua conta?")) {
                try {
                    await fetch('api/auth.php?action=logout');
                    window.location.replace('login.html');
                } catch(err) {
                    window.location.replace('login.html');
                }
            }
        };
    }

    const loginView = document.getElementById('login-view');
    const registerView = document.getElementById('register-view');
    const btnShowRegister = document.getElementById('show-register');
    const btnShowLogin = document.getElementById('show-login');
    const tabLogin = document.getElementById('tab-login');
    const tabRegister = document.getElementById('tab-register');
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    
    window.togglePassword = (inputId, iconElement) => {
        const input = document.getElementById(inputId);
        if (input.type === 'password') {
            input.type = 'text';
            iconElement.classList.remove('bx-show');
            iconElement.classList.add('bx-hide');
        } else {
            input.type = 'password';
            iconElement.classList.remove('bx-hide');
            iconElement.classList.add('bx-show');
        }
    };
    
    const switchToRegister = () => {
        loginView.style.display = 'none';
        registerView.style.display = 'block';
        tabLogin.classList.remove('active');
        tabRegister.classList.add('active');
        window.history.replaceState(null, '', '?action=register');
    };
    
    const switchToLogin = () => {
        registerView.style.display = 'none';
        loginView.style.display = 'block';
        tabRegister.classList.remove('active');
        tabLogin.classList.add('active');
        window.history.replaceState(null, '', '?action=login');
    };
    
    if(tabRegister) tabRegister.addEventListener('click', switchToRegister);
    if(tabLogin) tabLogin.addEventListener('click', switchToLogin);
    if(btnShowRegister) btnShowRegister.addEventListener('click', (e) => {
        e.preventDefault();
        switchToRegister();
    });
    if(btnShowLogin) btnShowLogin.addEventListener('click', (e) => {
        e.preventDefault();
        switchToLogin();
    });

    fetch('api/auth.php?action=check')
        .then(r => r.json())
        .then(data => {
            if(data.loggedIn) {
                if(document.querySelector('.auth-card')) document.querySelector('.auth-card').style.maxWidth = '900px';
                if(document.querySelector('.auth-tabs')) document.querySelector('.auth-tabs').style.display = 'none';
                if(loginView) loginView.style.display = 'none';
                if(registerView) registerView.style.display = 'none';
                
                const profileView = document.getElementById('profile-view');
                if(profileView) {
                    profileView.style.display = 'block';
                    if(document.getElementById('prof-nome')) document.getElementById('prof-nome').value = data.name;
                    if(document.getElementById('sec-email')) document.getElementById('sec-email').value = data.email || '';
                    if(document.getElementById('sec-cpf') && data.cpf) document.getElementById('sec-cpf').value = data.cpf;
                    if(document.getElementById('sec-telefone') && data.telefone) document.getElementById('sec-telefone').value = data.telefone;
                    
                    const welcomeName = document.getElementById('prof-welcome-name');
                    if(welcomeName) welcomeName.innerText = data.name;
                    
                    const picPreview = document.getElementById('prof-pic-preview');
                    if(picPreview) {
                        if(data.profile_picture) {
                            picPreview.src = data.profile_picture;
                        } else {
                            picPreview.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(data.name)}&background=random`;
                        }
                    }
                    loadUserOrders(data.id);
                    loadMiniPromos();
                    setupWhatsAppSupport();
                    
                    const toggleSecurityBtn = document.getElementById('btn-toggle-security');
                    const securityContent = document.getElementById('security-content');
                    const securityChevron = document.getElementById('security-chevron');
                    if(toggleSecurityBtn && securityContent) {
                        toggleSecurityBtn.addEventListener('click', function() {
                            if(securityContent.style.display === 'none') {
                                securityContent.style.display = 'block';
                                if(securityChevron) securityChevron.classList.add('rotate');
                            } else {
                                securityContent.style.display = 'none';
                                if(securityChevron) securityChevron.classList.remove('rotate');
                            }
                        });
                    }
                }
            } else {
                const urlParams = new URLSearchParams(window.location.search);
                if(urlParams.get('action') === 'register') {
                    switchToRegister();
                }
                if (urlParams.has('error')) {
                    const error = urlParams.get('error');
                    let msg = 'Ocorreu um erro na autenticação.';
                    if (error === 'no_token') msg = 'Token do Google não recebido.';
                    else if (error === 'invalid_token') msg = 'Token do Google inválido.';
                    else msg = decodeURIComponent(error);
                    if (window.showToast) {
                        window.showToast(msg, 'error');
                    } else {
                        alert(msg);
                    }
                    window.history.replaceState(null, '', window.location.pathname);
                }
            }
        });

    if(loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('login-email').value;
            const password = document.getElementById('login-password').value;
            try {
                const res = await fetch('api/auth.php?action=login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, password })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    window.showToast(data.message, 'success');
                    setTimeout(() => {
                        window.location.href = data.role === 'admin' ? 'admin.php' : 'index.php';
                    }, 1000);
                } else if (data.require_verification) {
                    window.showToast(data.message, 'warning');
                    setTimeout(() => {
                        window.location.href = `verificar.html?email=${encodeURIComponent(data.email)}`;
                    }, 1500);
                } else {
                    window.showToast(data.message, 'error');
                }
            } catch (err) {
                window.showToast('Erro ao se conectar ao servidor.', 'error');
            }
        });
    }

    const isCPFValid = (cpf) => {
        cpf = cpf.replace(/[^\d]+/g,'');
        if(cpf == '') return false;
        if (cpf.length != 11 ||
            cpf == "00000000000" || cpf == "11111111111" ||
            cpf == "22222222222" || cpf == "33333333333" ||
            cpf == "44444444444" || cpf == "55555555555" ||
            cpf == "66666666666" || cpf == "77777777777" ||
            cpf == "88888888888" || cpf == "99999999999")
                return false;
        let add = 0;
        for (let i=0; i < 9; i ++) add += parseInt(cpf.charAt(i)) * (10 - i);
        let rev = 11 - (add % 11);
        if (rev == 10 || rev == 11) rev = 0;
        if (rev != parseInt(cpf.charAt(9))) return false;
        add = 0;
        for (let i = 0; i < 10; i ++) add += parseInt(cpf.charAt(i)) * (11 - i);
        rev = 11 - (add % 11);
        if (rev == 10 || rev == 11) rev = 0;
        if (rev != parseInt(cpf.charAt(10))) return false;
        return true;
    };

    if(registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const name = document.getElementById('reg-name').value;
            const cpf = document.getElementById('reg-cpf').value;
            const telefone = document.getElementById('reg-telefone').value;
            const email = document.getElementById('reg-email').value;
            const password = document.getElementById('reg-password').value;
            const confirmPassword = document.getElementById('reg-password-confirm').value;
            if (!isCPFValid(cpf)) {
                window.showToast('Por favor, informe um CPF válido.', 'error');
                return;
            }
            const passRegex = /^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
            if (!passRegex.test(password)) {
                window.showToast('A senha não atende aos requisitos de segurança.', 'error');
                return;
            }
            if (password !== confirmPassword) {
                window.showToast('As senhas não coincidem.', 'error');
                return;
            }
            try {
                const res = await fetch('api/auth.php?action=register', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ name, cpf, telefone, email, password })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    window.showToast(data.message, 'success');
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1500);
                } else {
                    window.showToast(data.message, 'error');
                }
            } catch (err) {
                window.showToast('Erro de conexão.', 'error');
            }
        });
    }

    const picInput = document.getElementById('prof-pic-input');
    const picPreview = document.getElementById('prof-pic-preview');
    const btnEditPic = document.getElementById('btn-edit-pic');
    const btnDeletePic = document.getElementById('btn-delete-pic');
    
    window.pendingProfilePic = null;
    window.deleteProfilePic = false;

    if(btnEditPic && picInput) {
        btnEditPic.onclick = () => picInput.click();
    }
    if(btnDeletePic && picPreview) {
        btnDeletePic.onclick = () => {
            const userName = document.getElementById('prof-nome').value || 'User';
            picPreview.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(userName)}&background=random`;
            window.pendingProfilePic = null;
            window.deleteProfilePic = true;
            if(picInput) picInput.value = '';
            window.showToast("Foto removida. Salve o perfil para confirmar.", "success");
        };
    }
    if(picInput) {
        picInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = new Image();
                    img.onload = function() {
                        const canvas = document.createElement('canvas');
                        const ctx = canvas.getContext('2d');
                        const MAX_WIDTH = 300;
                        const MAX_HEIGHT = 300;
                        let width = img.width;
                        let height = img.height;
                        if (width > height) {
                          if (width > MAX_WIDTH) {
                            height *= MAX_WIDTH / width;
                            width = MAX_WIDTH;
                          }
                        } else {
                          if (height > MAX_HEIGHT) {
                            width *= MAX_HEIGHT / height;
                            height = MAX_HEIGHT;
                          }
                        }
                        canvas.width = width;
                        canvas.height = height;
                        ctx.drawImage(img, 0, 0, width, height);
                        const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
                        picPreview.src = dataUrl;
                        window.pendingProfilePic = dataUrl;
                        window.deleteProfilePic = false;
                    };
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    const profileForm = document.getElementById('profile-form');
    if(profileForm) {
        profileForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const name = document.getElementById('prof-nome').value.trim();
            const btn = e.target.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> Salvando...";
            btn.disabled = true;
            const fd = new FormData();
            fd.append('name', name);
            if(window.pendingProfilePic) fd.append('profile_picture', window.pendingProfilePic);
            if(window.deleteProfilePic) fd.append('delete_photo', '1');
            
            try {
                const res = await fetch('api/auth.php?action=update_profile', { method: 'POST', body: fd });
                const json = await res.json();
                if (json.status === 'success') {
                    window.showToast(json.message, 'success');
                    setTimeout(() => window.location.href = 'index.php', 1000);
                } else {
                    window.showToast(json.message, 'error');
                }
            } catch(err) {
                window.showToast("Erro ao salvar.", 'error');
            }
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }

    const securityForm = document.getElementById('security-form');
    if(securityForm) {
        securityForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('sec-email').value.trim();
            const cpf = document.getElementById('sec-cpf').value.trim();
            const telefone = document.getElementById('sec-telefone').value.trim();
            const currentPassword = document.getElementById('sec-senha-atual').value;
            const newPassword = document.getElementById('sec-nova-senha').value;
            const confirmPassword = document.getElementById('sec-repetir-senha').value;
            const btn = e.target.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> Alterando...";
            btn.disabled = true;
            
            if (currentPassword || newPassword || confirmPassword) {
                if (!currentPassword || !newPassword || !confirmPassword) {
                    window.showToast("Para alterar a senha, preencha todos os campos de senha.", 'error');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    return;
                }
            }
            
            if (email || cpf || telefone || newPassword) {
                const fd = new FormData();
                if(email) fd.append('email', email);
                if(cpf) fd.append('cpf', cpf);
                if(telefone) fd.append('telefone', telefone);
                if(currentPassword) fd.append('current_password', currentPassword);
                if(newPassword) fd.append('new_password', newPassword);
                if(confirmPassword) fd.append('confirm_password', confirmPassword);
                
                try {
                    const res = await fetch('api/auth.php?action=update_security', { method: 'POST', body: fd });
                    const json = await res.json();
                    if (json.status === 'success') {
                        window.showToast(json.message, 'success');
                        document.getElementById('sec-senha-atual').value = '';
                        document.getElementById('sec-nova-senha').value = '';
                        document.getElementById('sec-repetir-senha').value = '';
                    } else {
                        window.showToast(json.message, 'error');
                    }
                } catch(err) {
                    window.showToast("Erro ao atualizar.", 'error');
                }
            } else {
                window.showToast("Preencha pelo menos um campo para atualizar.", 'error');
            }
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }

    async function loadUserOrders(userId) {
        const list = document.getElementById('profile-orders-list');
        if(!list) return;
        if (!userId) {
            list.innerHTML = `<p style="color: var(--clr-text-light); font-size: 0.9rem; text-align: center; padding: 2rem 0;">Você ainda não tem nenhum pedido realizado.</p>`;
            return;
        }
        try {
            const userOrders = await window.OrderManager.getByUser(userId);
            if (!Array.isArray(userOrders) || userOrders.length === 0) {
                list.innerHTML = `<p style="color: var(--clr-text-light); font-size: 0.9rem; text-align: center; padding: 2rem 0;">Você ainda não tem nenhum pedido realizado.</p>`;
                return;
            }
            const filtered = userOrders.filter(o => String(o.user_id) === String(userId));
            if(filtered.length === 0) {
                list.innerHTML = `<p style="color: var(--clr-text-light); font-size: 0.9rem; text-align: center; padding: 2rem 0;">Você ainda não tem nenhum pedido realizado.</p>`;
                return;
            }
            
            list.style.maxHeight = '400px';
            list.style.overflowY = 'auto';
            
            let html = '';
            for (const o of filtered) {
                const dateObj = new Date(o.created_at);
                const dateStr = dateObj.toLocaleDateString('pt-BR');
                let items = [];
                try { items = typeof o.items_json === 'string' ? JSON.parse(o.items_json) : o.items_json; } catch(e) { items = []; }
                const firstItem = items[0] || {};
                let itemImage = firstItem.image || 'assets/img/logoPNG.png';
                const itemsMsg = items.map(i => `${i.quantity}x ${i.name}`).join(', ');
                const isEntregue = o.status === 'entregue' || o.status === 'concluido';
                const statusColor = isEntregue ? '#10B981' : '#F59E0B';
                const statusBg = isEntregue ? 'rgba(16, 185, 129, 0.1)' : 'rgba(245, 158, 11, 0.1)';
                const statusText = o.status.charAt(0).toUpperCase() + o.status.slice(1);
                const total = window.formatCurrency(o.total);
                const deleteBtn = isEntregue ? `<button onclick="window.deleteUserOrder(${o.id}, ${userId})" style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); background: #EF4444; color: white; border: none; border-radius: var(--radius-full); width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1rem;"><i class='bx bx-trash'></i></button>` : '';
                html += `<div style="display: flex; gap: 1rem; border-bottom: 1px solid var(--clr-border); padding: 1rem 0; align-items: center; position: relative;" id="order-${o.id}">
                        <img src="${itemImage}" alt="Produto" style="width: 70px; height: 70px; object-fit: cover; border-radius: var(--radius-md); border: 1px solid var(--clr-border);">
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                <div>
                                    <strong style="font-size: 1rem;">Pedido #${o.id}</strong>
                                    <div style="font-size: 0.8rem; color: var(--clr-text-light);">${dateStr}</div>
                                </div>
                                <span style="display:inline-block; padding:0.25rem 0.5rem; border-radius:1rem; background:${statusBg}; color:${statusColor}; font-size:0.75rem; font-weight:600;">${statusText}</span>
                            </div>
                            <div style="font-size: 0.85rem; color: var(--clr-text-light); margin-bottom: 0.5rem; line-height: 1.4;">${itemsMsg}</div>
                            <div style="font-weight: 700; color: var(--clr-primary); font-size: 0.95rem;">${total}</div>
                        </div>
                        ${deleteBtn}
                    </div>`;
            }
            list.innerHTML = html;
        } catch (e) {
            console.error("Erro ao carregar pedidos:", e);
            list.innerHTML = `<p style="color: var(--clr-text-light); font-size: 0.9rem; text-align: center; padding: 2rem 0;">Erro ao carregar pedidos.</p>`;
        }
    }

    window.deleteUserOrder = async function(orderId, userId) {
        if(!confirm("Tem certeza que deseja excluir este pedido?")) return;
        try {
            const result = await window.OrderManager.deleteUserOrder(orderId, userId);
            if (result && result.status === 'success') {
                window.showToast("Pedido excluído com sucesso!", "success");
                loadUserOrders(userId);
            } else {
                window.showToast(result?.message || "Erro ao excluir pedido.", "error");
            }
        } catch(e) { window.showToast("Erro ao excluir pedido.", "error"); }
    };

    async function loadMiniPromos() {
        const list = document.getElementById('profile-promos-list');
        if(!list) return;
        try {
            const allProducts = await window.ProductManager.getAll();
            const promoProducts = allProducts.filter(p => {
                const cat = (p.category || "").toLowerCase();
                return cat.includes('promo') || cat === 'promocoes';
            });
            if(promoProducts.length === 0) {
                list.innerHTML = `<p style="color: var(--clr-text-light); font-size: 0.85rem; padding: 0.5rem 0;">Nenhuma promoção ativa no momento.</p>`;
                return;
            }
            list.innerHTML = promoProducts.slice(0, 6).map(p => `
                <a href="detalhes.html?id=${p.id}" class="promo-mini-card">
                    <img src="${p.image}" alt="${p.name}">
                    <h4 title="${p.name}">${p.name}</h4>
                    <span>${window.formatCurrency(p.price)}</span>
                </a>
            `).join('');
        } catch (e) { console.error("Erro ao carregar promoções:", e); }
    }

    function setupWhatsAppSupport() {
        const btn = document.getElementById('profile-wa-btn');
        if(btn) {
            let waNum = window.ConfigManager.get('whatsappNumber') || '+5598985269184';
            waNum = waNum.replace(/\D/g, '');
            if (waNum && !waNum.startsWith('55') && waNum.length <= 11) waNum = '55' + waNum;
            btn.href = `https://wa.me/${waNum}?text=Ol%C3%A1!%20Me%20chamo%20${encodeURIComponent(window.userName || '')}%20e%20preciso%20de%20um%20suporte%20sobre%20meu%20pedido.`;
        }
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLogin);
} else {
    initLogin();
}
