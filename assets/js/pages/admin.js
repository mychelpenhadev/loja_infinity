
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
                renderTable();
            }
        });

        function renderTable() {
            const products = window.ProductManager.getAll();
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

        function openModal() {
            document.getElementById('modal-title').innerText = 'Novo Produto';
            form.reset();
            document.getElementById('prod-id').value = '';
            document.getElementById('prod-marca').value = '';
            document.getElementById('prod-imagem-base64').value = '';
            document.getElementById('image-preview-container').style.display = 'none';
            document.getElementById('image-preview').src = '';
            modal.classList.add('active');
        }

        
        const fileInput = document.getElementById('prod-imagem-file');
        const base64Input = document.getElementById('prod-imagem-base64');
        const imagePreview = document.getElementById('image-preview');
        const previewContainer = document.getElementById('image-preview-container');

        fileInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    base64Input.value = e.target.result;
                    imagePreview.src = e.target.result;
                    previewContainer.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });

        function closeModal() {
            modal.classList.remove('active');
        }

        window.editProduct = (id) => {
            const product = window.ProductManager.getById(id);
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
                fileInput.value = ''; // Reset file input

                document.getElementById('prod-video').value = product.video || '';
                document.getElementById('prod-desc').value = product.description;
                
                modal.classList.add('active');
            }
        };

        window.deleteProduct = (id) => {
            if (confirm('Tem certeza que deseja excluir permanentemente este produto?')) {
                window.ProductManager.remove(id);
                window.CartManager.remove(id); 
                window.showToast('Produto removido com sucesso!', 'success');
                renderTable();
            }
        };

        form.addEventListener('submit', (e) => {
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

            if (id) {
                
                window.ProductManager.update(id, productData);
                window.showToast('Produto atualizado com sucesso!');
            } else {
                
                window.ProductManager.add(productData);
                window.showToast('Produto adicionado ao catálogo!');
            }

            closeModal();
            renderTable();
        });
