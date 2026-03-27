        async function verifyAdminAccess() {
            try {
                const response = await fetch('api/auth.php?action=check');
                const data = await response.json();
                if (!data.loggedIn || data.role !== 'admin') {
                    document.body.innerHTML = `
                        <div style="height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; background: var(--clr-bg); color: var(--clr-text);">
                            <i class='bx bx-lock-alt' style="font-size: 4rem; color: #EF4444; margin-bottom: 1rem;"></i>
                            <h1>Acesso Negado</h1>
                            <p style="margin-top: 0.5rem; color: var(--clr-text-light);">Apenas administradores podem acessar esta página.</p>
                            <a href="index.php" class="btn btn-primary" style="margin-top: 2rem;">Voltar ao Início</a>
                        </div>
                    `;
                    return false;
                }
                return true;
            } catch (err) {
                window.location.href = 'index.php';
                return false;
            }
        }
        const tableBody = document.getElementById('table-body');
        const modal = document.getElementById('productFormModal');
        const form = document.getElementById('productForm');

        const fileInput = document.getElementById('prod-imagem-file');
        const base64Input = document.getElementById('prod-imagem-base64');
        const previewContainer = document.getElementById('image-preview-container');
        const imagePreview = document.getElementById('image-preview');

        let allProducts = [];
        let filteredProducts = [];
        let currentPage = 1;
        const itemsPerPage = 10;
        let searchQuery = '';
        document.addEventListener('DOMContentLoaded', async () => {
            const isAuthorized = await verifyAdminAccess();
            if (isAuthorized) {
                await initialLoad();
                setupImageHandler();
                setupSearchHandler();
            }
        });
        async function initialLoad() {
            if (!tableBody) return;
            tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 2rem;"><i class="bx bx-loader-alt bx-spin"></i> Carregando catálogo...</td></tr>';
            try {
                window.ProductManager.clearCache();
                const data = await window.ProductManager.getAll({ limit: 1000 });
                allProducts = data.products || [];
                filteredProducts = [...allProducts];
                renderTable();
            } catch(e) {
                console.error('Erro ao carregar dados:', e);
                window.showToast('Erro ao carregar dados.', 'error');
                if (tableBody) {
                    tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 2rem; color: #EF4444;"><i class="bx bx-error-circle"></i> Não foi possível carregar o catálogo. Verifique sua conexão.</td></tr>';
                }
            }
        }
        function setupSearchHandler() {
            const adminSearch = document.getElementById('admin-search');
            if (adminSearch) {
                adminSearch.addEventListener('input', (e) => {
                    searchQuery = e.target.value.toLowerCase();
                    applyFilterAndRender();
                });
            }
        }
        function applyFilterAndRender() {
            filteredProducts = allProducts.filter(p =>
                p.name.toLowerCase().includes(searchQuery) ||
                p.category.toLowerCase().includes(searchQuery) ||
                (p.brand && p.brand.toLowerCase().includes(searchQuery))
            );
            currentPage = 1;
            renderTable();
        }
        function renderTable() {
            if (!tableBody) return;
            const start = (currentPage - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            const pageItems = filteredProducts.slice(start, end);
            const totalPages = Math.ceil(filteredProducts.length / itemsPerPage) || 1;
            tableBody.innerHTML = '';
            if (pageItems.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 2rem;">Nenhum produto encontrado.</td></tr>';
                renderAdminPagination(totalPages);
                return;
            }
            pageItems.forEach(p => {
                const tr = document.createElement('tr');
                tr.setAttribute('data-product-id', p.id);
                tr.innerHTML = `
                    <td>
                        <div class="prod-cell">
                            <img src="${p.image || 'assets/img/logoPNG.png'}" class="prod-thumb" alt="${p.name}">
                            <span style="font-weight: 500;">${p.name}</span>
                        </div>
                    </td>
                    <td><span style="background: var(--clr-bg); padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; text-transform:uppercase;">${p.category}</span></td>
                    <td style="font-weight: 600;">${window.formatCurrency(p.price)}</td>
                    <td>
                        <div class="action-btns">
                            <button class="edit-btn" onclick="editProduct('${p.id}')" title="Editar"><i class='bx bx-edit'></i></button>
                            <button class="delete-btn" onclick="deleteProduct('${p.id}')" title="Excluir"><i class='bx bx-trash'></i></button>
                        </div>
                    </td>
                `;
                tableBody.appendChild(tr);
            });
            renderAdminPagination(totalPages);
        }
        function renderAdminPagination(totalPages) {
            let container = document.getElementById('admin-pagination');
            if (!container) {
                container = document.createElement('div');
                container.id = 'admin-pagination';
                container.className = 'admin-table-footer';
                container.style.padding = '1rem';
                container.style.borderTop = '1px solid var(--clr-border)';
                container.style.display = 'flex';
                container.style.justifyContent = 'center';
                container.style.gap = '1rem';
                document.querySelector('.admin-table-container').appendChild(container);
            }
            if (totalPages <= 1) {
                container.innerHTML = '';
                container.style.display = 'none';
                return;
            }
            container.style.display = 'flex';
            container.innerHTML = `
                <button class="btn" ${currentPage === 1 ? 'disabled' : ''} onclick="changePage(${currentPage - 1})">Anterior</button>
                <span style="align-self: center; font-size: 0.9rem; font-weight: 500;">Página ${currentPage} de ${totalPages}</span>
                <button class="btn" ${currentPage === totalPages ? 'disabled' : ''} onclick="changePage(${currentPage + 1})">Próxima</button>
            `;
        }
        window.changePage = (p) => {
            currentPage = p;
            renderTable();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        };
        window.openModal = () => {
            document.getElementById('modal-title').innerText = 'Novo Produto';
            form.reset();
            document.getElementById('prod-id').value = '';
            previewContainer.style.display = 'none';
            modal.classList.add('active');
        };
        window.closeModal = () => {
            modal.classList.remove('active');
        };
        function setupImageHandler() {
            if (!fileInput) return;
            fileInput.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (event) => {
                        const base64 = event.target.result;
                        base64Input.value = base64;
                        imagePreview.src = base64;
                        previewContainer.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            });
            const btnRemove = document.getElementById('btn-remove-prod-img');
            if (btnRemove) {
                btnRemove.onclick = () => {
                    base64Input.value = '';
                    fileInput.value = '';
                    previewContainer.style.display = 'none';
                };
            }
        }
        window.editProduct = async (id) => {
            const row = document.querySelector(`tr[data-product-id="${id}"]`);
            if (row) row.style.opacity = '0.5';
            try {
                const res = await fetch(`api/products.php?action=get&id=${id}`, { credentials: 'include' });
                const product = await res.json();
                if (product && !product.error) {
                    document.getElementById('modal-title').innerText = 'Editar Produto';
                    document.getElementById('prod-id').value = product.id;
                    document.getElementById('prod-nome').value = product.name;
                    document.getElementById('prod-preco').value = product.price;
                    document.getElementById('prod-categoria').value = product.category;
                    document.getElementById('prod-marca').value = product.brand || '';
                    document.getElementById('prod-imagem-base64').value = product.image;
                    imagePreview.src = product.image;
                    previewContainer.style.display = 'block';
                    fileInput.value = '';
                    document.getElementById('prod-video').value = product.video || '';
                    document.getElementById('prod-desc').value = product.description;
                    modal.classList.add('active');
                } else {
                    window.showToast('Erro ao carregar dados.', 'error');
                }
            } catch(e) {
                window.showToast('Erro de conexão ao buscar detalhes.', 'error');
            } finally {
                if (row) row.style.opacity = '1';
            }
        };
        window.deleteProduct = async (id) => {
            const row = document.querySelector(`tr[data-product-id="${id}"]`);
            if (!row) return;
            row.style.transition = 'opacity 0.2s';
            row.style.opacity = '0.3';
            row.style.pointerEvents = 'none';
            try {
                const res = await fetch(`api/products.php?action=delete&id=${id}`, { credentials: 'include' });
                const data = await res.json();
                if (data.status === 'success') {

                    allProducts = allProducts.filter(p => p.id != id);
                    window.ProductManager.clearCache();
                    window.showToast('Produto removido!', 'success');
                    applyFilterAndRender();
                } else {
                    row.style.opacity = '1';
                    row.style.pointerEvents = '';
                    window.showToast('Erro ao excluir: ' + (data.message || ''), 'error');
                }
            } catch(e) {
                row.style.opacity = '1';
                row.style.pointerEvents = '';
                window.showToast('Erro de conexão ao excluir.', 'error');
            }
        };
        if (form) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const imageBase64 = document.getElementById('prod-imagem-base64').value;
                if (!imageBase64 && !id) {
                    alert("Por favor, selecione uma imagem para o novo produto.");
                    return;
                }
                const id = document.getElementById('prod-id').value;
                const productData = {
                    name: document.getElementById('prod-nome').value,
                    price: parseFloat(document.getElementById('prod-preco').value),
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
                        if (result.status === 'success') {

                            const idx = allProducts.findIndex(p => p.id == id);
                            if (idx !== -1) allProducts[idx] = { ...allProducts[idx], ...productData };
                        }
                    } else {
                        result = await window.ProductManager.add(productData);
                        if (result.status === 'success') {

                            allProducts.unshift({ id: result.id, ...productData });
                        }
                    }
                    if (result && result.status === 'success') {
                        closeModal();
                        window.showToast(id ? 'Produto atualizado!' : 'Produto adicionado!', 'success');
                        applyFilterAndRender();
                    } else {
                        window.showToast('Erro ao salvar: ' + (result ? result.message : 'Erro'), 'error');
                    }
                } catch(err) {
                    window.showToast('Erro ao salvar produto.', 'error');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerText = "Salvar Produto";
                }
            });
        }
