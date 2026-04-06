const normalizeString = (str) => (str || "").normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();

window.SmartSearch = {
    _products: null,
    _levenshtein: function(a, b) {
        if (a.length === 0) return b.length;
        if (b.length === 0) return a.length;
        const matrix = [];
        for (let i = 0; i <= b.length; i++) matrix[i] = [i];
        for (let j = 0; j <= a.length; j++) matrix[0][j] = j;
        for (let i = 1; i <= b.length; i++) {
            for (let j = 1; j <= a.length; j++) {
                if (b.charAt(i - 1) == a.charAt(j - 1)) {
                    matrix[i][j] = matrix[i - 1][j - 1];
                } else {
                    matrix[i][j] = Math.min(matrix[i - 1][j - 1] + 1, Math.min(matrix[i][j - 1] + 1, matrix[i - 1][j] + 1));
                }
            }
        }
        return matrix[b.length][a.length];
    },
    _fuzzyMatch: function(query, text) {
        const q = normalizeString(query);
        const t = normalizeString(text);
        if (t.includes(q)) return 100;
        
        const qWords = q.split(/\s+/).filter(w => w.length > 0);
        const tWords = t.split(/\s+/).filter(w => w.length > 0);
        
        let matchedWords = 0;
        for (let qw of qWords) {
            let bestDist = 999;
            for (let tw of tWords) {
                if (tw.startsWith(qw) || tw.includes(qw)) bestDist = 0;
                else bestDist = Math.min(bestDist, this._levenshtein(qw, tw));
            }
            if (bestDist <= (qw.length > 4 ? 2 : 1)) matchedWords++;
        }
        if (matchedWords === qWords.length && qWords.length > 0) return 60;
        return 0;
    },
    load: async function() {
        if (this._products) return this._products;
        try {
            const data = await window.ProductManager.getAll({ limit: 500 });
            this._products = (data.products || []).map(p => ({
                id: p.id, name: p.name, price: p.price,
                image: (p.image_url) ? p.image_url : ((p.image && p.image.startsWith('data:image')) ? 'assets/img/logoPNG.png' : (p.image || 'assets/img/logoPNG.png'))
            }));
            return this._products;
        } catch (e) { return []; }
    },
    setup: function(inputEl, resultsEl) {
        if (!inputEl || !resultsEl) return;
        
        // Disable browser autocomplete dropdown
        inputEl.setAttribute('autocomplete', 'off');
        inputEl.setAttribute('spellcheck', 'false');

        const parent = inputEl.closest('.header-search-container') || inputEl.parentElement;
        if (parent) {
            parent.style.position = 'relative';
        }
        
        // Inject a dedicated overlay for results that matches the site's dark layout
        resultsEl.style.cssText = 'display: none; position: absolute; top: calc(100% + 5px); left: 0; width: 100%; '+
            'background: rgba(0, 20, 20, 0.95); backdrop-filter: blur(15px); border: 1px solid rgba(0, 212, 212, 0.2); '+
            'border-radius: 14px; padding: 0.5rem; z-index: 10000; box-shadow: 0 10px 30px rgba(0,0,0,0.5); '+
            'max-height: 400px; overflow-y: auto;';

        const doSearch = () => {
            const query = inputEl.value.trim();
            if (query) {
                window.location.href = `${window.APP_URL || ''}/produtos?q=${encodeURIComponent(query)}`;
            }
        };

        const form = inputEl.closest('form');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                doSearch();
            });
        }

        inputEl.addEventListener('input', async () => {
            const query = inputEl.value.trim();
            if (query.length < 1) { 
                resultsEl.style.display = 'none';
                return; 
            }
            
            const products = await this.load();
            const matches = products.filter(p => this._fuzzyMatch(query, p.name) > 0).slice(0, 6);
            
            if (matches.length === 0) {
                resultsEl.innerHTML = `<div style="padding: 1rem; color: #888; text-align: center; font-size: 0.85rem;">Nenhum produto encontrado</div>`;
            } else {
                resultsEl.innerHTML = matches.map(p => {
                    const price = window.formatCurrency(parseFloat(p.price));
                    return `<div class="search-sugg-item" data-id="${p.id}" style="display:flex; padding: 0.6rem; border-bottom: 1px solid rgba(255,255,255,0.05); cursor: pointer; align-items: center; gap: 0.8rem; border-radius: 8px; transition: background 0.2s;">
                        <img src="${p.image}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">
                        <div style="flex: 1; min-width:0;">
                            <div style="font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #fff; font-size: 0.85rem;">${p.name}</div>
                            <div style="color: var(--clr-primary, #00d4d4); font-weight: 800; font-size: 0.8rem;">${price}</div>
                        </div>
                    </div>`;
                }).join('');
                
                resultsEl.querySelectorAll('.search-sugg-item').forEach(el => {
                    el.addEventListener('mouseover', () => el.style.background = 'rgba(255,255,255,0.05)');
                    el.addEventListener('mouseout', () => el.style.background = 'transparent');
                });
            }
            resultsEl.style.display = 'block';
        });
        
        resultsEl.addEventListener('click', (e) => {
            const item = e.target.closest('.search-sugg-item');
            if (item) {
                window.location.href = `${window.APP_URL || ''}/detalhes/${item.dataset.id}`;
            }
        });

        document.addEventListener('click', (e) => {
            if (!inputEl.contains(e.target) && !resultsEl.contains(e.target)) {
                resultsEl.style.display = 'none';
            }
        });
    }
};

