

document.addEventListener('DOMContentLoaded', () => {
  initTheme();
  updateCartBadge();
  injectChatbot();
  injectMobileNav();
  checkAuth();
  
  
  window.addEventListener('cartUpdated', updateCartBadge);
  
  
  const themeToggle = document.getElementById('theme-toggle');
  if (themeToggle) {
    themeToggle.addEventListener('click', () => {
      const currentTheme = document.documentElement.getAttribute('data-theme');
      const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
      document.documentElement.setAttribute('data-theme', newTheme);
      localStorage.setItem('papelaria_theme', newTheme);
      updateThemeIcon(newTheme);
    });
  }
});

function initTheme() {
  const themeKey = 'papelaria_theme';
  const savedTheme = localStorage.getItem(themeKey) || 'light';
  document.documentElement.setAttribute('data-theme', savedTheme);
  updateThemeIcon(savedTheme);
}

function updateThemeIcon(theme) {
  const icon = document.querySelector('#theme-toggle i');
  if (icon) {
    icon.className = theme === 'dark' ? 'bx bxs-sun' : 'bx bxs-moon';
  }
}

function updateCartBadge() {
  const badge = document.getElementById('cart-badge');
  const mobileBadge = document.getElementById('cart-badge-mobile');
  const totalItems = window.CartManager.getTotalItems();
  const formatItems = totalItems > 99 ? '99+' : totalItems;
  
  if (badge) {
    badge.textContent = formatItems;
    badge.style.display = totalItems > 0 ? 'flex' : 'none';
  }
  
  if (mobileBadge) {
    mobileBadge.textContent = formatItems;
    mobileBadge.style.display = totalItems > 0 ? 'inline-block' : 'none';
  }
}


window.isLoggedIn = false;
window.userId = null;
window.userName = null;
window.authChecked = false;

async function checkAuth() {
  try {
      const response = await fetch('api/auth.php?action=check');
      const data = await response.json();
      
      window.isLoggedIn = data.loggedIn;
      window.userId = data.id || null;
      window.userName = data.name || null;
      window.authChecked = true;
      window.dispatchEvent(new Event('cartUpdated'));
      
      const userBtns = document.querySelectorAll('a[href="login.html"]');
      if (data.loggedIn) {
          userBtns.forEach(btn => {
              if (data.profile_picture) {
                  btn.innerHTML = `<img src="${data.profile_picture}" style="width: 28px; height: 28px; border-radius: 50%; object-fit: cover; border: 2px solid var(--clr-accent);" title="Minha Conta">`;
              } else {
                  btn.innerHTML = `<i class='bx bxs-user-circle' title="Minha Conta" style="font-size: 1.5rem; color: var(--clr-accent);"></i>`;
              }
              // O href nativo para login.html funciona, dependendo do login.js para exibir o perfil
          });
          
          
          if(data.role === 'admin') {
              document.querySelectorAll('a[href="admin.php"]').forEach(el => el.style.display = 'flex');
          } else {
              document.querySelectorAll('a[href="admin.php"]').forEach(el => el.style.display = 'none');
          }
      } else {
          document.querySelectorAll('a[href="admin.php"]').forEach(el => el.style.display = 'none');
      }
  } catch (err) {
      console.error("Erro ao verificar auth:", err);
  } finally {
      if(document.querySelector('.mobile-bottom-nav')) {
          const profileIcon = document.getElementById('mobile-profile-icon');
          if(profileIcon) {
             profileIcon.className = window.isLoggedIn ? 'bx bx-user-check' : 'bx bx-user';
          }
      }
  }
}

function injectMobileNav() {
    if (document.querySelector('.mobile-bottom-nav')) return;
    const nav = document.createElement('nav');
    nav.className = 'mobile-bottom-nav';
    const path = window.location.pathname;
    
    nav.innerHTML = `
        <a href="index.html" class="mobile-nav-item ${path.endsWith('index.html') || path === '/' ? 'active' : ''}">
            <i class='bx bx-home-alt'></i>
            <span>Início</span>
        </a>
        <a href="produtos.html" class="mobile-nav-item ${path.endsWith('produtos.html') ? 'active' : ''}">
            <i class='bx bx-grid-alt'></i>
            <span>Achei</span>
        </a>
        <a href="carrinho.html" class="mobile-nav-item ${path.endsWith('carrinho.html') ? 'active' : ''}" style="position: relative;">
            <i class='bx bx-shopping-bag'></i>
            <span>Carrinho</span>
            <span id="cart-badge-mobile" style="position: absolute; top: 0px; right: 10px; background: var(--clr-accent); color: white; font-size: 10px; font-weight: bold; border-radius: 50%; padding: 2px 6px; display: none;">0</span>
        </a>
        <a href="login.html" class="mobile-nav-item ${path.endsWith('login.html') ? 'active' : ''}">
            <i class='bx bx-user' id="mobile-profile-icon"></i>
            <span>Minha Conta</span>
        </a>
    `;
    document.body.appendChild(nav);
}


