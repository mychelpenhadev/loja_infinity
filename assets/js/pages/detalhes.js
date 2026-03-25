document.addEventListener('DOMContentLoaded', async () => {
            const urlParams = new URLSearchParams(window.location.search);
            const productId = urlParams.get('id') || urlParams.get('product_id');
            const container = document.getElementById('product-content');
            console.log('[Debug] ID do produto da URL:', productId);
            if (!productId || productId === 'undefined' || productId === 'null') {
                console.error('[Debug] ID do produto inválido:', productId);
                if(container) container.innerHTML = `<h2 style="text-align:center; padding: 4rem;">ID do produto inválido ou não fornecido.</h2>`;
                return;
            }

            if(container) container.innerHTML = '<div style="text-align:center; padding: 5rem;"><i class="bx bx-loader-alt bx-spin" style="font-size: 4rem; color: var(--clr-primary);"></i><p style="margin-top:1rem;">Buscando detalhes do produto...</p></div>';
            const setupBackLink = () => {
                const backLink = document.getElementById('back-link');
                const backText = document.getElementById('back-text');
                const referrer = document.referrer;
                if (backLink && backText) {
                    if (referrer.includes('index.php') || referrer.includes('index.php') || (referrer === window.location.origin + '/') || referrer === '') {
                        backLink.href = 'index.php#prod-' + productId;
                        backText.textContent = 'Voltar para o Início';
                    } else if (referrer.includes('produtos.html')) {

                        const baseReferrer = referrer.split('#')[0];
                        backLink.href = baseReferrer + '#prod-' + productId;
                        backText.textContent = 'Voltar para a Coleção';
                    } else {

                        backLink.addEventListener('click', (e) => {
                            if (window.history.length > 1) {
                                e.preventDefault();
                                window.history.back();
                            }
                        });
                    }
                }
            };
            setupBackLink();
            const product = await window.ProductManager.getById(productId);
            console.log('[Debug] Produto retornado pelo Manager:', product);
            if (!product) {
                if(container) {
                    container.innerHTML = `
                        <div style="text-align: center; padding: 4rem 0;">
                            <i class='bx bx-error-circle' style="font-size: 4rem; color: var(--clr-text-light); margin-bottom: 1rem;"></i>
                            <h2>Ops! Esse produto não foi encontrado no nosso banco de dados.</h2>
                            <p style="color: var(--clr-text-light); margin-bottom: 2rem;">Ele pode ter sido removido ou o link está incorreto.</p>
                            <a href="produtos.html" class="btn btn-primary">Ver Todos os Produtos</a>
                        </div>
                    `;
                }
                return;
            }
            document.title = `${product.name} | Infinity Variedades`;
            let quantity = 1;
            let selectedColor = null;
            const COLOR_CATEGORIES = window.COLOR_CATEGORIES || ['linhas', 'las', 'croche'];
            const COLOR_PALETTE = [
                { name: 'Branco', hex: '#FFFFFF' },
                { name: 'Preto', hex: '#000000' },
                { name: 'Vermelho', hex: '#EF4444' },
                { name: 'Azul Marinho', hex: '#1E3A8A' },
                { name: 'Rosa Bebê', hex: '#FBCFE8' },
                { name: 'Amarelo Canário', hex: '#FDE047' },
                { name: 'Verde Bandeira', hex: '#059669' },
                { name: 'Cinza Mescla', hex: '#94A3B8' },
                { name: 'Marrom Café', hex: '#451A03' }
            ];
            const renderContent = () => {
                if(!container) return;
                const needsColor = COLOR_CATEGORIES.includes(product.category.toLowerCase()) ||
                                 (product.category_id && COLOR_CATEGORIES.includes(product.category_id.toLowerCase()));
                let colorHtml = '';
                if (needsColor) {
                    colorHtml = `
                        <div class="color-selection" style="margin: 2rem 0; padding: 1.5rem; background: var(--clr-bg); border-radius: var(--radius-md); border: 1px solid var(--clr-border);">
                            <h3 style="font-size: 1rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                                <i class='bx bx-palette'></i> Escolha uma Cor: <span id="selected-color-name" style="color: var(--clr-primary); font-weight: 700;"></span>
                            </h3>
                            <div style="display: flex; flex-wrap: wrap; gap: 0.75rem;">
                                ${COLOR_PALETTE.map(color => `
                                    <div class="color-option" data-color="${color.name}" title="${color.name}"
                                         style="width: 35px; height: 35px; border-radius: 50%; background-color: ${color.hex}; cursor: pointer; border: 2px solid ${color.hex === '#FFFFFF' ? '#e2e8f0' : 'transparent'}; box-shadow: var(--shadow-sm); transition: var(--transition);">
                                    </div>
                                `).join('')}
                            </div>
                            <p style="font-size: 0.75rem; color: var(--clr-text-light); margin-top: 0.75rem;">* Seleção obrigatória para este item.</p>
                        </div>
                    `;
                }
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
                            ${colorHtml}
                            <div class="cart-actions" style="flex-wrap: wrap; gap: 1rem;">
                                <div class="qty-controls">
                                    <button class="qty-btn" id="btn-minus"><i class='bx bx-minus'></i></button>
                                    <input type="text" class="qty-input" id="qty-display" value="${quantity}" readonly>
                                    <button class="qty-btn" id="btn-plus"><i class='bx bx-plus'></i></button>
                                </div>
                                <button class="btn btn-primary btn-large" id="btn-buy-now" style="flex: 1;">
                                    Comprar Agora
                                </button>
                                <button class="btn btn-large" id="btn-add-cart" style="flex: 1; border: 1px solid var(--clr-primary); background: transparent; color: var(--clr-primary);">
                                    Adicionar <i class='bx bx-cart-add'></i>
                                </button>
                            </div>
                            <!-- Features List -->
                            <div style="margin-top: 3rem;">
                                <h3 style="margin-bottom: 1rem; font-size: 1.125rem;">Por que amamos este produto?</h3>
                                <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.75rem;">
                                    <li style="display: flex; align-items: center; gap: 0.5rem;"><i class='bx bx-check' style="color: #10B981; font-size: 1.25rem;"></i> Qualidade Premium Garantida</li>
                                    <li style="display: flex; align-items: center; gap: 0.5rem;"><i class='bx bx-check' style="color: #10B981; font-size: 1.25rem;"></i> Cores Vivas e Duradouras</li>
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

                const style = document.createElement('style');
                style.textContent = `
                    .color-option.active {
                        transform: scale(1.2);
                        border: 3px solid var(--clr-primary) !important;
                        box-shadow: 0 0 10px rgba(var(--clr-primary-rgb), 0.5);
                    }
                `;
                document.head.appendChild(style);
                attachEvents(needsColor);
            };
            const attachEvents = (needsColor) => {
                const btnMinus = document.getElementById('btn-minus');
                const btnPlus = document.getElementById('btn-plus');
                const display = document.getElementById('qty-display');
                const btnAddCart = document.getElementById('btn-add-cart');
                const btnBuyNow = document.getElementById('btn-buy-now');
                const colorOptions = document.querySelectorAll('.color-option');
                const colorLabel = document.getElementById('selected-color-name');
                if (colorOptions) {
                    colorOptions.forEach(opt => {
                        opt.addEventListener('click', () => {
                            colorOptions.forEach(o => o.classList.remove('active'));
                            opt.classList.add('active');
                            selectedColor = opt.dataset.color;
                            if (colorLabel) colorLabel.textContent = selectedColor;
                        });
                    });
                }
                if (btnMinus) {
                    btnMinus.addEventListener('click', () => {
                        if (quantity > 1) {
                            quantity--;
                            display.value = quantity;
                        }
                    });
                }
                if (btnPlus) {
                    btnPlus.addEventListener('click', () => {
                        if (quantity < 10) {
                            quantity++;
                            display.value = quantity;
                        }
                    });
                }
                if (btnAddCart) {
                    btnAddCart.addEventListener('click', () => {
                        if (needsColor && !selectedColor) {
                            window.showToast('Por favor, selecione uma cor antes de continuar!', 'warning');
                            return;
                        }
                        if (!window.isLoggedIn) {
                            window.showToast('Faça login para adicionar produtos ao carrinho!', 'error');
                            setTimeout(() => { window.location.href = 'login.html'; }, 1500);
                            return;
                        }
                        window.CartManager.add(product.id, quantity, selectedColor);
                        window.showToast(`Adicionado ${quantity} uni. (${selectedColor || ''}) ao carrinho!`);
                        quantity = 1;
                        display.value = quantity;

                        if (colorOptions) colorOptions.forEach(o => o.classList.remove('active'));
                        if (colorLabel) colorLabel.textContent = '';
                        selectedColor = null;
                    });
                }
                if (btnBuyNow) {
                    btnBuyNow.addEventListener('click', () => {
                        if (needsColor && !selectedColor) {
                            window.showToast('Por favor, selecione uma cor antes de continuar!', 'warning');
                            return;
                        }
                        window.handleBuyNow(product.id, quantity, selectedColor);
                    });
                }
            };
            renderContent();
        });