function initApp() {
  initTheme();
  initNotifications();
  injectSearchOverlay();
  injectMobileNav();
  updateCartBadge();
  checkAuth();

  const headSearchInput = document.getElementById('header-search-input');
  const headSearchSuggestions = document.getElementById('header-search-suggestions');
  if (headSearchInput && headSearchSuggestions) {
      window.SmartSearch.setup(headSearchInput, headSearchSuggestions);
  }

  const setupWhatsAppFab = async () => {
      if (window.ConfigManager) {
          await window.ConfigManager.waitReady();
          let num = window.ConfigManager.get('whatsappNumber') || '5598985269184';
          num = String(num).replace(/\D/g, '');
          if (num && !num.startsWith('55') && num.length <= 11) num = '55' + num;
          const fab = document.querySelector('.whatsapp-fab');
          if (fab) {
              fab.href = `https://wa.me/${num}?text=${encodeURIComponent('Olá! Vim pelo site da Infinity Variedades e gostaria de um atendimento.')}`;
          }
      }
  };
  setupWhatsAppFab();

  const prefetchLinks = () => {
    const links = document.querySelectorAll('a[href$=".html"], a[href$=".php"]');
    links.forEach(link => {
      link.addEventListener('mouseenter', () => {
        const url = link.href;
        if (url && !url.includes('#') && !document.querySelector(`link[href="${url}"]`)) {
          const prefetch = document.createElement('link');
          prefetch.rel = 'prefetch';
          prefetch.href = url;
          document.head.appendChild(prefetch);
        }
      }, { once: true });
    });
  };
  
  prefetchLinks();
  window.addEventListener('cartUpdated', updateCartBadge);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initApp);
} else {
    initApp();
}
function initTheme() {
  document.documentElement.setAttribute('data-theme', 'dark');
}
function initNotifications() {
  console.log("initNotifications called");
  const notifWrapper = document.getElementById('notif-wrapper');
  const notifBtn = document.getElementById('notification-btn');
  const notifDropdown = document.getElementById('notification-dropdown');
  console.log("Elements:", notifWrapper, notifBtn, notifDropdown);
  if (!notifWrapper || !notifBtn || !notifDropdown) {
    console.log("Notificações não disponíveis nesta página");
    return;
  }
  
  notifBtn.addEventListener('click', (e) => {
    console.log("Botão de notificação clicado!");
    e.stopPropagation();
    notifDropdown.classList.toggle('active');
    console.log("Dropdown active class:", notifDropdown.classList.contains('active'));
  });
  
  document.addEventListener('click', (e) => {
    if (!notifWrapper.contains(e.target)) {
      notifDropdown.classList.remove('active');
    }
  });

  loadDynamicNotifications();
  
  const markReadBtn = document.getElementById('mark-read-btn');
  if (markReadBtn) {
    markReadBtn.addEventListener('click', () => {
      const list = document.getElementById('notification-list');
      if (list) list.innerHTML = '';
      const badge = document.getElementById('notification-badge');
      if (badge) {
        badge.style.display = 'none';
        badge.textContent = '0';
      }
    });
  }
}

