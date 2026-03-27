document.addEventListener('DOMContentLoaded', async () => {

    try {
        const response = await fetch('api/auth.php?action=check');
        const data = await response.json();
        if (!data.loggedIn || data.role !== 'admin') {
            window.location.href = 'index.php';
            return;
        }
    } catch(err) {
        window.location.href = 'index.php';
        return;
    }

    if (window.ConfigManager && window.ConfigManager.init) {
        await window.ConfigManager.init();
    }
    loadSettings();
    setupForm();
    setupBanners();
    setupBackup();
    setupRestore();
});

function loadSettings() {
    const whatsappInput = document.getElementById('config-whatsapp');
    if (!whatsappInput) return;
    const savedWhatsapp = window.ConfigManager.get('whatsappNumber');
    if (savedWhatsapp) whatsappInput.value = savedWhatsapp;
}

function setupForm() {
    const form = document.getElementById('configForm');
    if (!form) return;
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const whatsappVal = document.getElementById('config-whatsapp').value.trim();
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> Salvando...";
        await window.ConfigManager.set('whatsappNumber', whatsappVal);
        submitBtn.disabled = false;
        submitBtn.innerHTML = "<i class='bx bx-save'></i> Salvar WhatsApp";
        window.showToast("WhatsApp salvo com sucesso!", "success");
    });
}

async function setupBanners() {
    const list = document.getElementById('banners-list');
    const addBtn = document.getElementById('add-banner-btn');
    const saveBtn = document.getElementById('save-banners-btn');
    if (!list || !addBtn || !saveBtn) return;

    const allProducts = [];
    try {
        const data = await window.ProductManager.getAll({ limit: 500 });
        (data.products || []).forEach(p => allProducts.push(p));
    } catch(e) {}

    const savedBannersRaw = window.ConfigManager.get('hero_banners');
    let savedBanners = [];
    try { savedBanners = JSON.parse(savedBannersRaw) || []; } catch(e) {}

    if (savedBanners.length > 0) {
        savedBanners.forEach(b => addBannerItem(list, allProducts, b.url || '', b.productId || '', b.link || ''));
    } else {
        addBannerItem(list, allProducts, '', '', '');
    }

    addBtn.addEventListener('click', () => addBannerItem(list, allProducts, '', '', ''));

    saveBtn.addEventListener('click', async () => {
        const items = list.querySelectorAll('.banner-item');
        const banners = [];
        saveBtn.disabled = true;
        saveBtn.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> Salvando...";

        for (const item of items) {
            const fileInput = item.querySelector('.banner-file-input');
            const customLinkToggle = item.querySelector('.banner-custom-link-toggle');
            const customLinkInput = item.querySelector('.banner-custom-link-input');
            const existingUrl = item.dataset.existingUrl || '';
            const isCustomLink = customLinkToggle && customLinkToggle.checked;
            let productId = item.dataset.productId || '';
            let link = '';
            if (isCustomLink && customLinkInput) {
                link = customLinkInput.value.trim();
                productId = '__custom__';
            } else if (productId) {
                link = 'detalhes.html?id=' + productId;
            }

            if (fileInput && fileInput.files.length > 0) {
                const formData = new FormData();
                formData.append('banner', fileInput.files[0]);
                try {
                    const resp = await fetch('api/config.php?action=upload-banner', {
                        method: 'POST',
                        body: formData,
                        credentials: 'include'
                    });
                    const result = await resp.json();
                    if (result.status === 'success') {
                        banners.push({ url: result.url, productId, link });
                    } else {
                        window.showToast(result.message || 'Erro no upload', 'error');
                    }
                } catch (err) {
                    window.showToast('Erro ao fazer upload da imagem', 'error');
                }
            } else if (existingUrl) {
                banners.push({ url: existingUrl, productId, link });
            }
        }

        const result = await window.ConfigManager.set('hero_banners', JSON.stringify(banners));
        saveBtn.disabled = false;
        saveBtn.innerHTML = "<i class='bx bx-save'></i> Salvar Banners";
        if (result && result.status === 'error') {
            window.showToast(result.message || 'Erro ao salvar banners', 'error');
        } else {
            window.showToast(`${banners.length} banner(s) salvo(s) com sucesso!`, "success");
        }
    });
}

