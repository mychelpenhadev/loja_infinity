

document.addEventListener('DOMContentLoaded', () => {
  initTheme();
  updateCartBadge();
  injectChatbot();
  checkAuth();
  
  
  window.addEventListener('cartUpdated', updateCartBadge);
  
  
  const themeToggle = document.getElementById('theme-toggle');
  if (themeToggle) {
    themeToggle.addEventListener('click', () => {
      const currentTheme = document.documentElement.getAttribute('data-theme');
      const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
      document.documentElement.setAttribute('data-theme', newTheme);
      localStorage.setItem(window.STORAGE_KEYS.THEME, newTheme);
      updateThemeIcon(newTheme);
    });
  }
});

function initTheme() {
  const savedTheme = localStorage.getItem(window.STORAGE_KEYS?.THEME) || 'light';
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
  if (badge) {
    const totalItems = window.CartManager.getTotalItems();
    badge.textContent = totalItems > 99 ? '99+' : totalItems;
    badge.style.display = totalItems > 0 ? 'flex' : 'none';
  }
}


window.isLoggedIn = false;
window.userId = null;
window.userName = null;

async function checkAuth() {
  try {
      const response = await fetch('api/auth.php?action=check');
      const data = await response.json();
      
      window.isLoggedIn = data.loggedIn;
      window.userId = data.id || null;
      window.userName = data.name || null;
      window.dispatchEvent(new Event('cartUpdated'));
      
      const userBtns = document.querySelectorAll('a[href="login.html"]');
      if (data.loggedIn) {
          userBtns.forEach(btn => {
              btn.innerHTML = `<i class='bx bxs-user-circle' title="Minha Conta" style="font-size: 1.5rem; color: var(--clr-accent);"></i>`;
              btn.href = "#";
              btn.onclick = (e) => {
                  e.preventDefault();
                  openProfileModal();
              };
          });
          
          
          if(data.role !== 'admin') {
              document.querySelectorAll('a[href="admin.html"]').forEach(el => el.style.display = 'none');
          }
      } else {
          
          document.querySelectorAll('a[href="admin.html"]').forEach(el => el.style.display = 'none');
      }
  } catch (err) {
      console.error("Erro ao verificar auth:", err);
  }
}