async function loadDynamicNotifications() {
  const list = document.getElementById('notification-list');
  const badge = document.getElementById('notification-badge');
  if (!list || !badge) return;

  try {
    const response = await fetch('api/products.php?action=list&limit=50');
    const data = await response.json();
    const products = data.products || [];

    // Filter for "Novidades" and "Promoções"
    const news = products.filter(p => {
        const cat = normalizeString(p.category);
        const hasDiscount = (parseFloat(p.original_price) > parseFloat(p.price)) || (parseInt(p.discount_percent) > 0);
        return cat.includes('novidade') || cat.includes('promocao') || hasDiscount;
    }).slice(0, 5); // Show latest 5

    if (news.length === 0) {
      list.innerHTML = `
        <div class="notification-empty">
          <i class='bx bx-bell-off'></i>
          Nada de novo por aqui agora.
        </div>
      `;
      badge.style.display = 'none';
      return;
    }

    list.innerHTML = news.map(p => {
      const isPromo = normalizeString(p.category).includes('promocao');
      const tagClass = isPromo ? 'cat-promocao' : 'cat-novidade';
      const tagText = isPromo ? 'Oferta' : 'Novo';
      const imgSrc = p.image || 'assets/img/logoPNG.png';
      
      const priceVal = parseFloat(p.price) || 0;
      const originalPriceVal = parseFloat(p.original_price) || 0;
      const dbDiscount = parseInt(p.discount_percent) || 0;
      let discountBadge = '';
      
      // Calculate or use DB discount
      const finalDiscount = dbDiscount > 0 ? dbDiscount : (originalPriceVal > priceVal ? Math.round((1 - priceVal / originalPriceVal) * 100) : 0);
      
      if (finalDiscount > 0) {
          discountBadge = `<span style="background: #ff4b2b; color: #fff; font-size: 0.65rem; padding: 2px 5px; border-radius: 4px; font-weight: 900; margin-left: 6.5px;">-${finalDiscount}%</span>`;
      }
      
      return `
        <a href="${window.APP_URL || ''}/detalhes/${p.id}" class="notification-item">
          <div class="notif-icon-box" style="background: #fff; border: 1px solid rgba(255,255,255,0.1); overflow: hidden;">
            <img src="${imgSrc}" onerror="this.src='assets/img/logoPNG.png'" style="width: 100%; height: 100%; object-fit: cover;">
          </div>
          <div class="notif-content">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <span class="notif-category ${tagClass}">${tagText}</span>
                ${discountBadge}
            </div>
            <span class="notif-name">${p.name}</span>
                <span style="font-size: 0.68rem; color: ${parseInt(p.stock_quantity || 0) <= 5 ? '#ef4444' : '#ccc'}; font-weight: 600;">| ${parseInt(p.stock_quantity || 0)} em estoque</span>
            </div>
          </div>
        </a>
      `;
    }).join('');

    badge.badgeCount = news.length;
    badge.textContent = news.length;
    badge.style.display = 'flex';

  } catch (err) {
    console.error("Erro ao carregar notificações:", err);
    list.innerHTML = '<div class="notification-empty">Erro ao carregar novidades.</div>';
  }
}