function addBannerItem(list, allProducts, url, productId, link) {
    const item = document.createElement('div');
    item.className = 'banner-item';
    if (url) item.dataset.existingUrl = url;
    item.dataset.productId = productId && productId !== '__custom__' ? productId : '';

    const selectedProduct = productId && productId !== '__custom__' ? allProducts.find(p => p.id == productId) : null;
    const searchValue = selectedProduct ? selectedProduct.name : '';
    const showCustomLink = productId === '__custom__';
    const customLinkValue = showCustomLink ? (link || '') : '';

    item.innerHTML = `
        <div>
            <label style="display:block; font-size:0.8rem; color:var(--clr-text-light); margin-bottom:0.4rem;">Imagem do Banner *</label>
            <input type="file" class="form-control banner-file-input" accept="image/*">
        </div>
        <div>
            <label style="display:block; font-size:0.8rem; color:var(--clr-text-light); margin-bottom:0.4rem;">Vincular Produto</label>
            <div class="banner-product-search" style="position:relative;">
                <input type="text" class="form-control banner-search-input" placeholder="Pesquisar produto..." value="${searchValue}" autocomplete="off">
                <div class="banner-search-results" style="display:none; position:absolute; top:100%; left:0; right:0; background:var(--clr-surface, #fff); border:1px solid var(--clr-border, #ddd); border-radius:6px; max-height:200px; overflow-y:auto; z-index:100; box-shadow:0 4px 12px rgba(0,0,0,0.15);"></div>
            </div>
            <div style="margin-top:0.4rem;">
                <label style="display:inline-flex; align-items:center; gap:0.3rem; font-size:0.8rem; color:var(--clr-text-light); cursor:pointer;">
                    <input type="checkbox" class="banner-custom-link-toggle" ${showCustomLink ? 'checked' : ''}> Link personalizado
                </label>
            </div>
            <input type="text" class="form-control banner-custom-link-input" placeholder="https://... ou produtos.html?cat=..." value="${customLinkValue}" style="margin-top:0.3rem; ${showCustomLink ? '' : 'display:none;'}">
        </div>
        <button class="banner-remove-btn" title="Remover"><i class='bx bx-trash'></i></button>
    `;
    item.querySelector('.banner-remove-btn').addEventListener('click', () => item.remove());

    const searchInput = item.querySelector('.banner-search-input');
    const resultsDiv = item.querySelector('.banner-search-results');
    const customLinkToggle = item.querySelector('.banner-custom-link-toggle');
    const customLinkInput = item.querySelector('.banner-custom-link-input');

    customLinkToggle.addEventListener('change', () => {
        customLinkInput.style.display = customLinkToggle.checked ? '' : 'none';
    });

    searchInput.addEventListener('input', () => {
        const query = searchInput.value.trim().toLowerCase();
        item.dataset.productId = '';
        if (query.length < 1) { resultsDiv.style.display = 'none'; return; }
        const matches = allProducts.filter(p => p.name.toLowerCase().includes(query)).slice(0, 10);
        if (matches.length === 0) {
            resultsDiv.innerHTML = '<div style="padding:0.6rem; color:var(--clr-text-light); font-size:0.85rem;">Nenhum produto encontrado</div>';
        } else {
            resultsDiv.innerHTML = matches.map(p =>
                `<div class="banner-search-item" data-id="${p.id}" style="padding:0.5rem 0.7rem; cursor:pointer; font-size:0.85rem; border-bottom:1px solid var(--clr-border, #eee); display:flex; align-items:center; gap:0.5rem;">
                    <img src="${p.image}" style="width:30px; height:30px; object-fit:cover; border-radius:4px;" onerror="this.style.display='none'">
                    <span>${p.name}</span>
                </div>`
            ).join('');
        }
        resultsDiv.style.display = 'block';

        resultsDiv.querySelectorAll('.banner-search-item').forEach(el => {
            el.addEventListener('click', () => {
                const id = el.dataset.id;
                const product = allProducts.find(p => p.id == id);
                if (product) {
                    searchInput.value = product.name;
                    item.dataset.productId = id;
                    resultsDiv.style.display = 'none';
                }
            });
        });
    });

    searchInput.addEventListener('focus', () => {
        if (searchInput.value.trim().length >= 1) searchInput.dispatchEvent(new Event('input'));
    });

    document.addEventListener('click', (e) => {
        if (!item.querySelector('.banner-product-search').contains(e.target)) {
            resultsDiv.style.display = 'none';
        }
    });

    if (url) {
        const preview = document.createElement('img');
        preview.className = 'banner-preview';
        preview.src = url;
        preview.style.display = 'block';
        item.appendChild(preview);
    }

    const fileInput = item.querySelector('.banner-file-input');
    fileInput.addEventListener('change', () => {
        if (fileInput.files.length > 0) {
            const reader = new FileReader();
            reader.onload = (e) => {
                let preview = item.querySelector('.banner-preview');
                if (!preview) {
                    preview = document.createElement('img');
                    preview.className = 'banner-preview';
                    item.appendChild(preview);
                }
                preview.src = e.target.result;
                preview.style.display = 'block';
                delete item.dataset.existingUrl;
            };
            reader.readAsDataURL(fileInput.files[0]);
        }
    });

    list.appendChild(item);
}

