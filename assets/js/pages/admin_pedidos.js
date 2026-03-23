document.addEventListener('DOMContentLoaded', async () => {
    // Basic Auth Check
    try {
        const response = await fetch('api/auth.php?action=check');
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
    if(confirm("Tem certeza que este pedido foi retirado com sucesso e deseja marcá-lo como Entregue?")) {
        await window.OrderManager.updateStatus(orderId, 'entregue');
        renderOrdersTable();
        window.showToast("Pedido finalizado com sucesso!", "success");
    }
}

window.deleteOrder = async (orderId) => {
    if(confirm("Tem certeza que deseja excluir permanentemente este pedido?")) {
        await window.OrderManager.remove(orderId);
        renderOrdersTable();
        window.showToast("Pedido excluído com sucesso!", "success");
    }
}

async function renderOrdersTable() {
    const tbody = document.getElementById('table-orders-body');
    if (!tbody) return;

    // Mostrar loader
    tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; padding: 3rem;"><i class="bx bx-loader-alt bx-spin" style="font-size: 2rem; color: var(--clr-primary);"></i></td></tr>`;

    const allOrders = await window.OrderManager.getAll();
    
    // Filter only orders where method includes "Retirar" or is "WhatsApp"
    const pickupOrders = allOrders.filter(o => o.method && (o.method.includes("Retirar") || o.method === "WhatsApp"));

    if (pickupOrders.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: var(--clr-text-light); padding: 3rem;">Nenhum pedido para retirada encontrado.</td></tr>`;
        return;
    }

    tbody.innerHTML = pickupOrders.map(order => {
        // Date formatting
        const dateObj = new Date(order.date);
        const formattedDate = dateObj.toLocaleDateString('pt-BR') + ' às ' + dateObj.toLocaleTimeString('pt-BR', {hour: '2-digit', minute:'2-digit'});
        
        // Items details
        let itemsList = "";
        try {
            const items = typeof order.items === 'string' ? JSON.parse(order.items) : order.items;
            itemsList = items.map(i => `${i.quantity}x ${i.name}`).join('<br>');
        } catch(e) {
            itemsList = "Erro ao carregar itens";
        }
        
        let statusHtml = '';
        if (order.status === 'entregue') {
            statusHtml = `<span style="display:inline-block; padding:0.25rem 0.65rem; border-radius:1rem; background:rgba(16, 185, 129, 0.1); color:#10B981; font-size:0.75rem; font-weight:600; margin-bottom:0.5rem;">Entregue</span><br>`;
        } else {
            statusHtml = `
                <span style="display:inline-block; padding:0.25rem 0.65rem; border-radius:1rem; background:rgba(245, 158, 11, 0.1); color:#F59E0B; font-size:0.75rem; font-weight:600; margin-bottom:0.5rem;">Pendente</span><br>
                <button onclick="window.markAsDelivered('${order.id}')" class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; margin-bottom:0.25rem;">Confirmar Retirada</button><br>
            `;
        }
        
        statusHtml += `<button onclick="window.deleteOrder('${order.id}')" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; background: transparent; border: 1px solid #EF4444; color: #EF4444;">Excluir</button>`;

        return `
            <tr>
                <td style="font-weight: 600; color: var(--clr-primary);">#${order.id}</td>
                <td>
                    <strong>${order.userName}</strong><br>
                    <small style="color: var(--clr-text-light);">ID: ${order.userId}</small>
                </td>
                <td style="color: var(--clr-text-light);">
                    ${formattedDate}<br>
                    <div style="margin-top:0.5rem;">${statusHtml}</div>
                </td>
                <td style="font-weight: 700;">${window.formatCurrency(order.total)}</td>
                <td style="font-size: 0.85rem; color: var(--clr-text-light);">
                    ${itemsList}
                </td>
            </tr>
        `;
    }).join('');
}
