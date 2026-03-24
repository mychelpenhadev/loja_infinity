document.addEventListener('DOMContentLoaded', async () => {
    const container = document.getElementById('catalog-grid');
    const searchInput = document.getElementById('search-input');
    const filterBtns = document.querySelectorAll('.filter-pill');
    const brandSelect = document.getElementById('brand-filter');
    
    let allProducts = [];
    let pagination = {};
    let currentPage = 1;
    let currentCategory = 'all';
    let currentBrand = 'all';
    let searchQuery = '';

    // Handle URL category
    const urlParams = new URLSearchParams(window.location.search);
    const urlCat = urlParams.get('cat');
    if (urlCat) {
        currentCategory = urlCat;
    }

    async function loadProducts(page = 1) {
        if (!container) return;
        currentPage = page;
        container.innerHTML = '<div style="grid-column: 1/-1; text-align:center; padding: 3rem;"><i class="bx bx-loader-alt bx-spin" style="font-size: 3rem; color: var(--clr-primary);"></i><p style="margin-top: 1rem;">Carregando catálogo...</p></div>';
        
        try {
            const data = await window.ProductManager.getAll({ 
                page: currentPage, 
                limit: 12, 
                cat: currentCategory === 'all' ? '' : currentCategory,
                search: searchQuery
            });
            
            allProducts = data.products || [];
            pagination = data.pagination || {};
            
            updateActivePill(currentCategory);
            renderProducts();
            renderPagination();

            // Handle Fast Scroll from anchor
            if (window.location.hash && window.location.hash.startsWith('#prod-')) {
                setTimeout(() => {
                    const target = document.querySelector(window.location.hash);
                    if (target) {
                        target.scrollIntoView({ block: 'start' });
                    }
                    const hideStyle = document.getElementById('fast-scroll-hide');
                    if (hideStyle) hideStyle.remove();
                }, 100);
            }
        } catch (e) {
            container.innerHTML = `<div style="grid-column: 1/-1; text-align:center; padding: 3rem; color: #EF4444;"><i class='bx bx-error-circle' style="font-size: 3rem;"></i><p>Erro ao carregar os produtos. Detalhes: ${e.message}</p></div>`;
        }
    }

    loadProducts();

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            searchQuery = e.target.value.toLowerCase();
            loadProducts(1);
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
            loadProducts(1);
        });
    });

    function updateActivePill(cat) {
        filterBtns.forEach(b => b.classList.remove('active'));
        const activeBtn = document.querySelector(`.filter-pill[data-cat="${cat}"]`);
        if (activeBtn) activeBtn.classList.add('active');
        
        const subFilters = document.getElementById('sub-filters-costura');
        if (subFilters) {
            const isCosturaRelada = cat === 'costura' || 
                ['agulhas', 'armarinhos', 'botoes', 'barbantes', 'bordados', 'cama', 'croche', 'fitas', 'las', 'linhas', 'embalagens'].includes(cat);
            subFilters.style.display = isCosturaRelada ? 'contents' : 'none';
        }

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
                const uniqueBrands = new Set();
                allProducts.forEach(p => {
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

    function renderProducts() {
        if (!container) return;
        
        let filtered = allProducts;
        if (currentBrand !== 'all') {
            filtered = allProducts.filter(p => (p.brand || '').toLowerCase() === currentBrand.toLowerCase());
        }

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
                <div class="product-card" id="prod-${product.id}">
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

    function renderPagination() {
        let paginationContainer = document.getElementById('pagination-container');
        if (!paginationContainer) {
            paginationContainer = document.createElement('div');
            paginationContainer.id = 'pagination-container';
            paginationContainer.style.width = '100%';
            paginationContainer.style.gridColumn = '1 / -1';
            container.after(paginationContainer);
        }

        if (!pagination.pages || pagination.pages <= 1) {
            paginationContainer.innerHTML = '';
            return;
        }

        let html = `
            <div style="display: flex; justify-content: center; align-items: center; gap: 0.5rem; margin-top: 3rem; flex-wrap: wrap;">
                <button class="btn" ${currentPage === 1 ? 'disabled' : ''} onclick="window.changePage(${currentPage - 1})" style="padding: 0.5rem 1rem; background: var(--clr-surface); border: 1px solid var(--clr-border); cursor: pointer;">
                    <i class='bx bx-chevron-left'></i>
                </button>
        `;

        for (let i = 1; i <= pagination.pages; i++) {
            if (i === 1 || i === pagination.pages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                html += `
                    <button class="btn ${i === currentPage ? 'active' : ''}" onclick="window.changePage(${i})" style="padding: 0.5rem 1rem; cursor: pointer; border-radius: 4px; ${i === currentPage ? 'background: var(--clr-primary); color: white; border: none;' : 'background: var(--clr-surface); border: 1px solid var(--clr-border);'}">
                        ${i}
                    </button>
                `;
            } else if (i === currentPage - 3 || i === currentPage + 3) {
                html += `<span style="padding: 0.5rem;">...</span>`;
            }
        }

        html += `
                <button class="btn" ${currentPage === pagination.pages ? 'disabled' : ''} onclick="window.changePage(${currentPage + 1})" style="padding: 0.5rem 1rem; background: var(--clr-surface); border: 1px solid var(--clr-border); cursor: pointer;">
                    <i class='bx bx-chevron-right'></i>
                </button>
            </div>
        `;
        paginationContainer.innerHTML = html;
    }

    window.changePage = (page) => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
        loadProducts(page);
    };
});
