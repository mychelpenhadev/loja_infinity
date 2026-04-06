document.addEventListener('DOMContentLoaded', async () => {
    const container = document.getElementById('catalog-grid');
    const searchInput = document.getElementById('search-input');
    const topicFilter = document.getElementById('topic-filter');
    const brandSelect = document.getElementById('brand-filter');
    let allProducts = [];
    let pagination = {};
    let currentPage = 1;
    let currentCategory = 'all';
    let currentBrand = 'all';
    let searchQuery = '';

    const urlParams = new URLSearchParams(window.location.search);
    const urlCat = urlParams.get('cat');
    if (urlCat) {
        currentCategory = urlCat;
    }
    async function loadProducts(page = 1) {
        if (!container) return;
        currentPage = page;
        renderSkeletons();
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
    if (topicFilter) {
        topicFilter.addEventListener('change', (e) => {
            currentCategory = e.target.value;
            loadProducts(1);
        });
    }
    if (brandSelect) {
        brandSelect.addEventListener('change', (e) => {
            currentBrand = e.target.value;
            renderProducts();
        });
    }
    function updateActivePill(cat) {
        if (topicFilter && topicFilter.value !== cat) {
            topicFilter.value = cat;
        }
        
        // Highlight active chip
        const chips = document.querySelectorAll('.category-chip');
        chips.forEach(chip => {
            if (chip.dataset.value === cat) {
                chip.classList.add('active');
                chip.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
            } else {
                chip.classList.remove('active');
            }
        });

        updateBrandFilterList(cat);
    }
    function updateBrandFilterList(cat) {
        if (!brandSelect) return;
        let dynamicBrands = [];
        if (cat === 'costura' || cat === 'mochilas') {
            const configKey = cat === 'costura' ? 'brandsCostura' : 'brandsMochilas';
            const configuredBrandsStr = window.ConfigManager.get(configKey);
            if (configuredBrandsStr) {
                dynamicBrands = configuredBrandsStr.split(',').map(b => b.trim()).filter(b => b.length > 0);
            }
        }
        if (dynamicBrands.length > 0) {
            brandSelect.style.display = 'inline-block';
            brandSelect.innerHTML = '<option value="all">Todas as Marcas</option>' +
                dynamicBrands.map(b => `<option value="${b.toLowerCase()}">${b}</option>`).join('');
        } else {
            brandSelect.style.display = 'none';
        }
        currentBrand = 'all';
    }
    function renderProducts() {
        if (!container) return;
        let filtered = allProducts;
        if (currentBrand !== 'all') {
            filtered = allProducts.filter(p => (p.brand || '').toLowerCase() === currentBrand.toLowerCase());
        }
        if (filtered.length === 0) {
            container.innerHTML = `
                <div class="no-results" style="grid-column: 1/-1; text-align:center; padding: 5rem;">
                    <i class='bx bx-search-alt-2' style="font-size: 5rem; color: var(--clr-text-light); margin-bottom: 1.5rem;"></i>
                    <h2 style="font-family: var(--font-display);">Nenhum produto encontrado</h2>
                    <p style="color: var(--clr-text-light);">Tente buscar por outro termo ou remova os filtros atuais.</p>
                </div>
            `;
            return;
        }

        container.innerHTML = filtered.map(product => {
            const soldCount = parseInt(product.sold_quantity || 0);
            const isOutOfStock = parseInt(product.stock_quantity || 0) <= 0;
            const oldPrice = product.price * 1.45;
            
            return `
                <a href="${window.location.origin}/detalhes/${product.id}" class="ali-style-card fade-in" id="prod-${product.id}">
                    <!-- Promo Header Banner -->
                    <div class="card-promo-header" style="background: var(--grad-primary); padding: 5px 12px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(0,0,0,0.05);">
                        <span style="font-weight: 800; font-size: 0.85rem; color: #003838;">Combos de ofertas</span>
                        <i class='bx bx-basket' style="color: #003838; font-size: 1.2rem; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));"></i>
                    </div>

                    <div class="ali-img-box">
                        <img src="${product.image_url}" alt="${product.name}" loading="lazy">
                    </div>
                    <div class="ali-card-content">
                        <div class="ali-prod-title">
                            ${product.name}
                        </div>
                        
                        <!-- New Image-Matched Price Layout -->
                        <div style="display: flex; align-items: flex-start; gap: 8px; margin-top: 8px;">
                            <div style="color: var(--clr-accent); font-size: 1.7rem; font-weight: 900; line-height: 1; letter-spacing: -1px;">
                                <span style="font-size: 1.1rem; font-weight: 800; margin-right: 1px;">R$</span>${parseFloat(product.price).toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                            </div>
                            
                            <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 5px; padding-top: 1px;">
                                <span style="background: rgba(239, 68, 68, 0.08); color: var(--clr-accent); font-size: 0.65rem; font-weight: 800; padding: 3px 6px; border-radius: 4px; line-height: 1; white-space: nowrap;">Oferta destaque</span>
                                <span style="color: #9ca3af; text-decoration: line-through; font-size: 0.75rem; font-weight: 600; line-height: 1;">R$ ${parseFloat(oldPrice).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</span>
                            </div>
                        </div>

                        <!-- Add to cart and standard info -->
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                            <div class="ali-rating-sold" style="margin: 0;">
                                <div style="display: flex; color: #ffd700;">
                                    <i class='bx bxs-star' style="font-size: 10px;"></i>
                                    <span style="font-weight: 700; margin-left: 2px; color: var(--clr-text);">${parseFloat(product.rating || 4.5).toFixed(1)}</span>
                                </div>
                                <div style="display: flex; gap: 4px; align-items: center; color: ${parseInt(product.stock_quantity || 0) <= 5 ? '#ef4444' : 'var(--clr-text-light)'};">
                                    <i class='bx bx-archive' style="font-size: 10px;"></i>
                                    <span>${parseInt(product.stock_quantity || 0)} em estoque</span>
                                </div>
                            </div>
                            <button class="ali-float-cart" style="margin-left: 0; width: 34px; height: 34px; transform: translateY(-5px);" onclick="event.preventDefault(); window.handleAddToCart('${product.id}')">
                                <i class='bx bx-cart-add'></i>
                            </button>
                        </div>

                        <button class="ali-buy-btn" onclick="event.preventDefault(); window.handleBuyNow('${product.id}')">
                            Comprar Agora
                        </button>
                    </div>
                </a>
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

    function renderSkeletons() {
        const skeletonsCount = 8;
        let html = '';
        for (let i = 0; i < skeletonsCount; i++) {
            html += `
                <div class="skeleton-card ali-style-card">
                    <div class="skeleton-img"></div>
                    <div class="skeleton-text" style="width: 70%;"></div>
                    <div class="skeleton-text" style="width: 40%; margin-bottom: 20px;"></div>
                    <div class="skeleton-price"></div>
                    <div class="skeleton-text" style="width: 90%; height: 35px; border-radius: 8px;"></div>
                </div>
            `;
        }
        container.innerHTML = html;
    }

    function setupCategoryChips() {
        const chipsContainer = document.getElementById('category-chips');
        if (!chipsContainer || !topicFilter) return;

        const options = Array.from(topicFilter.querySelectorAll('option'));
        let html = '';
        
        options.forEach(opt => {
            if (opt.parentElement && opt.parentElement.tagName === 'OPTGROUP') return; // Skip subcategories for chips to keep it clean
            
            html += `
                <div class="category-chip ${currentCategory === opt.value ? 'active' : ''}" data-value="${opt.value}">
                    ${opt.textContent}
                </div>
            `;
        });

        chipsContainer.innerHTML = html;

        chipsContainer.querySelectorAll('.category-chip').forEach(chip => {
            chip.addEventListener('click', () => {
                const val = chip.dataset.value;
                currentCategory = val;
                loadProducts(1);
            });
        });
    }

    setupCategoryChips();
});
