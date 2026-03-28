const normalizeString = (str) => (str || "").normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
function initApp() {
  initTheme();
  initNotifications();
  injectSearchOverlay();
  injectMobileNav();
  updateCartBadge();
  checkAuth();

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
    const userLinks = document.querySelectorAll('.action-btn[href="login.html"], .action-btn[href="perfil.php"]');
    if (data.loggedIn) {
        let pic = data.profile_picture;
        if (pic && !pic.startsWith('http') && !pic.startsWith('api/') && pic.startsWith('uploads/')) {
            pic = 'api/uploads.php?file=' + pic.replace('uploads/', '');
        }
        const name = data.name || 'User';
        const fallback = `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=random&color=fff`;
        
        userLinks.forEach(btn => {
            btn.href = 'perfil.php';
            btn.innerHTML = `<img src="${pic || fallback}" style="width: 28px; height: 28px; border-radius: 50%; object-fit: cover; border: 2px solid var(--clr-accent);" title="Meu Perfil">`;
        });
    } else {
        userLinks.forEach(btn => {
            btn.href = 'login.html';
            btn.innerHTML = `<i class='bx bx-user' title="Minha Conta" style="font-size: 1.5rem; color: var(--clr-accent);"></i>`;
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
    if (profPic && !profPic.startsWith('http') && !profPic.startsWith('api/') && profPic.startsWith('uploads/')) {
        profPic = 'api/uploads.php?file=' + profPic.replace('uploads/', '');
    }
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
            window.location.href = `produtos.html?q=${encodeURIComponent(query)}`;
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
        if (item) window.location.href = `detalhes.html?id=${item.dataset.id}`;
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
              window.location.href = `detalhes.html?id=${productId}`;
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
        window.location.href = 'carrinho.html';
    }
};
