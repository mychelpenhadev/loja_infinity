document.addEventListener('DOMContentLoaded', async () => {
            const container = document.getElementById('catalog-grid');
            const searchInput = document.getElementById('search-input');
            const filterBtns = document.querySelectorAll('.filter-pill');
            const brandSelect = document.getElementById('brand-filter');
            
            // Mostrar loader inicial
            if(container) container.innerHTML = '<div style="grid-column: 1/-1; text-align:center; padding: 3rem;"><i class="bx bx-loader-alt bx-spin" style="font-size: 3rem; color: var(--clr-primary);"></i><p style="margin-top: 1rem;">Carregando catálogo...</p></div>';

            let allProducts = await window.ProductManager.getAll();
            let currentCategory = 'all';
            let currentBrand = 'all';
            let searchQuery = '';

            // Handle URL category
            const urlParams = new URLSearchParams(window.location.search);
            const urlCat = urlParams.get('cat');
            if (urlCat) {
                currentCategory = urlCat;
                updateActivePill(urlCat);
            }

            renderProducts();

            if (searchInput) {
                searchInput.addEventListener('input', (e) => {
                    searchQuery = e.target.value.toLowerCase();
                    renderProducts();
                });
            }

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
                updateBrandFilterList(cat);
            }

            function updateBrandFilterList(cat) {
                if (!brandSelect) return;
                
                if (cat === 'costura' || cat === 'canetas') {
                    let configuredBrandsStr = '';
                    if (cat === 'costura') {
                        configuredBrandsStr = window.ConfigManager.get('brandsCostura');
                    } else if (cat === 'canetas') {
                        configuredBrandsStr = window.ConfigManager.get('brandsCanetas');
                    }

                    let brands = [];
                    if (configuredBrandsStr && configuredBrandsStr.trim().length > 0) {
                        brands = configuredBrandsStr.split(',').map(b => b.trim()).filter(b => b.length > 0);
                    } else {
                        const catProducts = allProducts.filter(p => (p.category || '').toLowerCase().includes(cat));
                        const uniqueBrands = new Set();
                        catProducts.forEach(p => {
                            if (p.brand && p.brand.trim() !== '') uniqueBrands.add(p.brand.trim());
                        });
                        brands = Array.from(uniqueBrands);
                    }

                    if (brands.length > 0) {
                        brandSelect.style.display = 'inline-block';
                        brandSelect.innerHTML = '<option value="all">Todas as Marcas</option>' + 
                            brands.map(b => `<option value="${b.toLowerCase()}">${b}</option>`).join('');
                    } else {
                        brandSelect.style.display = 'none';
                    }
                } else {
                    brandSelect.style.display = 'none';
                }
                
                currentBrand = 'all';
                brandSelect.value = 'all';
            }

            // Init brands
            updateBrandFilterList(currentCategory);

            function renderProducts() {
                if (!container) return;
                
                let filtered = allProducts.filter(p => {
                    const name = p.name || '';
                    const desc = p.description || '';
                    const cat = p.category || '';
                    const brand = p.brand || '';

                    const matchesSearch = name.toLowerCase().includes(searchQuery) || desc.toLowerCase().includes(searchQuery);
                    
                    let matchesCategory = currentCategory === 'all' || cat.toLowerCase().includes(currentCategory);
                    
                    if (currentCategory === 'novidades') {
                        matchesCategory = parseFloat(p.rating) >= 4.8 || cat.toLowerCase().includes('novid');
                    }
                    if (currentCategory === 'promocoes') {
                        matchesCategory = cat.toLowerCase().includes('promo');
                    }
                    if (currentCategory === 'criancas') {
                        matchesCategory = cat.toLowerCase().includes('crianca') || cat.toLowerCase().includes('infantil');
                    }
                    if (currentCategory === 'materiais') {
                        matchesCategory = cat.toLowerCase().includes('materiais') || cat.toLowerCase().includes('estojo');
                    }
                    
                    const matchesBrand = currentBrand === 'all' || 
                        brand.toLowerCase() === currentBrand ||
                        name.toLowerCase().includes(currentBrand) ||
                        desc.toLowerCase().includes(currentBrand);
                    
                    return matchesSearch && matchesCategory && matchesBrand;
                });

                if (filtered.length === 0) {
                    container.innerHTML = `
                        <div class="no-results" style="grid-column: 1/-1; text-align:center; padding: 4rem;">
                            <i class='bx bx-search-alt' style="font-size: 4rem; color: var(--clr-text-light); margin-bottom: 1rem;"></i>
                            <h2>Nenhum produto encontrado</h2>
                            <p>Tente buscar por outro termo ou limpe os filtros.</p>
                        </div>
                    `;
                    return;
                }

                container.innerHTML = filtered.map(product => {
                    const isNew = parseFloat(product.rating) >= 4.8;
                    return `
                        <div class="product-card">
                            ${isNew ? '<span class="product-badge">Novidade</span>' : ''}
                            
                            <div class="product-actions">
                                <button class="icon-btn" onclick="window.location.href='detalhes.html?id=${product.id}'" title="Ver Detalhes">
                                    <i class='bx bx-show'></i>
                                </button>
                            </div>

                            <a href="detalhes.html?id=${product.id}" class="product-image-container">
                                <img src="${product.image}" alt="${product.name}" loading="lazy" decoding="async">
                            </a>
                            
                            <div class="product-info">
                                <span class="product-category">${product.category}</span>
                                <a href="detalhes.html?id=${product.id}" class="product-title">${product.name}</a>
                                
                                <div class="product-rating">
                                    ${window.generateStars(product.rating)}
                                </div>

                                <div class="product-footer">
                                    <span class="product-price">${window.formatCurrency(product.price)}</span>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <button class="btn" style="padding: 0.5rem 0.75rem; font-size: 0.875rem; background-color: var(--clr-primary); color: white;" onclick="window.handleBuyNow('${product.id}')">
                                            Comprar
                                        </button>
                                        <button class="btn-add" style="padding: 0.5rem 0.75rem; font-size: 0.875rem;" onclick="window.handleAddToCart('${product.id}')" title="Adicionar ao Carrinho">
                                            <i class='bx bx-cart-add'></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');
            }
        });
