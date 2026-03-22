document.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const productId = urlParams.get('id');
            const container = document.getElementById('product-content');

            if (!productId) {
                container.innerHTML = `<h2>Produto não encontrado.</h2>`;
                return;
            }

            const product = window.ProductManager.getById(productId);

            if (!product) {
                container.innerHTML = `
                    <div style="text-align: center; padding: 4rem 0;">
                        <i class='bx bx-error-circle' style="font-size: 4rem; color: var(--clr-text-light); margin-bottom: 1rem;"></i>
                        <h2>Ops! Esse produto não existe.</h2>
                        <a href="produtos.html" class="btn btn-primary" style="margin-top: 2rem;">Ver Catálogo</a>
                    </div>
                `;
                return;
            }

            
            document.title = `${product.name} | Infinity Variedades`;

            let quantity = 1;

            const renderContent = () => {
                container.innerHTML = `
                    <div class="details-grid">
                        <div class="product-gallery">
                            <img src="${product.image}" alt="${product.name}">
                        </div>

                        <div class="product-info-full">
                            <div class="product-meta">
                                <span class="category-tag">${product.category}</span>
                                <h1 class="product-title-large">${product.name}</h1>
                                <div class="product-rating" style="font-size: 1.125rem;">
                                    ${window.generateStars(product.rating)}
                                    <span style="color: var(--clr-text-light); margin-left: 0.5rem; font-size: 0.875rem;">(Avaliações de Clientes)</span>
                                </div>
                            </div>

                            <p class="desc-text">${product.description}</p>
                            
                            <div class="price-large">
                                ${window.formatCurrency(product.price)}
                            </div>

                            <div class="cart-actions">
                                <div class="qty-controls">
                                    <button class="qty-btn" id="btn-minus"><i class='bx bx-minus'></i></button>
                                    <input type="text" class="qty-input" id="qty-display" value="${quantity}" readonly>
                                    <button class="qty-btn" id="btn-plus"><i class='bx bx-plus'></i></button>
                                </div>
                                <button class="btn btn-primary btn-large" id="btn-add-cart">
                                    Adicionar ao Carrinho <i class='bx bx-shopping-bag'></i>
                                </button>
                            </div>

                            <!-- Features List -->
                            <div style="margin-top: 3rem;">
                                <h3 style="margin-bottom: 1rem; font-size: 1.125rem;">Por que amamos este produto?</h3>
                                <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.75rem;">
                                    <li style="display: flex; align-items: center; gap: 0.5rem;"><i class='bx bx-check' style="color: #10B981; font-size: 1.25rem;"></i> Qualidade Premium Garantida</li>
                                    <li style="display: flex; align-items: center; gap: 0.5rem;"><i class='bx bx-check' style="color: #10B981; font-size: 1.25rem;"></i> Design Exclusivo</li>
                                    <li style="display: flex; align-items: center; gap: 0.5rem;"><i class='bx bx-check' style="color: #10B981; font-size: 1.25rem;"></i> Compra 100% Segura</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                `;

                
                if (product.video && product.video.trim() !== '') {
                    container.innerHTML += `
                        <div class="video-container">
                            <h2 class="section-title" style="font-size: 2rem; margin-bottom: 1.5rem; text-align: center;">Veja em Detalhes</h2>
                            <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: var(--radius-lg); box-shadow: var(--shadow-md);">
                                <iframe style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none;" src="${product.video}" allowfullscreen></iframe>
                            </div>
                        </div>
                    `;
                }

                attachEvents();
            };

            const attachEvents = () => {
                const btnMinus = document.getElementById('btn-minus');
                const btnPlus = document.getElementById('btn-plus');
                const display = document.getElementById('qty-display');
                const btnAddCart = document.getElementById('btn-add-cart');

                btnMinus.addEventListener('click', () => {
                    if (quantity > 1) {
                        quantity--;
                        display.value = quantity;
                    }
                });

                btnPlus.addEventListener('click', () => {
                    if (quantity < 10) {
                        quantity++;
                        display.value = quantity;
                    }
                });

                btnAddCart.addEventListener('click', () => {
                    if (!window.isLoggedIn) {
                        window.showToast('Faça login para adicionar produtos ao carrinho!', 'error');
                        setTimeout(() => { window.location.href = 'login.html'; }, 1500);
                        return;
                    }
                    window.CartManager.add(product.id, quantity);
                    window.showToast(`Adicionado ${quantity} uni. ao carrinho!`);
                    quantity = 1;
                    display.value = quantity;
                });
            };

            renderContent();
        });
