        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('featured-products');
            const products = window.ProductManager.getAll().slice(0, 4); 
            
            if (products.length === 0) {
                container.innerHTML = '<p>Nenhum produto encontrado.</p>';
                return;
            }

            products.forEach(product => {
                const isNew = product.rating >= 4.8;
                container.innerHTML += `
                    <div class="product-card">
                        ${isNew ? '<span class="product-badge">Novidade</span>' : ''}
                        
                        <div class="product-actions">
                            <button class="icon-btn" onclick="window.location.href='detalhes.html?id=${product.id}'" title="Ver Detalhes">
                                <i class='bx bx-show'></i>
                            </button>
                            <button class="icon-btn" title="Favoritar">
                                <i class='bx bx-heart'></i>
                            </button>
                        </div>

                        <a href="detalhes.html?id=${product.id}" class="product-image-container">
                            <img src="${product.image}" alt="${product.name}">
                        </a>
                        
                        <div class="product-info">
                            <span class="product-category">${product.category}</span>
                            <a href="detalhes.html?id=${product.id}" class="product-title">${product.name}</a>
                            
                            <div class="product-rating">
                                ${window.generateStars(product.rating)}
                                <span style="color: var(--clr-text-light); margin-left: auto; font-size: 0.75rem;">(${Math.floor(Math.random() * 50) + 10})</span>
                            </div>

                            <div class="product-footer">
                                <span class="product-price">${window.formatCurrency(product.price)}</span>
                                <button class="btn-add" onclick="window.handleAddToCart('${product.id}')">
                                    <i class='bx bx-cart-add'></i> Adicionar
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
        });