function injectChatbot() {
  if (document.querySelector('.chatbot-fab')) return; // não duplicar
  
  
  const chatBtn = document.createElement('button');
  chatBtn.className = 'chatbot-fab';
  chatBtn.innerHTML = `<img src="assets/img/chatbot_mascot.png" alt="Chatbot" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">`;
  chatBtn.title = "Fale com nossa IA!";
  
  
  const chatWin = document.createElement('div');
  chatWin.className = 'chat-window';
  chatWin.id = 'chat-window';
  
  chatWin.innerHTML = `
      <div class="chat-header">
         <div class="chat-header-info">
             <img src="assets/img/chatbot_mascot.png" class="chat-avatar">
             <div>
                 <strong style="display:block; font-size:1rem;">Bolsinha IA</strong>
                 <small style="opacity: 0.8; font-size: 0.75rem;">Sempre online</small>
             </div>
         </div>
         <button class="chat-close-btn" id="chat-close-btn"><i class='bx bx-x'></i></button>
      </div>
      <div class="chat-body" id="chat-body">
         <div class="chat-msg ai">Olá! Eu sou a Bolsinha, sua IA assistente! 🎒✨<br>Posso te ajudar a encontrar os melhores materiais hoje?</div>
      </div>
      <div class="chat-footer">
         <input type="text" id="chat-input" class="chat-input" placeholder="Digite sua dúvida...">
         <button class="chat-send-btn" id="chat-send-btn"><i class='bx bx-send'></i></button>
      </div>
  `;
  
  document.body.appendChild(chatBtn);
  document.body.appendChild(chatWin);
  
  chatBtn.onclick = () => {
      chatWin.classList.add('active');
  };
  
  document.getElementById('chat-close-btn').onclick = () => {
      chatWin.classList.remove('active');
  };

// Modal de perfil removido conforme solicitado pelo usuário (movido para login.html)
  
  const chatInput = document.getElementById('chat-input');
  const chatSend = document.getElementById('chat-send-btn');
  const chatBody = document.getElementById('chat-body');
  
  const addMessage = (text, type) => {
      const msg = document.createElement('div');
      msg.className = `chat-msg ${type}`;
      if (type === 'user') {
          msg.textContent = text;
      } else {
          msg.innerHTML = text;
      }
      chatBody.appendChild(msg);
      chatBody.scrollTop = chatBody.scrollHeight;
  };

  const getAIResponse = async (userText) => {
      const lower = userText.toLowerCase();

      // Busca na Base de Dados
      if (window.ProductManager) {
          const products = await window.ProductManager.getAll();
          
          // Match Exato
          const exactMatch = products.find(p => p.name.toLowerCase() === lower);
          if (exactMatch) {
             return `Encontrei exatamente o que você procura! Veja: <br><br>
                     <div style="display:flex; align-items:center; gap: 10px; margin-top:5px; padding: 5px; border-radius: 5px; background: rgba(0,0,0,0.05);">
                        <img src="${exactMatch.image}" style="width:40px; height:40px; border-radius:5px; object-fit:cover;">
                        <div style="flex:1;">
                           <strong style="display:block; font-size:0.85rem;">${exactMatch.name}</strong>
                           <span style="color:var(--clr-primary); font-weight:bold;">${window.formatCurrency(exactMatch.price)}</span>
                        </div>
                        <a href="detalhes.html?id=${exactMatch.id}" class="btn btn-primary" style="padding: 5px 10px; font-size: 0.75rem;">Ver</a>
                     </div>`;
          }
          
          // Match Relacionado (Busca em Nome, Categoria e Descrição)
          // Ignora palavras curtas demais
          if (lower.length > 2) {
              const related = products.filter(p => {
                  const name = (p.name || "").toLowerCase();
                  const cat = (p.category || "").toLowerCase();
                  const desc = (p.description || "").toLowerCase();
                  return name.includes(lower) || cat.includes(lower) || desc.includes(lower);
              });

              if (related.length > 0) {
                 let html = `Não encontrei esse nome exato, mas achei <b>${related.length}</b> oferta(s) parecidas que podem te interessar: <br><br>`;
                 related.slice(0, 3).forEach(p => {
                    html += `
                         <div style="display:flex; align-items:center; gap: 10px; margin-top:5px; padding: 5px; border-radius: 5px; background: rgba(0,0,0,0.05);">
                            <img src="${p.image}" style="width:40px; height:40px; border-radius:5px; object-fit:cover;">
                            <div style="flex:1;">
                               <strong style="display:block; font-size:0.85rem;">${p.name}</strong>
                            </div>
                            <a href="detalhes.html?id=${p.id}" class="btn btn-primary" style="padding: 5px 10px; font-size: 0.75rem;">Ver</a>
                         </div>`;
                 });
                 if(related.length > 3) html += `<div style="text-align:center; margin-top:10px; font-size:0.8rem;"><a href="produtos.html?q=${encodeURIComponent(userText)}" style="color:var(--clr-secondary); font-weight:bold;">Ver todos os ${related.length} resultados</a></div>`;
                 return html;
              }
          }
      }

      // Fallback genéricos
      if (lower.includes('comprar') || lower.includes('pagamento') || lower.includes('frete')) {
          return "Pode colocar tudo no carrinho e simular a compra lá! Como sou uma loja virtual incrível mas demonstrativa, o frete aqui é super de brincadeirinha! 🛒🎉";
      } else if (lower.includes('caneta') || lower.includes('lápis') || lower.includes('lapis')) {
          return "Nossas canetas são super coloridas e temos kits perfeitos pra deixar seus resumos lindos. 🖊️✨ Dá uma olhada lá nos Filtros!";
      } else if (lower.includes('oi') || lower.includes('olá') || lower.includes('ola') || lower.includes('bom dia')) {
          return "Oii! Tudo bem com você? Pode me perguntar o nome de um produto ou o que você deseja, e eu procuro pra você agora mesmo! 🥰";
      } else {
          return "Hmm... Interessante! 🤔 Que tal tentar digitar o nome de um produto diferente ou uma palavra-chave para eu procurar no catálogo?";
      }
  };

  const handleSend = async () => {
      const text = chatInput.value.trim();
      if (!text) return;
      
      addMessage(text, 'user');
      chatInput.value = '';
      
      
      const typingIndicator = document.createElement('div');
      typingIndicator.className = 'chat-msg ai';
      typingIndicator.innerHTML = '<span style="opacity:0.5;">A bolsinha está digitando... 💬</span>';
      chatBody.appendChild(typingIndicator);
      chatBody.scrollTop = chatBody.scrollHeight;

      const aiResponse = await getAIResponse(text);

      setTimeout(() => {
          chatBody.removeChild(typingIndicator);
          addMessage(aiResponse, 'ai');
      }, 500 + Math.random() * 1000);
  };

  chatSend.onclick = handleSend;
  chatInput.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') handleSend();
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
  if (!window.isLoggedIn) {
      window.showToast('Faça login para adicionar produtos ao carrinho!', 'error');
      setTimeout(() => {
          window.location.href = 'login.html';
      }, 1500);
      return;
  }

  // Se não tem cor, verifica se a categoria exige cor
  if (!color) {
      const product = await window.ProductManager.getById(productId);
      if (product) {
          const cat = (product.category || '').toLowerCase();
          if (window.COLOR_CATEGORIES.includes(cat)) {
              // Redireciona para detalhes para escolher a cor
              window.location.href = `detalhes.html?id=${productId}`;
              return;
          }
      }
  }

  window.CartManager.add(productId, quantity, color);
  window.showToast('Produto adicionado ao carrinho!');
};

window.handleBuyNow = async (productId, quantity = 1, color = null) => {
  if (!window.isLoggedIn) {
      window.showToast('Faça login para comprar!', 'error');
      setTimeout(() => {
          window.location.href = 'login.html';
      }, 1500);
      return;
  }

  // Se não tem cor, verifica se a categoria exige cor
  if (!color) {
      const product = await window.ProductManager.getById(productId);
      if (product) {
          const cat = (product.category || '').toLowerCase();
          if (window.COLOR_CATEGORIES.includes(cat)) {
              window.location.href = `detalhes.html?id=${productId}`;
              return;
          }
      }
  }

  window.CartManager.add(productId, quantity, color);
  window.location.href = 'carrinho.html';
};

window.handleWhatsApp = () => {
    const num = window.ConfigManager.get('whatsappNumber') || '5599999999999'; // Fallback
    const msg = encodeURIComponent("Olá! Gostaria de tirar uma dúvida.");
    window.open(`https://wa.me/${num}?text=${msg}`, '_blank');
};
