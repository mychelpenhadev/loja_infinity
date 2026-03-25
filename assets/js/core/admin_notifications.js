(function() {
    // Evita múltiplas instâncias
    if (window.adminNotificationStarted) return;
    window.adminNotificationStarted = true;

    let lastOrderId = localStorage.getItem('last_notified_order_id');

    async function checkNewOrders() {
        try {
            console.log("[Notificações] Verificando novos pedidos...");
            // Cache buster com timestamp para evitar dados antigos na Home
            const response = await fetch(`api/orders.php?action=list&limit=1&t=${Date.now()}`, { credentials: 'include' });
            const data = await response.json();
            
            if (data && data.length > 0) {
                const latestOrder = data[0];
                const latestId = parseInt(latestOrder.id);
                console.log("[Notificações] Último ID no banco:", latestId, "| Último notificado:", lastOrderId);

                // Na primeira vez, apenas registramos o ID atual sem notificar
                if (!lastOrderId) {
                    lastOrderId = latestId;
                    localStorage.setItem('last_notified_order_id', latestId);
                    return;
                }

                if (latestId > parseInt(lastOrderId)) {
                    console.log("[Notificações] NOVO PEDIDO DETECTADO!", latestId);
                    const userName = latestOrder.user_name || 'Alguém';
                    showFloatingNotification(userName, latestOrder.total);
                    
                    // Atualiza o ID para não notificar o mesmo pedido novamente
                    lastOrderId = latestId;
                    localStorage.setItem('last_notified_order_id', latestId);
                    
                    // Se estivermos na página de pedidos, atualizamos a tabela automaticamente
                    if (window.location.pathname.includes('admin_pedidos.php') && typeof window.renderOrdersTable === 'function') {
                        console.log("[Notificações] Atualizando tabela de pedidos...");
                        window.renderOrdersTable();
                    }
                }
            }
        } catch (e) {
            console.warn("Erro silencioso ao verificar pedidos:", e);
        }
    }

    function showFloatingNotification(name, total) {
        const value = parseFloat(total).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        
        // Cria o elemento de notificação
        const notif = document.createElement('div');
        notif.className = 'admin-order-alert';
        notif.innerHTML = `
            <div class="alert-icon"><i class='bx bxs-bell-ring bx-tada'></i></div>
            <div class="alert-content">
                <div class="alert-title">Novo Pedido Recebido! 🛍️</div>
                <div class="alert-text"><strong>${name}</strong> acabou de fazer um pedido de <strong>${value}</strong>.</div>
            </div>
            <button class="alert-close"><i class='bx bx-x'></i></button>
        `;

        // Estilos Inline Melhorados (Mais Visível)
        Object.assign(notif.style, {
            position: 'fixed',
            top: '30px',
            right: '30px',
            backgroundColor: '#ffffff',
            color: '#333',
            padding: '1.5rem',
            borderRadius: '16px',
            boxShadow: '0 20px 50px rgba(0,0,0,0.25)',
            display: 'flex',
            alignItems: 'center',
            gap: '1.2rem',
            zIndex: '10001',
            borderLeft: '8px solid #10B981',
            minWidth: '350px',
            maxWidth: '500px',
            animation: 'alertSlideIn 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards',
            fontFamily: "'Inter', sans-serif"
        });

        // Adiciona estilos globais para as animações
        if (!document.getElementById('admin-alert-styles')) {
            const style = document.createElement('style');
            style.id = 'admin-alert-styles';
            style.innerHTML = `
                @keyframes alertSlideIn { from { transform: translateX(120%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
                @keyframes alertSlideOut { from { transform: translateX(0); opacity: 1; } to { transform: translateX(120%); opacity: 0; } }
                .admin-order-alert .alert-icon { font-size: 1.5rem; color: #10B981; }
                .admin-order-alert .alert-title { font-weight: 700; font-size: 0.95rem; margin-bottom: 0.2rem; color: #10B981; }
                .admin-order-alert .alert-text { font-size: 0.85rem; line-height: 1.4; color: var(--clr-text-light); }
                .admin-order-alert .alert-close { background: none; border: none; color: #ccc; cursor: pointer; padding: 0.25rem; margin-left: auto; transition: 0.2s; }
                .admin-order-alert .alert-close:hover { color: #666; }
            `;
            document.head.appendChild(style);
        }

        document.body.appendChild(notif);

        // Som de notificação (opcional - alguns browsers bloqueiam autoplay)
        const audio = new Audio('https://assets.mixkit.co/sfx/preview/mixkit-software-interface-start-2574.mp3');
        audio.volume = 0.5;
        audio.play().catch(() => {});

        // Evento de fechar
        const closeBtn = notif.querySelector('.alert-close');
        closeBtn.onclick = () => {
            notif.style.animation = 'alertSlideOut 0.4s ease-in forwards';
            setTimeout(() => notif.remove(), 400);
        };

        // Auto-remove após 15 segundos (mais tempo para ver)
        setTimeout(() => {
            if (notif.parentNode) {
                notif.style.animation = 'alertSlideOut 0.4s ease-in forwards';
                setTimeout(() => notif.remove(), 400);
            }
        }, 15000);
    }

    // Pooling a cada 8 segundos para ser bem reativo
    setInterval(checkNewOrders, 8000);
    // Verificar imediatamente ao carregar
    setTimeout(checkNewOrders, 1000);
})();
