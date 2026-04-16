(function() {
    let tableBody, modal, form, fileInput, base64Input, previewContainer, imagePreview;
    let allProducts = [];
    let filteredProducts = [];
    let currentPage = 1;
    const itemsPerPage = 10;
    let searchQuery = '';

    async function verifyAdminAccess() {
        try {
            const response = await fetch('api/auth?action=check');
            const data = await response.json();
            if (!data.loggedIn || data.role !== 'admin') {
                document.body.innerHTML = `
                    <div style="height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; background: var(--clr-bg); color: var(--clr-text);">
                        <i class='bx bx-lock-alt' style="font-size: 4rem; color: #EF4444; margin-bottom: 1rem;"></i>
                        <h1>Acesso Negado</h1>
                        <p style="margin-top: 0.5rem; color: var(--clr-text-light);">Apenas administradores podem acessar esta página.</p>
                        <a href="/" class="btn btn-primary" style="margin-top: 2rem;">Voltar ao Início</a>
                    </div>
                `;
                return false;
            }
            return true;
        } catch (err) {
            window.location.href = '/';
            return false;
        }
    }

    document.addEventListener('DOMContentLoaded', async () => {
        const isAuthorized = await verifyAdminAccess();
        if (!isAuthorized) return;

        // Initialize Elements
        tableBody = document.getElementById('table-body');
        modal = document.getElementById('productFormModal');
        form = document.getElementById('productForm');
        fileInput = document.getElementById('prod-imagem-file');
        base64Input = document.getElementById('prod-imagem-base64');
        previewContainer = document.getElementById('image-preview-container');
        imagePreview = document.getElementById('image-preview');

        await initialLoad();
        setupImageHandler();
        setupSearchHandler();
        setupFormHandler();
        setupPricingLogic();
    });

    async function initialLoad() {
        if (!tableBody) return;
        tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 4rem;"><i class="bx bx-loader-alt bx-spin" style="font-size: 2rem; color: var(--clr-primary);"></i><p style="margin-top: 1rem; color: var(--clr-text-light);">Carregando catálogo premium...</p></td></tr>';
        
        try {
            window.ProductManager.clearCache();
            const data = await window.ProductManager.getAll({ limit: 1000 });
            allProducts = data.products || [];
            filteredProducts = [...allProducts];
            
            updateDashboardStats();
            renderTable();
        } catch(e) {
            console.error('Erro ao carregar dados:', e);
            window.showToast?.('Erro ao carregar dados.', 'error');
            if (tableBody) {
                tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 4rem; color: #EF4444;"><i class="bx bx-error-circle" style="font-size: 3rem;"></i><p style="margin-top: 1rem;">Não foi possível carregar o catálogo.</p></td></tr>';
            }
        }
    }

    async function updateDashboardStats() {
        const totalProductsEl = document.getElementById('stat-total-products');
        const totalCategoriesEl = document.getElementById('stat-total-categories');
        const avgPriceEl = document.getElementById('stat-avg-price');
        const totalSalesEl = document.getElementById('stat-total-sales');

        if (totalProductsEl) totalProductsEl.innerText = allProducts.length;
        if (totalCategoriesEl) {
            const categories = new Set(allProducts.map(p => p.category));
            totalCategoriesEl.innerText = categories.size;
        }
        if (avgPriceEl && allProducts.length > 0) {
            const totalValue = allProducts.reduce((sum, p) => sum + parseFloat(p.price || 0), 0);
            avgPriceEl.innerText = window.formatCurrency(totalValue / allProducts.length);
        }
    }

    function setupSearchHandler() {
        const adminSearch = document.getElementById('admin-search');
        if (adminSearch) {
            adminSearch.addEventListener('input', (e) => {
                searchQuery = e.target.value.toLowerCase();
                filteredProducts = allProducts.filter(p =>
                    p.name.toLowerCase().includes(searchQuery) ||
                    p.category.toLowerCase().includes(searchQuery) ||
                    (p.brand && p.brand.toLowerCase().includes(searchQuery))
                );
                currentPage = 1;
                renderTable();
            });
        }
    }

    function renderTable() {
        if (!tableBody) return;
        const start = (currentPage - 1) * itemsPerPage;
        const pageItems = filteredProducts.slice(start, start + itemsPerPage);
        const totalPages = Math.ceil(filteredProducts.length / itemsPerPage) || 1;
        
        tableBody.innerHTML = '';
        if (pageItems.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 4rem; color: var(--clr-text-light);">Nenhum produto encontrado.</td></tr>';
            renderPagination(totalPages);
            return;
        }

        pageItems.forEach((p, index) => {
            const tr = document.createElement('tr');
            tr.setAttribute('data-product-id', p.id);
            tr.style.animation = `fadeInUp 0.4s ease forwards ${index * 0.05}s`;
            tr.style.opacity = '0';
            
            tr.innerHTML = `
                <td data-label="Produto">
                    <div class="prod-cell" style="display: flex; align-items: center; gap: 1.25rem;">
                        <img src="${p.image_url || 'assets/img/logoPNG.png'}" class="prod-thumb" style="width: 50px; height: 50px; border-radius: 12px; object-fit: cover; border: 2px solid var(--clr-border);">
                        <div>
                            <div style="font-weight: 600; color: var(--clr-text); font-size: 1rem;">${p.name}</div>
                            <div style="font-size: 0.75rem; color: var(--clr-text-light); text-transform: uppercase;">${p.brand || 'Infinity'}</div>
                        </div>
                    </div>
                </td>
                <td data-label="Categoria"><span class="cat-badge">${p.category}</span></td>
                <td data-label="Preço"><span class="prod-price">${window.formatCurrency(p.price)}</span></td>
                <td data-label="Ações">
                    <div class="action-btns" style="justify-content: flex-end;">
                        <button class="circle-btn edit" onclick="window.editProduct('${p.id}')"><i class='bx bx-edit-alt'></i></button>
                        <button class="circle-btn delete" onclick="window.deleteProduct('${p.id}')"><i class='bx bx-trash'></i></button>
                    </div>
                </td>
            `;
            tableBody.appendChild(tr);
        });
        renderCards(pageItems);
        renderPagination(totalPages);
    }

    function renderCards(pageItems) {
        const cardsList = document.getElementById('product-cards-list');
        if (!cardsList) return;
        cardsList.innerHTML = '';

        if (pageItems.length === 0) {
            cardsList.innerHTML = '<p style="text-align:center;padding:3rem;color:var(--clr-text-light);">Nenhum produto encontrado.</p>';
            return;
        }

        pageItems.forEach((p, index) => {
            const card = document.createElement('div');
            card.className = 'product-card-item';
            card.style.animationDelay = `${index * 0.04}s`;
            card.innerHTML = `
                <img src="${p.image_url || 'assets/img/logoPNG.png'}" class="product-card-thumb" alt="${p.name}">
                <div class="product-card-info">
                    <div class="product-card-name">${p.name}</div>
                    <div class="product-card-meta">
                        <span class="cat-badge" style="font-size:0.65rem;padding:0.2rem 0.6rem;">${p.category}</span>
                        ${p.brand ? `<span>${p.brand}</span>` : ''}
                    </div>
                    <div class="product-card-price">${window.formatCurrency(p.price)}</div>
                </div>
                <div class="product-card-actions">
                    <button class="circle-btn edit" onclick="window.editProduct('${p.id}')" title="Editar"><i class='bx bx-edit-alt'></i></button>
                    <button class="circle-btn delete" onclick="window.deleteProduct('${p.id}')" title="Excluir"><i class='bx bx-trash'></i></button>
                </div>
            `;
            cardsList.appendChild(card);
        });
    }

    function renderPagination(totalPages) {
        let container = document.getElementById('admin-pagination');
        if (!container) {
            container = document.createElement('div');
            container.id = 'admin-pagination';
            container.className = 'admin-table-footer';
            document.querySelector('.admin-table-wrapper').after(container);
        }
        if (totalPages <= 1) { container.style.display = 'none'; return; }
        container.style.display = 'flex';
        container.style.justifyContent = 'center';
        container.style.gap = '1rem';
        container.style.padding = '2rem';

        container.innerHTML = `
            <button class="btn" style="border: 1px solid var(--clr-border);" ${currentPage === 1 ? 'disabled' : ''} onclick="window.changePage(${currentPage - 1})"><i class='bx bx-chevron-left'></i>Anterior</button>
            <span style="display:flex; align-items:center; padding:0 1rem; color:var(--clr-text-light);">Página ${currentPage} de ${totalPages}</span>
            <button class="btn" style="border: 1px solid var(--clr-border);" ${currentPage === totalPages ? 'disabled' : ''} onclick="window.changePage(${currentPage + 1})">Próxima<i class='bx bx-chevron-right'></i></button>
        `;
    }

    window.changePage = (p) => {
        currentPage = p;
        renderTable();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    window.openModal = () => {
        if (!modal || !form) return;
        document.getElementById('modal-title').innerText = 'Novo Produto';
        form.reset();
        document.getElementById('prod-id').value = '';
        document.getElementById('prod-original-price').value = '';
        document.getElementById('prod-discount').value = '';
        document.getElementById('prod-stock').value = '';
        document.getElementById('prod-sold').value = '';
        if (previewContainer) previewContainer.style.display = 'none';
        const placeholder = document.getElementById('upload-placeholder');
        if (placeholder) placeholder.style.display = 'block';
        modal.classList.add('active');
    };

    window.closeModal = () => {
        if (modal) modal.classList.remove('active');
    };

    window.editProduct = async (id) => {
        const row = document.querySelector(`tr[data-product-id="${id}"]`);
        if (row) row.style.opacity = '0.5';
        try {
            const res = await fetch(`api/products.php?action=get&id=${id}`);
            const product = await res.json();
            if (product && !product.error) {
                document.getElementById('modal-title').innerText = 'Editar Produto';
                document.getElementById('prod-id').value = product.id;
                document.getElementById('prod-nome').value = product.name;
                document.getElementById('prod-preco').value = product.price;
                document.getElementById('prod-original-price').value = product.original_price || '';
                document.getElementById('prod-discount').value = product.discount_percent || '';
                document.getElementById('prod-stock').value = product.stock_quantity || 0;
                document.getElementById('prod-sold').value = product.sold_quantity || 0;
                document.getElementById('prod-categoria').value = product.category;
                document.getElementById('prod-marca').value = product.brand || '';
                document.getElementById('prod-imagem-base64').value = product.image;
                if (imagePreview) imagePreview.src = product.image_url;
                if (previewContainer) previewContainer.style.display = 'block';
                const placeholder = document.getElementById('upload-placeholder');
                if (placeholder) placeholder.style.display = 'none';
                
                document.getElementById('prod-video').value = product.video || '';
                document.getElementById('prod-desc').value = product.description;
                if (modal) modal.classList.add('active');
            }
        } catch(e) {
            window.showToast?.('Erro ao carregar dados do produto.', 'error');
        } finally {
            if (row) row.style.opacity = '1';
        }
    };

    // --- Dynamic Calculation Logic ---
    function setupPricingLogic() {
        const originalInput = document.getElementById('prod-original-price');
        const discountInput = document.getElementById('prod-discount');
        const saleInput = document.getElementById('prod-preco');

        if (!originalInput || !discountInput || !saleInput) return;

        // Calc Sale Price from Original + Discount
        const updateSalePrice = () => {
            const original = parseFloat(originalInput.value) || 0;
            const discount = parseFloat(discountInput.value) || 0;
            if (original > 0) {
                const sale = original - (original * (discount / 100));
                saleInput.value = sale.toFixed(2);
            }
        };

        // Calc Discount from Original + Sale Price
        const updateDiscount = () => {
            const original = parseFloat(originalInput.value) || 0;
            const sale = parseFloat(saleInput.value) || 0;
            if (original > 0 && sale > 0) {
                const discount = ((original - sale) / original) * 100;
                discountInput.value = Math.round(discount);
            }
        };

        originalInput.addEventListener('input', updateSalePrice);
        discountInput.addEventListener('input', updateSalePrice);
        saleInput.addEventListener('input', updateDiscount);
    }

    window.deleteProduct = async (id) => {
        if (!confirm("Deseja realmente excluir este produto?")) return;
        try {
            const res = await fetch(`api/products.php?action=delete&id=${id}`);
            const data = await res.json();
            if (data.status === 'success') {
                allProducts = allProducts.filter(p => p.id != id);
                filteredProducts = filteredProducts.filter(p => p.id != id);
                window.ProductManager.clearCache();
                window.showToast?.('Produto removido!', 'success');
                renderTable();
                updateDashboardStats();
            }
        } catch(e) {
            window.showToast?.('Erro de conexão ao excluir.', 'error');
        }
    };

    function setupImageHandler() {
        if (!fileInput) return;
        fileInput.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (file) {
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> Otimizando...";

                try {
                    const compressedBase64 = await window.ImageOptimizer.compress(file, {
                        maxWidth: 1000,
                        maxHeight: 1000,
                        quality: 0.8,
                        returnBase64: true
                    });
                    if (base64Input) base64Input.value = compressedBase64;
                    if (imagePreview) imagePreview.src = compressedBase64;
                    if (previewContainer) previewContainer.style.display = 'block';
                    const placeholder = document.getElementById('upload-placeholder');
                    if (placeholder) placeholder.style.display = 'none';
                } catch (err) {
                    console.error("Erro ao otimizar imagem:", err);
                    window.showToast?.('Erro ao processar imagem.', 'error');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            }
        });
        const btnRemove = document.getElementById('btn-remove-prod-img');
        if (btnRemove) {
            btnRemove.onclick = (e) => {
                e.stopPropagation(); // Prevent triggering the zone click
                if (base64Input) base64Input.value = '';
                if (fileInput) fileInput.value = '';
                if (previewContainer) previewContainer.style.display = 'none';
                const placeholder = document.getElementById('upload-placeholder');
                if (placeholder) placeholder.style.display = 'block';
            };
        }
    }

    function setupFormHandler() {
        if (!form) return;
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('prod-id').value;
            const imageBase64 = document.getElementById('prod-imagem-base64').value;
            
            if (!imageBase64 && !id) {
                alert("Por favor, selecione uma imagem.");
                return;
            }

            const productData = {
                id: id,
                name: document.getElementById('prod-nome').value,
                price: parseFloat(document.getElementById('prod-preco').value),
                original_price: parseFloat(document.getElementById('prod-original-price').value) || null,
                discount_percent: parseInt(document.getElementById('prod-discount').value) || null,
                stock_quantity: parseInt(document.getElementById('prod-stock').value) || 0,
                sold_quantity: parseInt(document.getElementById('prod-sold').value) || 0,
                category: document.getElementById('prod-categoria').value,
                brand: document.getElementById('prod-marca').value.trim(),
                image: imageBase64,
                video: document.getElementById('prod-video').value,
                description: document.getElementById('prod-desc').value
            };

            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> Salvando...";

            try {
                let result;
                if (id) {
                    result = await window.ProductManager.update(id, productData);
                } else {
                    result = await window.ProductManager.add(productData);
                }

                if (result && result.status === 'success') {
                    window.closeModal();
                    window.showToast?.(id ? 'Produto atualizado!' : 'Produto adicionado!', 'success');
                    await initialLoad(); // Refresh everything safely
                } else {
                    window.showToast?.('Erro: ' + (result?.message || 'Falha ao salvar'), 'error');
                }
            } catch(err) {
                window.showToast?.('Erro ao salvar produto.', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerText = "Salvar Produto";
            }
        });
    }
})();
