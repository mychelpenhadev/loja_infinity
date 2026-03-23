document.addEventListener('DOMContentLoaded', () => {
            renderCart();
            window.addEventListener('cartUpdated', renderCart);
        });

        window.updateItemQuantity = (id, delta) => {
            const cartItems = window.CartManager.getCart();
            const item = cartItems.find(i => String(i.productId) === String(id));
            if (item) {
                window.CartManager.updateQuantity(id, item.quantity + delta);
            }
        };

        window.removeItem = (id) => {
            if (confirm('Deseja realmente remover este item do carrinho?')) {
                window.CartManager.remove(id);
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

            const cartItems = window.CartManager.getCart();

            if (cartItems.length === 0) {
                if(!container.querySelector('.bxs-check-circle')){ 
                    container.innerHTML = `
                        <div class="empty-cart">
                            <i class='bx bx-shopping-bag' style="font-size: 5rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <h2>Seu carrinho está vazio</h2>
                            <p style="margin-bottom: 2rem;">Adicione itens adoráveis para começar suas compras!</p>
                            <a href="produtos.html" class="btn btn-primary">Ir para Loja</a>
                        </div>
                    `;
                }
                return;
            }

            // Mostrar mini loader
            container.style.opacity = '0.6';
            
            const allProducts = await window.ProductManager.getAll();
            container.style.opacity = '1';

            let itemsHTML = '';
            let subtotal = 0;

            cartItems.forEach(item => {
                const product = allProducts.find(p => String(p.id) === String(item.productId));
                if (product) {
                    const price = parseFloat(product.price);
                    const itemTotal = price * item.quantity;
                    subtotal += itemTotal;

                    itemsHTML += `
                        <div class="cart-item">
                            <img src="${product.image}" alt="${product.name}" class="cart-item-img">
                            
                            <div class="cart-item-info">
                                <span class="cart-item-category">${product.category}</span>
                                <h3 class="cart-item-title">
                                    <a href="detalhes.html?id=${product.id}">${product.name}</a>
                                </h3>
                                <div class="qty-controls">
                                    <button class="qty-btn" onclick="updateItemQuantity('${product.id}', -1)"><i class='bx bx-minus'></i></button>
                                    <input type="text" class="qty-input" value="${item.quantity}" readonly>
                                    <button class="qty-btn" onclick="updateItemQuantity('${product.id}', 1)"><i class='bx bx-plus'></i></button>
                                </div>
                            </div>

                            <span class="cart-item-price">${window.formatCurrency(itemTotal)}</span>
                            
                            <button class="btn-remove" onclick="removeItem('${product.id}')" title="Remover item">
                                <i class='bx bx-trash'></i>
                            </button>
                        </div>
                    `;
                }
            });

            const total = subtotal;

            container.innerHTML = `
                <div class="cart-layout">
                    <div class="cart-items-container">
                        ${itemsHTML}
                    </div>

                    <div class="order-summary">
                        <h2 style="font-size: 1.5rem; margin-bottom: 1.5rem;">Resumo do Pedido</h2>
                        
                        <div class="summary-row">
                            <span>Subtotal (${window.CartManager.getTotalItems()} itens)</span>
                            <span style="color: var(--clr-text); font-weight: 500;">${window.formatCurrency(subtotal)}</span>
                        </div>

                        <div class="summary-total">
                            <span>Total</span>
                            <span style="color: var(--clr-accent);">${window.formatCurrency(total)}</span>
                        </div>

                        <button id="checkout-btn" class="btn btn-primary btn-checkout" onclick="checkout()">
                            Finalizar Compra <i class='bx bx-check-shield' ></i>
                        </button>
                        
                        <p style="text-align: center; margin-top: 1rem; color: var(--clr-text-light); font-size: 0.75rem;">
                            <i class='bx bx-lock-alt'></i> Pagamento 100% seguro via Stripe
                        </p>
                    </div>
                </div>
            `;
        }
