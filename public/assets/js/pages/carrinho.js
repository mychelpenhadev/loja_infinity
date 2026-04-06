document.addEventListener('DOMContentLoaded', () => {
            renderCart();
            window.addEventListener('cartUpdated', renderCart);
        });
        window.updateItemQuantity = (id, delta, color = null) => {
            const cartItems = window.CartManager.getCart();
            const item = cartItems.find(i => String(i.productId) === String(id) && i.color === color);
            if (item) {
                window.CartManager.updateQuantity(id, item.quantity + delta, color);
            }
        };
        window.removeItem = (id, color = null) => {
            if (confirm('Deseja realmente remover este item do carrinho?')) {
                window.CartManager.remove(id, color);
                window.showToast('Item removido com sucesso.', 'success');
            }
        };
        window.checkout = () => {
            const cartTools = window.CartManager.getCart();
            if(cartTools.length === 0) return;
            if (!window.isLoggedIn) {
                window.showToast('Faça login para finalizar a compra!', 'error');
                setTimeout(() => {
                    window.location.href = 'login.html';
                }, 1500);
                return;
            }
            const btn = document.getElementById('checkout-btn');
            if(btn) {
                btn.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> Redirecionando...";
                btn.style.opacity = '0.7';
                btn.disabled = true;
            }
            setTimeout(() => {
                window.location.href = 'pagamento.html';
            }, 800);
        };
        async function renderCart() {
            const container = document.getElementById('cart-content');
            if (!container) return;
            const cartItems = window.CartManager.getCart();            if (cartItems.length === 0) {
                if (!window.authChecked) {
                    return;
                }
                container.innerHTML = `
                    <div class="empty-cart-premium">
                        <i class='bx bx-cart-alt empty-cart-icon'></i>
                        <h2 style="font-size: 2rem; margin-bottom: 1rem; font-family: var(--font-display);">Seu carrinho está vazio</h2>
                        <p style="margin-bottom: 2.5rem; color: var(--clr-text-light); font-size: 1.1rem;">Explore nossa coleção e encontre algo especial para você!</p>
                        <a href="produtos" class="btn btn-primary" style="padding: 1rem 3rem; border-radius: 14px;">Explorar Loja</a>
                    </div>
                `;
                return;
            }

            container.innerHTML = `
                <div style="text-align: center; padding: 6rem; color: var(--clr-primary);">
                    <i class='bx bx-loader-alt bx-spin' style="font-size: 4rem; margin-bottom: 1.5rem;"></i>
                    <p style="font-size: 1.2rem; font-weight: 500;">Preparando sua seleção premium...</p>
                </div>
            `;
            let allProducts = [];
            try {
                const productIds = [...new Set(cartItems.map(item => item.productId))];
                allProducts = await window.ProductManager.getBatch(productIds);
            } catch (e) {
                console.error("Erro ao carregar produtos do carrinho:", e);
                container.innerHTML = `
                    <div class="empty-cart-premium" style="border-color: rgba(239, 68, 68, 0.2);">
                        <i class='bx bx-error-circle empty-cart-icon' style="background: #ef4444; -webkit-text-fill-color: #ef4444;"></i>
                        <h2>Ops! Algo deu errado</h2>
                        <p style="margin-bottom: 2rem;">Não conseguimos carregar os detalhes dos seus produtos.</p>
                        <button class="btn btn-primary" onclick="renderCart()">Tentar Novamente</button>
                    </div>
                `;
                return;
            }

            let itemsHTML = '';
            let subtotal = 0;
            cartItems.forEach(item => {
                const product = allProducts.find(p => String(p.id) === String(item.productId));
                if (product) {
                    const price = parseFloat(product.price);
                    const itemTotal = price * item.quantity;
                    subtotal += itemTotal;
                    const colorInfo = item.color ? `<div style="display: flex; align-items: center; gap: 6px; margin: 4px 0;"><div style="width: 12px; height: 12px; border-radius: 50%; background: ${item.color.toLowerCase() === 'preto' ? '#000' : (item.color.toLowerCase() === 'branco' ? '#fff' : 'var(--clr-primary)')}; border: 1px solid var(--glass-border);"></div><span style="font-size: 0.85rem; color: var(--clr-text-light); font-weight: 600;">${item.color}</span></div>` : '';
                    
                    itemsHTML += `
                        <div class="cart-item" data-color="${item.color || ''}">
                            <div class="cart-item-img-wrapper">
                                <img src="${product.image}" alt="${product.name}" class="cart-item-img">
                            </div>
                            <div class="cart-item-info">
                                <span class="cart-item-category">${product.category}</span>
                                <h3 class="cart-item-title">
                                    <a href="detalhes/${product.id}">${product.name}</a>
                                </h3>
                                ${colorInfo}
                                <div class="qty-controls-premium">
                                    <button class="qty-btn-premium" onclick="updateItemQuantity('${product.id}', -1, ${item.color ? `'${item.color}'` : 'null'})"><i class='bx bx-minus'></i></button>
                                    <input type="text" class="qty-input-premium" value="${item.quantity}" readonly>
                                    <button class="qty-btn-premium" onclick="updateItemQuantity('${product.id}', 1, ${item.color ? `'${item.color}'` : 'null'})"><i class='bx bx-plus'></i></button>
                                </div>
                            </div>
                            <div class="cart-item-right">
                                <span class="cart-item-price">${window.formatCurrency(itemTotal)}</span>
                                <button class="btn-remove-premium" onclick="removeItem('${product.id}', ${item.color ? `'${item.color}'` : 'null'})" title="Remover item">
                                    <i class='bx bx-trash-alt' style="font-size: 0.9rem;"></i> Excluir
                                </button>
                            </div>
                        </div>
                    `;
                }
            });

            container.innerHTML = `
                <div class="cart-layout">
                    <div class="cart-items-container">
                        ${itemsHTML}
                    </div>
                    <div class="order-summary-premium">
                        <h2 class="summary-title">Resumo do Pedido</h2>
                        <div class="summary-row">
                            <span>Subtotal (${window.CartManager.getTotalItems()} itens)</span>
                            <span style="color: var(--clr-text); font-weight: 600;">${window.formatCurrency(subtotal)}</span>
                        </div>
                        <div class="summary-row">
                            <span>Frete</span>
                            <span style="color: #10B981; font-weight: 600;">Grátis</span>
                        </div>
                        <div class="summary-total">
                            <span>Total</span>
                            <span class="total-amount">${window.formatCurrency(subtotal)}</span>
                        </div>
                        <button id="checkout-btn" class="btn-checkout-premium" onclick="checkout()">
                            Finalizar Compra <i class='bx bx-right-arrow-alt' style="font-size: 1.5rem;"></i>
                        </button>
                        <div style="margin-top: 2rem; display: flex; flex-direction: column; gap: 1rem;">
                            <div style="display: flex; align-items: center; gap: 10px; color: var(--clr-text-light); font-size: 0.85rem;">
                                <i class='bx bx-lock-alt' style="color: #10B981; font-size: 1.1rem;"></i>
                                Pagamento Seguro & Criptografado
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px; color: var(--clr-text-light); font-size: 0.85rem;">
                                <i class='bx bx-package' style="color: var(--clr-primary); font-size: 1.1rem;"></i>
                                Entrega garantida Infinity
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