function updateCartBadge() {
  const badge = document.getElementById('cart-badge');
  const mobileBadge = document.getElementById('cart-badge-mobile');
  if (!window.CartManager) return;
  
  const totalItems = window.CartManager.getTotalItems();
  const formatItems = totalItems > 99 ? '99+' : totalItems;
  
  console.log(`[CartDebug] Total items: ${totalItems}, Format: ${formatItems}, UserID: ${window.userId}`);

  const applyUpdate = (el) => {
    if (!el) return;
    const isNewValue = el.textContent !== String(formatItems);
    el.textContent = formatItems;
    el.style.display = totalItems > 0 ? 'flex' : 'none';
    
    if (totalItems > 0 && isNewValue) {
      el.classList.remove('pop');
      void el.offsetWidth;
      el.classList.add('pop');
    }
  };

  applyUpdate(badge);
  applyUpdate(mobileBadge);
}
window.isLoggedIn = false;
window.userId = null;
window.userName = null;
window.authChecked = false;
async function checkAuth() {
  try {
    const response = await fetch(`api/auth.php?action=check&t=${Date.now()}`);
    const data = await response.json();
    window.isLoggedIn = data.loggedIn;
    window.userId = data.id || null;
    window.userName = data.name || null;
    window.userRole = data.role || 'cliente';
    window.profilePicture = data.profile_picture || null;
    window.authChecked = true;
    if (window.userId && window.CartManager) {
        window.CartManager.mergeGuestCart();
    }
    window.dispatchEvent(new Event('cartUpdated'));
    injectMobileNav();
    updateUserIcons(data);
  } catch (err) {
      console.error("Erro ao verificar auth:", err);
  } finally {
      window.authChecked = true;
  }
}

