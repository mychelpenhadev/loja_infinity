document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('catalog-grid');
            const searchInput = document.getElementById('search-input');
            const filterBtns = document.querySelectorAll('.filter-pill');
            const brandSelect = document.getElementById('brand-filter');
            
            let allProducts = window.ProductManager.getAll();
            let currentCategory = 'all';
            let currentBrand = 'all';
            let searchQuery = '';

            
            const urlParams = new URLSearchParams(window.location.search);
            const urlCat = urlParams.get('cat');
            if (urlCat) {
                currentCategory = urlCat;
                updateActivePill(urlCat);
            }

            
            renderProducts();

            
            searchInput.addEventListener('input', (e) => {
                searchQuery = e.target.value.toLowerCase();
                renderProducts();
            });

            // Listen to brand select changes
            if (brandSelect) {
                brandSelect.addEventListener('change', (e) => {
                    currentBrand = e.target.value;
                    renderProducts();
                });
            }

            
            filterBtns.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    currentCategory = e.target.getAttribute('data-cat');
                    updateActivePill(currentCategory);
                    renderProducts();
                });
            });

            function updateActivePill(cat) {
                filterBtns.forEach(b => b.classList.remove('active'));
                const activeBtn = document.querySelector(`.filter-pill[data-cat="${cat}"]`);
                if (activeBtn) activeBtn.classList.add('active');
            }

            function renderProducts() {
                
                let filtered = allProducts.filter(p => {
                    const matchesSearch = p.name.toLowerCase().includes(searchQuery) || p.description.toLowerCase().includes(searchQuery);
                    
                    const matchesCategory = currentCategory === 'all' || p.category.toLowerCase().includes(currentCategory);
                    
                    const matchesBrand = currentBrand === 'all' || (p.brand && p.brand.toLowerCase() === currentBrand);
                    
                    return matchesSearch && matchesCategory && matchesBrand;
                });

                if (filtered.length === 0) {
                    container.innerHTML = `
                        <div class="no-results">
                            <i class='bx bx-search-alt' style="font-size: 4rem; color: var(--clr-text-light); margin-bottom: 1rem;"></i>
                            <h2>Nenhum produto encontrado</h2>
                            <p>Tente buscar por outro termo ou limpe os filtros.</p>
                        </div>
                    `;
                    return;
                }

                container.innerHTML = filtered.map(product => {
                    const isNew = product.rating >= 4.8;
                    return `
                        <div class="product-card">
                            ${isNew ? '<span class="product-badge">Novidade</span>' : ''}
                            
                            <div class="product-actions">
                                <button class="icon-btn" onclick="window.location.href='detalhes.html?id=${product.id}'" title="Ver Detalhes">
                                    <i class='bx bx-show'></i>
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
                }).join('');
            }
        });
