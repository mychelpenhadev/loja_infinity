
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

        document.addEventListener('DOMContentLoaded', async () => {
            const isAuthorized = await verifyAdminAccess();
            if (isAuthorized) {
                await renderTable();
            }
        });

        async function renderTable() {
            tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 2rem;"><i class="bx bx-loader-alt bx-spin"></i> Carregando produtos...</td></tr>';
            const products = await window.ProductManager.getAll();
            tableBody.innerHTML = '';

            if (products.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 2rem;">Nenhum produto cadastrado.</td></tr>';
                return;
            }

            products.forEach(p => {
                const tr = document.createElement('tr');
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
            if (confirm('Tem certeza que deseja excluir permanentemente este produto?')) {
                await window.ProductManager.remove(id);
                window.CartManager.remove(id); 
                window.showToast('Produto removido com sucesso!', 'success');
                await renderTable();
            }
        };

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

            if (id) {
                await window.ProductManager.update(id, productData);
                window.showToast('Produto atualizado com sucesso!');
            } else {
                await window.ProductManager.add(productData);
                window.showToast('Produto adicionado ao catálogo!');
            }

            submitBtn.disabled = false;
            submitBtn.innerText = "Salvar Produto";
            closeModal();
            await renderTable();
        });
