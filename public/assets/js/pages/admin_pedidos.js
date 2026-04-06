document.addEventListener('DOMContentLoaded', async () => {
    try {
        const response = await fetch('api/auth.php?action=check', { credentials: 'include' });
        const data = await response.json();
        if (!data.loggedIn || data.role !== 'admin') {
            window.location.href = 'index.php';
            return;
        }
    } catch(err) {
        window.location.href = 'index.php';
        return;
    }
    renderOrdersTable();
});

/* ===== Confirm delivery ===== */
window.markAsDelivered = async (orderId) => {
    const row  = document.querySelector(`tr[data-order-id="${orderId}"]`);
    const card = document.querySelector(`.order-card[data-order-id="${orderId}"]`);

    try {
        await fetch(`api/orders.php?action=update_status&id=${orderId}&status=entregue`, { credentials: 'include' });
        window.showToast && window.showToast("Pedido finalizado com sucesso!", "success");
        renderOrdersTable();
    } catch(e) {
        window.showToast && window.showToast("Erro ao atualizar pedido.", "error");
    }
};

/* ===== Delete order ===== */
window.deleteOrder = async (orderId) => {
    if (!confirm('Deseja realmente excluir este pedido?')) return;

    const row  = document.querySelector(`tr[data-order-id="${orderId}"]`);
    const card = document.querySelector(`.order-card[data-order-id="${orderId}"]`);

    // Visual feedback
    [row, card].forEach(el => { if (el) { el.style.opacity = '0.4'; el.style.pointerEvents = 'none'; } });

    try {
        const res  = await fetch(`api/orders.php?action=delete&id=${orderId}`, { credentials: 'include' });
        const data = await res.json();
        if (data.status === 'success') {
            [row, card].forEach(el => { if (el) el.remove(); });
            window.showToast && window.showToast("Pedido excluído!", "success");
            renderOrdersTable();
        } else {
            [row, card].forEach(el => { if (el) { el.style.opacity = '1'; el.style.pointerEvents = ''; } });
            window.showToast && window.showToast("Erro: " + (data.message || "Não foi possível excluir."), "error");
        }
    } catch(e) {
        [row, card].forEach(el => { if (el) { el.style.opacity = '1'; el.style.pointerEvents = ''; } });
        window.showToast && window.showToast("Erro de conexão ao excluir.", "error");
    }
};

