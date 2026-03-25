
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
                            <a href="index.html" class="btn btn-primary" style="margin-top: 2rem;">Voltar ao Início</a>
                        </div>
                    `;
                    return false;
                }
                return true;
            } catch (err) {
                window.location.href = 'index.html';
                return false;
            }
        }


        const tableBody = document.getElementById('table-body');
        const modal = document.getElementById('productFormModal');
        const form = document.getElementById('productForm');
        
        // Elementos de Imagem
        const fileInput = document.getElementById('prod-imagem-file');
        const base64Input = document.getElementById('prod-imagem-base64');
        const previewContainer = document.getElementById('image-preview-container');
        const imagePreview = document.getElementById('image-preview');

        document.addEventListener('DOMContentLoaded', async () => {
            const isAuthorized = await verifyAdminAccess();
            if (isAuthorized) {
                await renderTable();
                setupImageHandler();
                setupSearchHandler();
            }
        });

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        function setupSearchHandler() {
            const adminSearch = document.getElementById('admin-search');
            if (adminSearch) {
                const debouncedSearch = debounce((value) => {
                    searchQuery = value.toLowerCase();
                    renderTable(1);
                }, 400);

                adminSearch.addEventListener('input', (e) => {
                    debouncedSearch(e.target.value);
                });
            }
        }

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
        }

        async function renderTable() {
            if (!tableBody) return;
            tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 2rem;"><i class="bx bx-loader-alt bx-spin"></i> Carregando produtos...</td></tr>';
            const products = await window.ProductManager.getAll();
            tableBody.innerHTML = '';

            if (products.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 2rem;">Nenhum produto cadastrado.</td></tr>';
                return;
            }

            products.forEach(p => {
                const tr = document.createElement('tr');
                tr.setAttribute('data-product-id', p.id);
                tr.innerHTML = `
                    <td>
                        <div class="prod-cell">
                            <img src="${p.image}" class="prod-thumb" alt="${p.name}">
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
        }

        window.editProduct = async (id) => {
            const product = await window.ProductManager.getById(id);
            if (product) {
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
            }
        };

        window.deleteProduct = async (id) => {
            const row = document.querySelector(`tr[data-product-id="${id}"]`);
            if (!row) return;

            // Remove otimisticamente do DOM imediatamente
            row.style.transition = 'opacity 0.2s';
            row.style.opacity = '0.3';
            row.style.pointerEvents = 'none';

            try {
                const res = await fetch(`api/products.php?action=delete&id=${id}`, { credentials: 'include' });
                const data = await res.json();
                if (data.status === 'success') {
                    row.remove();
                    window.ProductManager.clearCache();
                    window.showToast('Produto removido!', 'success');
                    const remaining = tableBody.querySelectorAll('tr[data-product-id]');
                    if (remaining.length === 0) {
                        tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:2rem;">Nenhum produto cadastrado.</td></tr>';
                    }
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
                if (!imageBase64) {
                    alert("Por favor, selecione uma imagem do seu computador (ou mantenha a atual se estiver editando).");
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
                    } else {
                        result = await window.ProductManager.add(productData);
                    }

                    if (result && result.status === 'success') {
                        closeModal(); // Fecha imediatamente
                        window.showToast(id ? 'Produto atualizado!' : 'Produto adicionado!', 'success');
                        renderTable(); // Atualiza em background sem await
                    } else {
                        window.showToast('Erro ao salvar: ' + (result ? result.message : 'Resposta inválida do servidor'), 'error');
                    }
                } catch(err) {
                    window.showToast('Erro ao salvar produto.', 'error');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerText = "Salvar Produto";
                }
            });
        }

        let currentPage = 1;
        let totalPages = 1;
        let searchQuery = '';

        async function renderTable(page = 1) {
            currentPage = page;
            if (!tableBody) return;
            tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 2rem;"><i class="bx bx-loader-alt bx-spin"></i> Carregando produtos...</td></tr>';
            
            try {
                const data = await window.ProductManager.getAll({ 
                    page: currentPage, 
                    limit: 10,
                    search: searchQuery
                });
                const products = data.products || [];
                const pagination = data.pagination || {};
                totalPages = pagination.pages || 1;

                tableBody.innerHTML = '';

                if (products.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 2rem;">Nenhum produto cadastrado.</td></tr>';
                    return;
                }

                products.forEach(p => {
                    const tr = document.createElement('tr');
                    tr.setAttribute('data-product-id', p.id);
                    tr.innerHTML = `
                        <td>
                            <div class="prod-cell">
                                <img src="${p.image}" class="prod-thumb" alt="${p.name}">
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

                renderAdminPagination();
            } catch(e) {
                const errorMsg = e.message || 'Erro desconhecido';
                tableBody.innerHTML = `<tr><td colspan="4" style="text-align:center; padding: 2rem; color: #EF4444;">
                    <i class='bx bx-error-circle' style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>
                    Erro ao carregar os produtos.<br>
                    <small style="opacity: 0.8;">Detalhes: ${errorMsg}</small>
                </td></tr>`;
                window.showToast('Erro ao carregar produtos.', 'error');
            }
        }

        function renderAdminPagination() {
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
                container.style.display = 'none';
                return;
            }

            container.style.display = 'flex';
            container.innerHTML = `
                <button class="btn" ${currentPage === 1 ? 'disabled' : ''} onclick="renderTable(${currentPage - 1})">Anterior</button>
                <span style="align-self: center;">Página ${currentPage} de ${totalPages}</span>
                <button class="btn" ${currentPage === totalPages ? 'disabled' : ''} onclick="renderTable(${currentPage + 1})">Próxima</button>
            `;
        }