function updateUserIcons(data) {
    const userLinks = document.querySelectorAll('.action-btn[href="login.html"], .action-btn[href="perfil.php"], .action-btn[title="Meu Perfil"], .action-btn[title="Login"], #auth-modal-target-btn');
    if (data.loggedIn) {
        let pic = data.profile_picture;
        // Keep relative path so it respects subdirectory deployments like XAMPP htdocs
        userLinks.forEach(btn => {
            if (btn.tagName === 'A') btn.href = window.APP_URL ? window.APP_URL + '/perfil' : 'perfil';
            if (pic) {
                btn.innerHTML = `<img src="${pic}" style="width: 28px; height: 28px; border-radius: 50%; object-fit: cover; border: 2px solid var(--clr-accent);" title="Meu Perfil">`;
            } else {
                btn.innerHTML = `<i class='bx bx-user-circle' title="Meu Perfil" style="font-size: 1.6rem; color: var(--clr-accent);"></i>`;
            }
        });
    } else {
        userLinks.forEach(btn => {
            if (btn.tagName === 'A') btn.href = window.APP_URL ? window.APP_URL + '/login' : 'login';
            btn.innerHTML = `<i class='bx bx-user-circle' title="Minha Conta" style="font-size: 1.6rem; color: var(--clr-accent);"></i>`;
        });
    }
}
function injectMobileNav() {
    let nav = document.querySelector('.mobile-bottom-nav');
    if (!nav) {
        nav = document.createElement('nav');
        nav.className = 'mobile-bottom-nav';
        document.body.appendChild(nav);
    }
    const path = window.location.pathname;
    const getProfileLabel = () => {
        if (!window.isLoggedIn) return 'Entrar';
        if (window.userRole === 'admin') return 'ADM';
        const firstName = window.userName ? window.userName.split(' ')[0] : 'Conta';
        return firstName.length > 10 ? '' : firstName;
    };

    const profileLabel = getProfileLabel();
    let profPic = window.profilePicture;
    // Keep relative path so it respects subdirectory deployments like XAMPP htdocs
    const profileIconHtml = (window.isLoggedIn && profPic) 
        ? `<img src="${profPic}" class="mobile-nav-profile-img" alt="Perfil">`
        : `<i class='bx bx-user'></i>`;

    nav.innerHTML = `
        <a href="index.php" class="mobile-nav-item ${path.endsWith('index.php') || path === '/' ? 'active' : ''}">
            <i class='bx bx-home-alt'></i>
            <span>Início</span>
        </a>
        <a href="produtos.html" class="mobile-nav-item ${path.endsWith('produtos.html') ? 'active' : ''}">
            <i class='bx bx-grid-alt'></i>
            <span>Achei</span>
        </a>
        <a href="carrinho.html" class="mobile-nav-item ${path.endsWith('carrinho.html') ? 'active' : ''}" style="position: relative;">
            <i class='bx bx-cart-alt'></i>
            <span>Carrinho</span>
            <span id="cart-badge-mobile" class="cart-badge" style="top: 0px; right: 10px; display: none;">0</span>
        </a>
        <a href="perfil.php" class="mobile-nav-item ${path.endsWith('perfil.php') || path.endsWith('login.html') ? 'active' : ''}">
            ${profileIconHtml}
            ${profileLabel ? `<span>${profileLabel}</span>` : ''}
        </a>
        <a href="admin.php" class="mobile-nav-item ${path.endsWith('admin.php') || path.endsWith('admin_config.php') || path.endsWith('admin_pedidos.php') ? 'active' : ''}" style="display: ${window.userRole === 'admin' ? 'flex' : 'none'};">
            <i class='bx bx-cog'></i>
            <span>Admin</span>
        </a>
    `;
}
function injectSearchOverlay() {
    if (document.getElementById('search-overlay')) return;
    const overlay = document.createElement('div');
    overlay.className = 'search-overlay';
    overlay.id = 'search-overlay';
    overlay.innerHTML = `
        <div class="search-form">
            <i class='bx bx-search search-submit-icon'></i>
            <input type="text" id="search-input-field" placeholder="O que você está procurando?" autocomplete="off">
            <button class="search-close-btn" id="search-close-btn"><i class='bx bx-x'></i></button>
            <div class="search-suggestions" id="search-suggestions"></div>
        </div>
    `;
    document.body.appendChild(overlay);
    const input = document.getElementById('search-input-field');
    const closeBtn = document.getElementById('search-close-btn');
    const suggestions = document.getElementById('search-suggestions');
    if (!input || !closeBtn || !suggestions) return;

    let allProducts = null;
    const loadAllProducts = async () => {
        if (allProducts) return;
        try {
            const res = await fetch('api/products.php?action=list&limit=500');
            const data = await res.json();
            allProducts = data.products || [];
        } catch(e) { allProducts = []; }
    };
    const filterLocal = (query) => {
        const q = normalizeString(query);
        return (allProducts || []).filter(p =>
            normalizeString(p.name).includes(q) ||
            (p.description && normalizeString(p.description).includes(q))
        );
    };
    const handleSearch = (query) => {
        query = query || input.value.trim();
        if (query) {
            hideSuggestions();
            window.location.href = `${window.APP_URL || ''}/produtos?q=${encodeURIComponent(query)}`;
        }
    };
    const hideSuggestions = () => suggestions.classList.remove('visible');
    const showSuggestions = (products, query) => {
        if (!products || products.length === 0) {
            suggestions.innerHTML = `<div class="suggestion-empty">Nenhum produto encontrado</div>`;
        } else {
            suggestions.innerHTML = products.slice(0, 5).map(p => {
                const imgSrc = p.image || 'assets/img/logoPNG.png';
                const priceVal = parseFloat(p.price) || 0;
                const price = priceVal.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                return `<div class="suggestion-item" data-id="${p.id}" data-name="${p.name}">
                    <img class="suggestion-img" src="${imgSrc}" onerror="this.src='assets/img/logoPNG.png'" alt="">
                    <div class="suggestion-info">
                        <div class="suggestion-name">${p.name}</div>
                        <div class="suggestion-price">${price}</div>
                    </div>
                </div>`;
            }).join('');
            if (products.length > 5) {
                suggestions.innerHTML += `<div class="suggestion-see-all" data-query="${query}">
                    <i class='bx bx-search-alt'></i> Ver todos os ${products.length} resultados
                </div>`;
            }
        }
        suggestions.classList.add('visible');
    };
    suggestions.addEventListener('click', (e) => {
        const item = e.target.closest('.suggestion-item');
        const seeAll = e.target.closest('.suggestion-see-all');
        if (item) window.location.href = `${window.APP_URL || ''}/detalhes/${item.dataset.id}`;
        else if (seeAll) handleSearch(seeAll.dataset.query);
    });

    input.addEventListener('input', () => {
        const query = input.value.trim();
        if (query.length < 1) { hideSuggestions(); return; }
        showSuggestions(filterLocal(query), query);
    });
    input.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') handleSearch();
    });
    document.addEventListener('click', (e) => {
        if (!overlay.contains(e.target) && !e.target.closest('#search-toggle')) hideSuggestions();
    });
    closeBtn.onclick = () => {
        overlay.classList.remove('active');
        hideSuggestions();
        input.value = '';
    };

    document.addEventListener('click', (e) => {
        const toggle = e.target.closest('#search-toggle');
        if (toggle) {
            overlay.classList.add('active');
            loadAllProducts();
            setTimeout(() => input.focus(), 80);
        }
    });

    // Close on ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && overlay.classList.contains('active')) {
            overlay.classList.remove('active');
            hideSuggestions();
            input.value = '';
        }
    });
}
window.showToast = function(message, type = 'success') {
  let container = document.querySelector('.toast-container');
  if (!container) {
    container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
  }
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  let icon = type === 'success' ? 'bx-check-circle' : 'bx-error-circle';
  toast.innerHTML = `
    <i class='bx ${icon}' style="font-size: 1.5rem; color: ${type === 'success' ? '#10B981' : '#EF4444'}"></i>
    <span>${message}</span>
  `;
  container.appendChild(toast);
  setTimeout(() => {
    toast.remove();
    if (container.children.length === 0) {
      container.remove();
    }
  }, 3500);
}
window.formatCurrency = (value) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value);
};
window.generateStars = (rating) => {
  let html = '';
  for (let i = 1; i <= 5; i++) {
    if (i <= rating) {
      html += "<i class='bx bxs-star'></i>";
    } else if (i - 0.5 <= rating) {
      html += "<i class='bx bxs-star-half'></i>";
    } else {
      html += "<i class='bx bx-star'></i>";
    }
  }
  return html;
};
window.handleAddToCart = async (productId, quantity = 1, color = null) => {
  /* Guest additions are now allowed for a better UX */
  
  if (!color) {
      const product = await window.ProductManager.getById(productId);
      if (product) {
          const cat = (product.category || '').toLowerCase();
          const colorCats = window.COLOR_CATEGORIES || [];
          if (colorCats.includes(cat)) {
              window.location.href = `${window.APP_URL || ''}/detalhes/${productId}`;
              return;
          }
      }
  }
  window.CartManager.add(productId, quantity, color);
  const product = await window.ProductManager.getById(productId);
  const colorInfo = color ? ` (${color})` : '';
  window.showToast(`${Number(quantity)}x ${product ? product.name : 'Produto'}${colorInfo} adicionado ao carrinho!`);
};
window.handleBuyNow = async (productId, quantity = 1, color = null) => {
    await window.handleAddToCart(productId, quantity, color);
    if (window.isLoggedIn) {
        window.location.href = `${window.APP_URL || ''}/carrinho`;
    }
};