function setupBackup() {
    const backupBtn = document.getElementById('backup-btn');
    const statusEl = document.getElementById('backup-status');
    if (!backupBtn) return;

    backupBtn.addEventListener('click', async () => {
        backupBtn.disabled = true;
        const originalHTML = backupBtn.innerHTML;
        backupBtn.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> Gerando backup...";
        
        if (statusEl) {
            statusEl.style.display = 'block';
            statusEl.style.color = 'var(--clr-text-light)';
            statusEl.textContent = 'Preparando arquivo ZIP, isso pode levar alguns segundos...';
        }

        try {
            const response = await fetch('api/backup.php', {
                method: 'POST'
            });

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.message || 'Erro ao gerar backup');
            }

            const disposition = response.headers.get('Content-Disposition');
            let filename = 'backup_loja.zip';
            if (disposition && disposition.indexOf('attachment') !== -1) {
                const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                const matches = filenameRegex.exec(disposition);
                if (matches != null && matches[1]) { 
                    filename = matches[1].replace(/['"]/g, '');
                }
            }

            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);

            if (statusEl) {
                statusEl.style.color = '#10B981';
                statusEl.textContent = 'Backup gerado e download iniciado!';
            }
            window.showToast("Backup gerado com sucesso!", "success");
        } catch (err) {
            console.error(err);
            if (statusEl) {
                statusEl.style.color = '#EF4444';
                statusEl.textContent = 'Erro: ' + err.message;
            }
            window.showToast(err.message, "error");
        } finally {
            backupBtn.disabled = false;
            backupBtn.innerHTML = originalHTML;
        }
    });
}

function setupRestore() {
    const restoreBtn = document.getElementById('restore-btn');
    const fileInput = document.getElementById('restore-file-input');
    const statusEl = document.getElementById('backup-status');
    if (!restoreBtn || !fileInput) return;

    restoreBtn.addEventListener('click', () => {
        fileInput.click();
    });

    fileInput.addEventListener('change', async () => {
        if (fileInput.files.length === 0) return;

        const file = fileInput.files[0];
        if (!file.name.endsWith('.zip')) {
            window.showToast("Selecione um arquivo .zip válido", "error");
            fileInput.value = '';
            return;
        }

        if (!confirm("Tem certeza que deseja restaurar este backup?\n\nTodos os dados atuais (produtos, clientes, pedidos, configurações) serão substituídos.")) {
            fileInput.value = '';
            return;
        }

        restoreBtn.disabled = true;
        restoreBtn.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> Restaurando...";
        if (statusEl) {
            statusEl.style.display = 'block';
            statusEl.style.color = 'var(--clr-text-light)';
            statusEl.textContent = 'Restaurando backup, aguarde...';
        }

        try {
            const formData = new FormData();
            formData.append('backup', file);

            const response = await fetch('api/restore.php', {
                method: 'POST',
                body: formData,
                credentials: 'include'
            });

            const result = await response.json().catch(() => ({ status: 'error', message: 'Resposta inválida do servidor' }));

            if (result.status === 'success') {
                if (statusEl) {
                    statusEl.style.color = '#10B981';
                    statusEl.textContent = result.message;
                }
                window.ProductManager.clearCache();
                window.showToast("Backup restaurado com sucesso!", "success");
            } else {
                throw new Error(result.message || 'Erro ao restaurar backup');
            }
        } catch (err) {
            if (statusEl) {
                statusEl.style.color = '#EF4444';
                statusEl.textContent = err.message;
            }
            window.showToast(err.message, "error");
        }

        restoreBtn.disabled = false;
        restoreBtn.innerHTML = "<i class='bx bx-upload'></i> Restaurar Backup";
        fileInput.value = '';
    });
}
