document.addEventListener('DOMContentLoaded', async () => {
    // 1. Auth Protection & Empty Cart Check
    // Como a checagem global do app.js pode não ter finalizado ainda, checamos assincronamente.
    try {
        const response = await fetch('api/auth.php?action=check');
        const data = await response.json();
        if (!data.loggedIn) {
            window.location.href = 'login.html';
            return;
        }
        // Ensure local variables are set if global ones aren't ready
        window.userId = data.id;
        window.userName = data.name;
    } catch(err) {
        window.location.href = 'login.html';
        return;
    }

    const cart = window.CartManager.getCart();
    if (cart.length === 0) {
        window.location.href = 'carrinho.html';
        return;
    }

    renderCheckoutSummary();
    setupCheckoutForm();

    const supportWaBtn = document.getElementById('support-wa-btn');
    if (supportWaBtn) {
        let waNum = window.ConfigManager.get('whatsappNumber') || '+5598985269184';
        waNum = waNum.replace(/\D/g, ''); 
        if (waNum && !waNum.startsWith('55') && waNum.length <= 11) {
            waNum = '55' + waNum;
        }
        supportWaBtn.href = `https://wa.me/${waNum}?text=Ol%C3%A1!%20Fiz%20um%20pedido%20no%20site%20e%20gostaria%20de%20tirar%20uma%20d%C3%BAvida%20sobre%20a%20retirada.`;
    }
});

async function renderCheckoutSummary() {
    const container = document.getElementById('checkout-summary');
    if (!container) return;

    const cartItems = window.CartManager.getCart();
    
    // Mostrar loader mini
    container.style.opacity = '0.6';
    const allProducts = await window.ProductManager.getAll();
    container.style.opacity = '1';

    let subtotal = 0;
    cartItems.forEach(item => {
        const product = allProducts.find(p => String(p.id) === String(item.productId));
        if (product) {
            subtotal += parseFloat(product.price) * item.quantity;
        }
    });

    const shipping = 0.00;
    const total = subtotal + shipping;

    container.innerHTML = `
        <h2 style="font-size: 1.5rem; margin-bottom: 1.5rem;">Retirada na loja</h2>
        
        <div class="summary-row">
            <span>Subtotal (${window.CartManager.getTotalItems()} itens)</span>
            <span style="color: var(--clr-text); font-weight: 500;">${window.formatCurrency(subtotal)}</span>
        </div>
        <div class="summary-row" id="shipping-row">
            <span>Retirada em Loja</span>
            <span style="color: #10B981; font-weight: 500; font-size:0.875rem;" id="shipping-val">Grátis</span>
        </div>

        <div class="summary-total">
            <span>Total a Pagar</span>
            <span style="color: var(--clr-accent);" id="total-val">${window.formatCurrency(total)}</span>
        </div>

        <button type="submit" form="payment-form" id="confirm-btn" class="btn btn-primary" style="width: 100%; margin-top: 2rem; padding: 1rem;">
            Confirmar Compra <i class='bx bxl-whatsapp' ></i>
        </button>
        
        <p style="text-align: center; margin-top: 1rem; color: var(--clr-text-light); font-size: 0.75rem;">
            Seu pedido será enviado via WhatsApp
        </p>
    `;
}


function setupCheckoutForm() {
    const form = document.getElementById('payment-form');
    if (!form) return;
    
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const btn = document.getElementById('confirm-btn');
        if (btn) {
            btn.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> Processando...";
            btn.style.opacity = '0.7';
            btn.disabled = true;
        }

        const cartItems = window.CartManager.getCart();
        const allProducts = await window.ProductManager.getAll();

        let subtotal = 0;
        let fullItemsData = [];
        
        cartItems.forEach(item => {
            const product = allProducts.find(p => String(p.id) === String(item.productId));
            if(product) {
                const price = parseFloat(product.price);
                subtotal += price * item.quantity;
                fullItemsData.push({
                    productId: product.id,
                    name: product.name,
                    price: price,
                    quantity: item.quantity
                });
            }
        });

        // Recupera o número do WhatsApp da configuração (Removendo não numéricos)
        let whatsappNumber = window.ConfigManager.get('whatsappNumber') || '+5598985269184';
        whatsappNumber = whatsappNumber.replace(/\D/g, ''); 
        if (whatsappNumber && !whatsappNumber.startsWith('55') && whatsappNumber.length <= 11) {
            whatsappNumber = '55' + whatsappNumber;
        }

        // Formatar mensagem para o WhatsApp
        let message = `*Novo Pedido - Infinity Variedades*\n\n`;
        message += `*Cliente:* ${window.userName || 'Visitante'}\n\n`;
        message += `*Produtos:*\n`;
        
        fullItemsData.forEach(item => {
            message += `- ${item.quantity}x ${item.name} (${window.formatCurrency(item.price)})\n`;
        });
        
        message += `\n*Total da Compra:* ${window.formatCurrency(subtotal)}`;
        const encodedMessage = encodeURIComponent(message);
        
        // Registra o pedido no Banco de Dados
        await window.OrderManager.add({
            user_id: window.userId,
            user_name: window.userName || "Cliente Padrão",
            items: fullItemsData,
            total: subtotal,
            method: 'WhatsApp'
        });

        window.CartManager.clear();
        
        const container = document.getElementById('checkout-container');
        if (container) {
            container.innerHTML = `
                <div style="padding: 2rem; background-color: var(--clr-surface); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); max-width: 700px; margin: 0 auto; text-align: center;">
                    <i class='bx bxs-check-circle' style="font-size: 5rem; color: #10B981; margin-bottom: 0.5rem;" id="status-icon"></i>
                    <h2 style="font-size: 1.8rem; margin-bottom: 0.5rem;" id="status-title">Pedido Registrado!</h2>
                    <p style="color: var(--clr-text-light); margin-bottom: 2rem;" id="status-desc">Você será redirecionado para o WhatsApp para enviar o pedido à loja.</p>
                    
                    <div style="margin-top: 1.5rem;">
                        <a href="https://wa.me/${whatsappNumber}?text=${encodedMessage}" target="_blank" class="btn btn-primary" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                            <i class='bx bxl-whatsapp' style="font-size: 1.25rem;"></i> Enviar Pedido no WhatsApp
                        </a>
                    </div>
                    
                    <div style="margin-top: 1rem;" id="back-btn-box">
                        <a href="produtos.html" class="btn" style="border: 1px solid var(--clr-border); background: transparent; color: var(--clr-text); width: 100%;">Voltar para Loja de Produtos</a>
                    </div>
                </div>
            `;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Redirecionamento automático após 1.5s
        setTimeout(() => {
            window.open(`https://wa.me/${whatsappNumber}?text=${encodedMessage}`, '_blank');
        }, 1500);
    });
}
