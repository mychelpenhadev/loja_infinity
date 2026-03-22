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
});

function renderCheckoutSummary() {
    const container = document.getElementById('checkout-summary');
    const cartItems = window.CartManager.getCart();
    
    let subtotal = 0;
    cartItems.forEach(item => {
        const product = window.ProductManager.getById(item.productId);
        if (product) {
            subtotal += product.price * item.quantity;
        }
    });

    const shipping = 0.00;
    const total = subtotal + shipping;

    container.innerHTML = `
        <h2 style="font-size: 1.5rem; margin-bottom: 1.5rem;">Resumo para Pagamento</h2>
        
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
            Confirmar Pagamento <i class='bx bx-check-shield' ></i>
        </button>
        
        <p style="text-align: center; margin-top: 1rem; color: var(--clr-text-light); font-size: 0.75rem;">
            <i class='bx bx-lock-alt'></i> Transação Criptografada (SSL de Ponta a Ponta)
        </p>
    `;
}



window.copyPix = () => {
    const copyText = document.getElementById("pix-key");
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(copyText.value);
    
    window.showToast("Chave Pix copiada com sucesso!");
};

function setupCheckoutForm() {
    const form = document.getElementById('payment-form');
    
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        
        const btn = document.getElementById('confirm-btn');
        btn.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> Autorizando Transação...";
        btn.style.opacity = '0.7';
        btn.disabled = true;

        const cartItems = window.CartManager.getCart();
        let subtotal = 0;
        let fullItemsData = [];
        
        cartItems.forEach(item => {
            const product = window.ProductManager.getById(item.productId);
            if(product) {
                subtotal += product.price * item.quantity;
                fullItemsData.push({
                    productId: product.id,
                    name: product.name,
                    price: product.price,
                    quantity: item.quantity
                });
            }
        });

        fetch('api/create_payment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ items: fullItemsData })
        })
        .then(res => res.json())
        .then(data => {
            if(data.qr_code_base64) {
                // Registra o pedido como pendente
                window.OrderManager.add({
                    userId: window.userId,
                    userName: window.userName || "Cliente Padrão",
                    items: fullItemsData,
                    total: subtotal,
                    method: 'Mercado Pago (Pix Nativo)'
                });

                window.CartManager.clear();
                
                const container = document.getElementById('checkout-container');
                container.innerHTML = `
                    <div style="padding: 2rem; background-color: var(--clr-surface); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); max-width: 700px; margin: 0 auto; text-align: center;">
                        <i class='bx bx-loader-alt bx-spin' style="font-size: 5rem; color: var(--clr-primary); margin-bottom: 0.5rem;" id="status-icon"></i>
                        <h2 style="font-size: 1.8rem; margin-bottom: 0.5rem;" id="status-title">Aguardando Pagamento</h2>
                        <p style="color: var(--clr-text-light); margin-bottom: 2rem;" id="status-desc">Leia o QR Code abaixo para finalizar seu pedido.</p>
                        
                        <div id="payment-box" style="background-color: var(--clr-bg); border: 1px solid var(--clr-primary); border-radius: var(--radius-md); padding: 2rem; text-align: center;">
                            <h3 style="margin-bottom: 1rem; color: var(--clr-primary);">
                                <i class='bx bx-qr-scan'></i> Realizar Pagamento Pix
                            </h3>
                            <p style="color: var(--clr-text); margin-bottom: 1.5rem; font-size: 0.95rem;">Escaneie o QR Code abaixo pelo aplicativo do banco ou repasse para a chave para finalizar.</p>
                            
                            <div style="margin-bottom: 1.5rem;">
                                <img src="data:image/jpeg;base64,${data.qr_code_base64}" alt="QR Code Pix" style="width: 200px; height: 200px; border-radius: var(--radius-md); padding: 10px; background: white; border: 1px solid var(--clr-border);">
                            </div>
                            
                            <div style="text-align: left;">
                                <label style="font-weight: 500; font-size: 0.85rem; margin-bottom: 0.5rem; display: block;">Copie e Cole a chave abaixo:</label>
                                <div style="display: flex; gap: 0.5rem;">
                                    <input type="text" style="flex:1; padding: 0.75rem 1rem; border: 1px solid var(--clr-border); border-radius: var(--radius-md); background-color: var(--clr-surface); color: var(--clr-text);" value="${data.qr_code}" id="pix-key" readonly>
                                    <button type="button" class="btn btn-primary" onclick="window.copyPix()" style="width: auto; padding: 0 1.5rem;">Copiar</button>
                                </div>
                            </div>
                        </div>
                        
                        <div style="margin-top: 2.5rem; display: none;" id="back-btn-box">
                            <a href="produtos.html" class="btn" style="border: 1px solid var(--clr-border); background: transparent; color: var(--clr-text); width: 100%;">Voltar para Loja de Produtos</a>
                        </div>
                    </div>
                `;
                window.scrollTo({ top: 0, behavior: 'smooth' });

                // Iniciar Polling a cada 3 segundos
                const interval = setInterval(() => {
                    fetch('api/check_payment.php?id=' + data.id)
                    .then(res => res.json())
                    .then(check => {
                        if (check.status === 'approved') {
                            clearInterval(interval);
                            document.getElementById('status-icon').className = 'bx bxs-check-circle';
                            document.getElementById('status-icon').style.color = '#10B981';
                            document.getElementById('status-title').innerText = 'Pagamento Concluído!';
                            document.getElementById('status-desc').innerText = 'O pedido está sendo preparado para a entrega.';
                            document.getElementById('payment-box').style.display = 'none';
                            document.getElementById('back-btn-box').style.display = 'block';
                        }
                    })
                    .catch(err => console.error("Erro no polling:", err));
                }, 3000);

            } else {
                throw new Error(data.error || "Erro desconhecido ao gerar QR Code.");
            }
        })
        .catch(err => {
            btn.innerHTML = "Confirmar Pagamento <i class='bx bx-check-shield'></i>";
            btn.style.opacity = '1';
            btn.disabled = false;
            alert("Erro ao processar pagamento: " + err.message);
        });
    });
}