function injectChatbot() {
  if (document.querySelector('.chatbot-fab')) return; // do not duplicate
  
  
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

window.openProfileModal = () => {
    let modal = document.getElementById('profile-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'profile-modal';
        modal.style.cssText = `
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(0, 0, 0, 0.5); backdrop-filter: blur(4px);
            display: flex; align-items: center; justify-content: center;
            z-index: 1000; opacity: 0; pointer-events: none;
            transition: opacity 0.3s ease;
        `;
        
        modal.innerHTML = `
            <div style="background-color: var(--clr-surface); padding: 2.5rem; border-radius: var(--radius-lg); width: 90%; max-width: 400px; box-shadow: var(--shadow-lg); transform: translateY(20px); transition: transform 0.3s ease;" id="profile-content-box">
                <h2 style="margin-bottom: 1.5rem; text-align: center; color: var(--clr-text);">Minha Conta</h2>
                <form id="profile-form">
                    <div style="margin-bottom: 1rem;">
                        <label style="display:block; margin-bottom: 0.5rem; font-weight: 500; font-size: 0.875rem;">Nome Completo</label>
                        <input type="text" id="prof-nome" value="${window.userName}" required style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--clr-border); border-radius: var(--radius-md); background-color: var(--clr-bg); color: var(--clr-text); font-family: var(--font-body);">
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display:block; margin-bottom: 0.5rem; font-weight: 500; font-size: 0.875rem;">Nova Senha <small>(deixe em branco para manter)</small></label>
                        <input type="password" id="prof-senha" placeholder="****" style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--clr-border); border-radius: var(--radius-md); background-color: var(--clr-bg); color: var(--clr-text); font-family: var(--font-body);">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; margin-bottom: 1rem;">Salvar Alterações</button>
                    
                    <button type="button" id="logout-btn" class="btn" style="width: 100%; justify-content: center; background-color: #EF4444; color: white; margin-bottom: 0.5rem;">
                        <i class='bx bx-log-out'></i> Sair da Conta
                    </button>
                    
                    <button type="button" class="btn" style="width: 100%; justify-content: center; background: transparent; border: 1px solid var(--clr-border); color: var(--clr-text);" onclick="closeProfileModal()">Cancelar</button>
                </form>
            </div>
        `;
        document.body.appendChild(modal);

        document.getElementById('logout-btn').addEventListener('click', async () => {
            if(confirm('Tem certeza que deseja sair?')) {
                await fetch('api/auth.php?action=logout');
                window.location.reload();
            }
        });

        document.getElementById('profile-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const passVal = document.getElementById('prof-senha').value;
            if(passVal) {
                const passRegex = /^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
                if(!passRegex.test(passVal)) {
                    window.showToast("A senha nova deve ter no mínimo 8 caracteres, 1 maiúscula, 1 número e 1 especial.", "error");
                    return;
                }
            }
            
            const btn = e.target.querySelector('button[type="submit"]');
            const originalText = btn.innerText;
            btn.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> Salvando...";
            btn.disabled = true;

            const fd = new FormData();
            fd.append('name', document.getElementById('prof-nome').value);
            fd.append('password', document.getElementById('prof-senha').value);

            try {
                const res = await fetch('api/auth.php?action=update_profile', { method: 'POST', body: fd });
                const json = await res.json();
                if (json.status === 'success') {
                    window.showToast(json.message, 'success');
                    window.userName = document.getElementById('prof-nome').value;
                    closeProfileModal();
                } else {
                    window.showToast(json.message, 'error');
                }
            } catch(err) {
                window.showToast("Erro de rede ao salvar perfil.", 'error');
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });
    }

    // Trigger animation
    setTimeout(() => {
        modal.style.opacity = '1';
        modal.style.pointerEvents = 'all';
        document.getElementById('profile-content-box').style.transform = 'translateY(0)';
    }, 10);
};

window.closeProfileModal = () => {
    const modal = document.getElementById('profile-modal');
    if (modal) {
        modal.style.opacity = '0';
        modal.style.pointerEvents = 'none';
        document.getElementById('profile-content-box').style.transform = 'translateY(20px)';
        setTimeout(() => modal.remove(), 300);
    }
};
  
  const chatInput = document.getElementById('chat-input');
  const chatSend = document.getElementById('chat-send-btn');
  const chatBody = document.getElementById('chat-body');
  
  const addMessage = (text, type) => {
      const msg = document.createElement('div');
      msg.className = `chat-msg ${type}`;
      msg.innerHTML = text;
      chatBody.appendChild(msg);
      chatBody.scrollTop = chatBody.scrollHeight;
  };

  const getAIResponse = (userText) => {
      const lower = userText.toLowerCase();

      // Busca na Base de Dados
      if (window.ProductManager) {
          const products = window.ProductManager.getAll();
          
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
              const related = products.filter(p => p.name.toLowerCase().includes(lower) || p.category.toLowerCase().includes(lower) || p.description.toLowerCase().includes(lower));
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

  const handleSend = () => {
      const text = chatInput.value.trim();
      if (!text) return;
      
      addMessage(text, 'user');
      chatInput.value = '';
      
      
      const typingIndicator = document.createElement('div');
      typingIndicator.className = 'chat-msg ai';
      typingIndicator.innerHTML = '<span style="opacity:0.5;">A bolsinha está digitando... 💬</span>';
      chatBody.appendChild(typingIndicator);
      chatBody.scrollTop = chatBody.scrollHeight;

      setTimeout(() => {
          chatBody.removeChild(typingIndicator);
          addMessage(getAIResponse(text), 'ai');
      }, 1000 + Math.random() * 1500);
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


window.handleAddToCart = (productId) => {
  if (!window.isLoggedIn) {
      window.showToast('Faça login para adicionar produtos ao carrinho!', 'error');
      setTimeout(() => {
          window.location.href = 'login.html';
      }, 1500);
      return;
  }
  window.CartManager.add(productId, 1);
  window.showToast('Produto adicionado ao carrinho!');
};
