document.addEventListener('DOMContentLoaded', async () => {

    const checkAuthStatus = () => {
        if (!window.authChecked) {
            setTimeout(checkAuthStatus, 50);
            return;
        }
        if (!window.isLoggedIn) {
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
    };
    checkAuthStatus();
    const supportWaBtn = document.getElementById('support-wa-btn');
    if (supportWaBtn) {
        let waNum = (window.ConfigManager && window.ConfigManager.get('whatsappNumber')) || '+5598985269184';
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

    container.style.opacity = '0.6';
    const productIds = [...new Set(cartItems.map(item => item.productId))];
    const allProducts = await window.ProductManager.getBatch(productIds);
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
        <div class="receipt-header">
            <span class="receipt-title">Resumo do Pedido</span>
        </div>
        
        <div class="summary-details">
            <div class="summary-item">
                <span>Subtotal (${window.CartManager.getTotalItems()} itens)</span>
                <span style="color: #fff; font-weight: 700;">${window.formatCurrency(subtotal)}</span>
            </div>
            <div class="summary-item">
                <span>Taxa de Entrega</span>
                <span style="color: #10B981; font-weight: 700;">Grátis</span>
            </div>
        </div>

        <div class="summary-total-box">
            <div class="total-label">Subtotal Geral</div>
            <div class="total-value">${window.formatCurrency(total)}</div>
        </div>

        <button type="submit" form="payment-form" id="confirm-btn" class="btn btn-primary" style="width: 100%; margin-top: 3rem; padding: 1.25rem; border-radius: 20px; font-weight: 900; background: var(--grad-primary); color: #003838; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.75rem; box-shadow: 0 15px 30px rgba(0, 212, 212, 0.3); font-size: 1.1rem; transition: all 0.3s;">
            CONFIRMAR PEDIDO <i class='bx bxl-whatsapp' style="font-size:1.6rem;"></i>
        </button>
    `;
}

function setupCheckoutForm() {
    const form = document.getElementById('payment-form');
    if (!form) return;
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('confirm-btn');
        if (btn) {
            btn.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> Criando Pedido...";
            btn.style.opacity = '0.7';
            btn.disabled = true;
        }
        
        try {
            const cartItems = window.CartManager.getCart();
            const productIds = [...new Set(cartItems.map(item => item.productId))];
            const allProducts = await window.ProductManager.getBatch(productIds);
            
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
                        quantity: item.quantity,
                        color: item.color,
                        image: product.image
                    });
                }
            });

            let num = (window.ConfigManager && window.ConfigManager.get('whatsappNumber')) || '5598985269184';
            num = num.replace(/\D/g, '');
            if (num && !num.startsWith('55') && num.length <= 11) num = '55' + num;

            let message = `*Novo Pedido - Infinity Variedades*\n\n`;
            message += `*Cliente:* ${window.userName || 'Visitante'}\n\n`;
            message += `*Produtos:*\n`;
            fullItemsData.forEach(item => {
                const colorStr = item.color ? ` (Cor: ${item.color})` : '';
                message += `- ${item.quantity}x ${item.name}${colorStr} (${window.formatCurrency(item.price)})\n`;
            });
            message += `\n*Total:* ${window.formatCurrency(subtotal)}`;
            const encoded = encodeURIComponent(message);

            await window.OrderManager.add({
                user_id: window.userId,
                user_name: window.userName || "Cliente Padrão",
                items: fullItemsData,
                total: subtotal,
                method: 'Retirada'
            });

            window.CartManager.clear();
            const container = document.getElementById('checkout-container');
            if (container) {
                container.innerHTML = `
                    <div style="min-height: 60vh; display: flex; align-items: center; justify-content: center;">
                        <div class="glass-panel" style="max-width: 600px; text-align: center; padding: 4rem;">
                            <div style="width: 100px; height: 100px; background: rgba(16, 185, 129, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 2.5rem; border: 2px solid #10B981;">
                                <i class='bx bx-check-double' style="font-size: 4rem; color: #10B981;"></i>
                            </div>
                            <h2 style="font-size: 2.25rem; font-weight: 800; margin-bottom: 1rem; color: #fff;">Pedido Confirmado!</h2>
                            <p style="color: var(--clr-text-light); margin-bottom: 3rem; line-height: 1.6;">Obrigado por escolher a Infinity! Agora você só precisa enviar a lista pelo WhatsApp para finalizar a separação.</p>
                            
                            <a href="https://wa.me/${num}?text=${encoded}" target="_blank" class="btn btn-primary" style="width: 100%; padding: 1.5rem; border-radius: 20px; font-weight: 800; background: #25D366; color: #fff; display: flex; align-items: center; justify-content: center; gap: 1rem; font-size: 1.25rem; box-shadow: 0 10px 25px rgba(37, 211, 102, 0.3);">
                                <i class='bx bxl-whatsapp' style="font-size: 2rem;"></i> Abrir no WhatsApp
                            </a>
                            
                            <a href="/" style="display: block; margin-top: 2rem; color: var(--clr-text-light); text-decoration: none; font-size: 0.9rem; font-weight: 700;">
                                <i class='bx bx-left-arrow-alt'></i> Voltar para a Loja
                            </a>
                        </div>
                    </div>
                `;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            setTimeout(() => {
                window.open(`https://wa.me/${num}?text=${encoded}`, '_blank');
            }, 1000);

        } catch (err) {
            console.error(err);
            const btn = document.getElementById('confirm-btn');
            if (btn) {
                btn.innerHTML = "CONFIRMAR PEDIDO <i class='bx bxl-whatsapp'></i>";
                btn.style.opacity = '1';
                btn.disabled = false;
            }
            window.showToast("Erro ao processar. Tente novamente.", "error");
        }
    });
}
