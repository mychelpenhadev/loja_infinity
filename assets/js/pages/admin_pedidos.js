document.addEventListener('DOMContentLoaded', async () => {
    try {
        const response = await fetch('api/auth.php?action=check', { credentials: 'include' });
        const data = await response.json();
        if (!data.loggedIn || data.role !== 'admin') {
            window.location.href = 'index.html';
            return;
        }
    } catch(err) {
        window.location.href = 'index.html';
        return;
    }
    renderOrdersTable();
});

window.markAsDelivered = async (orderId) => {
    const row = document.querySelector(`tr[data-order-id="${orderId}"]`);
    try {
        await fetch(`api/orders.php?action=update_status&id=${orderId}&status=entregue`, { credentials: 'include' });
        // Atualização otimista do status na linha
        if (row) {
            const statusCell = row.querySelector('.status-cell');
            if (statusCell) statusCell.innerHTML = `<span style="display:inline-block;padding:0.25rem 0.65rem;border-radius:1rem;background:rgba(16,185,129,0.1);color:#10B981;font-size:0.75rem;font-weight:600;">Entregue</span><br><button onclick="window.deleteOrder('${orderId}')" class="btn" style="padding:0.25rem 0.5rem;font-size:0.75rem;background:transparent;border:1px solid #EF4444;color:#EF4444;margin-top:0.35rem;">Excluir</button>`;
        }
        window.showToast && window.showToast("Pedido finalizado com sucesso!", "success");
    } catch(e) {
        window.showToast && window.showToast("Erro ao atualizar pedido.", "error");
    }
};

window.deleteOrder = async (orderId) => {
    const row = document.querySelector(`tr[data-order-id="${orderId}"]`);
    if (!row) return;

    // Remove otimisticamente do DOM imediatamente
    row.style.transition = 'opacity 0.2s';
    row.style.opacity = '0.4';
    row.style.pointerEvents = 'none';

    try {
        const res = await fetch(`api/orders.php?action=delete&id=${orderId}`, { credentials: 'include' });
        const data = await res.json();
        if (data.status === 'success') {
            row.remove();
            window.showToast && window.showToast("Pedido excluído!", "success");
            // Checar se ficou vazio
            const tbody = document.getElementById('table-orders-body');
            if (tbody && tbody.children.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;color:var(--clr-text-light);padding:3rem;">Nenhum pedido para retirada encontrado.</td></tr>`;
            }
        } else {
            row.style.opacity = '1';
            row.style.pointerEvents = '';
            window.showToast && window.showToast("Erro: " + (data.message || "Não foi possível excluir."), "error");
        }
    } catch(e) {
        row.style.opacity = '1';
        row.style.pointerEvents = '';
        window.showToast && window.showToast("Erro de conexão ao excluir.", "error");
    }
};

window.renderOrdersTable = async function() {
    const tbody = document.getElementById('table-orders-body');
    if (!tbody) return;

    tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;padding:3rem;"><i class="bx bx-loader-alt bx-spin" style="font-size:2rem;color:var(--clr-primary);"></i></td></tr>`;

    const allOrders = await fetch('api/orders.php?action=list', { credentials: 'include' }).then(r => r.json()).catch(() => []);
    const pickupOrders = allOrders.filter(o => o.method && (o.method.includes("Retirar") || o.method === "WhatsApp"));

    if (pickupOrders.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;color:var(--clr-text-light);padding:3rem;">Nenhum pedido para retirada encontrado.</td></tr>`;
        return;
    }

    tbody.innerHTML = pickupOrders.map(order => {
        const dateObj = new Date(order.created_at);
        const formattedDate = dateObj.toLocaleDateString('pt-BR') + ' às ' + dateObj.toLocaleTimeString('pt-BR', {hour: '2-digit', minute:'2-digit'});
        
        let itemsList = "";
        try {
            const items = typeof order.items_json === 'string' ? JSON.parse(order.items_json) : (order.items_json || []);
            itemsList = items.map(i => `${i.quantity}x ${i.name}`).join('<br>');
        } catch(e) { itemsList = "Erro ao carregar itens"; }
        
        let statusHtml = '';
        if (order.status === 'entregue') {
            statusHtml = `<span style="display:inline-block;padding:0.25rem 0.65rem;border-radius:1rem;background:rgba(16,185,129,0.1);color:#10B981;font-size:0.75rem;font-weight:600;margin-bottom:0.5rem;">Entregue</span><br>`;
        } else {
            statusHtml = `
                <span style="display:inline-block;padding:0.25rem 0.65rem;border-radius:1rem;background:rgba(245,158,11,0.1);color:#F59E0B;font-size:0.75rem;font-weight:600;margin-bottom:0.5rem;">Pendente</span><br>
                <button onclick="window.markAsDelivered('${order.id}')" class="btn btn-primary" style="padding:0.25rem 0.5rem;font-size:0.75rem;margin-bottom:0.25rem;">Confirmar Retirada</button><br>
            `;
        }
        statusHtml += `<button onclick="window.deleteOrder('${order.id}')" class="btn" style="padding:0.25rem 0.5rem;font-size:0.75rem;background:transparent;border:1px solid #EF4444;color:#EF4444;">Excluir</button>`;

        return `
            <tr data-order-id="${order.id}">
                <td style="font-weight:600;color:var(--clr-primary);">${order.external_id || ('#' + order.id)}</td>
                <td><strong>${order.user_name || 'Visitante'}</strong><br><small style="color:var(--clr-text-light);">ID: ${order.user_id || 'N/A'}</small></td>
                <td style="color:var(--clr-text-light);">${formattedDate}<br><div class="status-cell" style="margin-top:0.5rem;">${statusHtml}</div></td>
                <td style="font-weight:700;">${window.formatCurrency(order.total)}</td>
                <td style="font-size:0.85rem;color:var(--clr-text-light);">${itemsList}</td>
            </tr>
        `;
    }).join('');
}