/* ===== Render ===== */
window.renderOrdersTable = async function() {
    const tbody     = document.getElementById('table-orders-body');
    const cardsList = document.getElementById('order-cards-list');

    const loadingHTML = `<tr><td colspan="6" style="text-align:center;padding:4rem;">
        <i class="bx bx-loader-alt bx-spin" style="font-size:2rem;color:var(--clr-primary);"></i>
        <p style="margin-top:1rem;color:var(--clr-text-light);">Carregando pedidos...</p>
    </td></tr>`;

    if (tbody)     tbody.innerHTML     = loadingHTML;
    if (cardsList) cardsList.innerHTML = '<p style="text-align:center;padding:3rem;color:var(--clr-text-light);"><i class="bx bx-loader-alt bx-spin" style="font-size:2rem;"></i></p>';

    try {
        const allOrders    = await fetch('api/orders.php?action=list', { credentials: 'include' }).then(r => r.json()).catch(() => []);
        const pickupOrders = allOrders.filter(o => {
            const m = String(o.method || "").toLowerCase();
            return m.includes("retira") || m.includes("whatsapp") || m === "";
        });

        updateOrderStats(pickupOrders);

        /* Apply filter */
        const filter       = window.getCurrentFilter ? window.getCurrentFilter() : 'all';
        const visibleOrders = filter === 'all'
            ? pickupOrders
            : pickupOrders.filter(o => (filter === 'entregue' ? o.status === 'entregue' : o.status !== 'entregue'));

        /* Empty state */
        if (visibleOrders.length === 0) {
            const emptyMsg = `<i class='bx bx-ghost' style='font-size:3rem;margin-bottom:1rem;display:block;'></i>Nenhum pedido encontrado.`;
            if (tbody)     tbody.innerHTML     = `<tr><td colspan="6" style="text-align:center;color:var(--clr-text-light);padding:4rem;">${emptyMsg}</td></tr>`;
            if (cardsList) cardsList.innerHTML = `<p style="text-align:center;color:var(--clr-text-light);padding:3rem;">${emptyMsg}</p>`;
            return;
        }

        /* --- TABLE rows (Desktop) --- */
        if (tbody) {
            tbody.innerHTML = '';
            visibleOrders.forEach((order, index) => {
                const tr = document.createElement('tr');
                tr.setAttribute('data-order-id', order.id);
                tr.style.animation = `fadeInUp 0.4s ease forwards ${index * 0.05}s`;
                tr.style.opacity   = '0';

                const { formattedDate, itemsList, isDelivered } = parseOrder(order);

                tr.innerHTML = `
                    <td data-label="ID do Pedido"><span class="order-id">${order.external_id || ('#' + order.id)}</span></td>
                    <td data-label="Cliente">
                        <div style="font-weight:600;color:var(--clr-text);">${order.user_name || 'Visitante'}</div>
                        <div style="font-size:0.75rem;color:var(--clr-text-light);">ID: ${order.user_id || 'N/A'}</div>
                    </td>
                    <td data-label="Data e Status">
                        <div style="font-size:0.85rem;color:var(--clr-text);margin-bottom:0.4rem;">${formattedDate}</div>
                        <span class="status-badge ${isDelivered ? 'status-entregue' : 'status-pendente'}">${isDelivered ? 'Entregue' : 'Pendente'}</span>
                    </td>
                    <td data-label="Total"><span style="font-weight:700;color:var(--clr-text);font-size:1.1rem;">${window.formatCurrency(order.total)}</span></td>
                    <td data-label="Itens" style="font-size:0.8rem;color:var(--clr-text-light);border-left:1px dashed var(--clr-border);padding-left:1.5rem;">${itemsList}</td>
                    <td data-label="Ações">
                        <div style="display:flex;justify-content:flex-end;gap:0.5rem;" class="status-cell">
                            ${!isDelivered ? `
                                <button onclick="window.markAsDelivered('${order.id}')" class="btn btn-primary" style="padding:0.5rem 1rem;border-radius:12px;font-size:0.75rem;">
                                    <i class='bx bx-check-circle'></i> Confirmar
                                </button>
                            ` : ''}
                            <button onclick="window.deleteOrder('${order.id}')" class="circle-btn delete" style="background:rgba(239,68,68,0.1);color:#EF4444;border:1px solid rgba(239,68,68,0.2);width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;" title="Excluir">
                                <i class='bx bx-trash'></i>
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        /* --- ORDER CARDS (Mobile) --- */
        if (cardsList) {
            cardsList.innerHTML = '';
            visibleOrders.forEach((order, index) => {
                const { formattedDate, itemsList, isDelivered } = parseOrder(order);

                const card = document.createElement('div');
                card.className = 'order-card';
                card.setAttribute('data-order-id', order.id);
                card.style.animationDelay = `${index * 0.05}s`;

                card.innerHTML = `
                    <div class="order-card-header" onclick="toggleOrderCard(this.parentElement)">
                        <span class="order-card-status-dot ${isDelivered ? 'dot-delivered' : 'dot-pending'}"></span>
                        <div class="order-card-main">
                            <div class="order-card-id">${order.external_id || ('#' + order.id)}</div>
                            <div class="order-card-name">${order.user_name || 'Visitante'}</div>
                        </div>
                        <span class="order-card-price">${window.formatCurrency(order.total)}</span>
                        <i class='bx bx-chevron-down order-card-chevron'></i>
                    </div>

                    <div class="order-card-body">
                        <div class="order-card-row" style="padding-top:0.75rem;">
                            <span class="order-card-label">Status</span>
                            <div class="order-card-value">
                                <span class="status-badge ${isDelivered ? 'status-entregue' : 'status-pendente'}">${isDelivered ? 'Entregue' : 'Pendente'}</span>
                            </div>
                        </div>
                        <div class="order-card-row">
                            <span class="order-card-label">Data</span>
                            <div class="order-card-value" style="font-size:0.85rem;">${formattedDate}</div>
                        </div>
                        <div class="order-card-row">
                            <span class="order-card-label">Itens</span>
                            <div class="order-card-value">
                                <div class="order-card-items">${itemsList || 'Sem itens registrados'}</div>
                            </div>
                        </div>
                        <div class="order-card-actions">
                            ${!isDelivered ? `
                                <button class="btn btn-confirm" onclick="window.markAsDelivered('${order.id}')">
                                    <i class='bx bx-check-circle'></i> Confirmar Retirada
                                </button>
                            ` : ''}
                            <button class="btn btn-delete" onclick="window.deleteOrder('${order.id}')">
                                <i class='bx bx-trash'></i> Excluir
                            </button>
                        </div>
                    </div>
                `;
                cardsList.appendChild(card);
            });
        }

    } catch(e) {
        console.error(e);
        const errHTML = `<tr><td colspan="6" style="text-align:center;padding:4rem;color:#EF4444;"><i class='bx bx-error' style='font-size:3rem;'></i><p>Erro ao carregar pedidos.</p></td></tr>`;
        if (tbody)     tbody.innerHTML     = errHTML;
        if (cardsList) cardsList.innerHTML = `<p style="text-align:center;padding:3rem;color:#EF4444;">Erro ao carregar pedidos.</p>`;
    }
};

/* ===== Helpers ===== */
function parseOrder(order) {
    const dateObj       = new Date(order.created_at);
    const formattedDate = dateObj.toLocaleDateString('pt-BR') + ' às ' + dateObj.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    const isDelivered   = order.status === 'entregue';

    let itemsList = '';
    try {
        const items = typeof order.items_json === 'string' ? JSON.parse(order.items_json) : (order.items_json || []);
        itemsList   = items.map(i => `• ${i.quantity}x ${i.name}`).join('<br>');
    } catch(e) { itemsList = 'Erro ao carregar itens'; }

    return { formattedDate, itemsList, isDelivered };
}

function updateOrderStats(orders) {
    const totalEl     = document.getElementById('stat-orders-total');
    const pendingEl   = document.getElementById('stat-orders-pending');
    const completedEl = document.getElementById('stat-orders-completed');
    const salesEl     = document.getElementById('stat-total-sales');

    if (totalEl)     totalEl.innerText     = orders.length;
    if (pendingEl)   pendingEl.innerText   = orders.filter(o => o.status !== 'entregue').length;
    if (completedEl) completedEl.innerText = orders.filter(o => o.status === 'entregue').length;

    if (salesEl) {
        const totalSales = orders
            .filter(o => o.status === 'entregue' || o.status === 'concluido')
            .reduce((sum, o) => sum + parseFloat(o.total || 0), 0);
        salesEl.innerText = window.formatCurrency(totalSales);
    }
}
