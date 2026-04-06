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
    setupFloatingPromos();
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

async function setupFloatingPromos() {
    const s1 = document.getElementById('config-promo-float-1');
    const s2 = document.getElementById('config-promo-float-2');
    const form = document.getElementById('promoFloatForm');
    if (!s1 || !s2 || !form) return;

    try {
        const data = await window.ProductManager.getAll({ limit: 500 });
        let optionsHtml = '<option value="">-- Padrão (Mais recentes) --</option>';
        (data.products || []).forEach(p => {
            optionsHtml += `<option value="${p.id}">${p.name}</option>`;
        });
        s1.innerHTML = optionsHtml;
        s2.innerHTML = optionsHtml;

        const val1 = window.ConfigManager.get('promo_float_1');
        const val2 = window.ConfigManager.get('promo_float_2');
        if (val1) s1.value = val1;
        if (val2) s2.value = val2;
    } catch (err) {
        console.error(err);
        s1.innerHTML = '<option value="">Erro ao carregar</option>';
        s2.innerHTML = '<option value="">Erro ao carregar</option>';
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = form.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> Salvando...";
        
        await window.ConfigManager.set('promo_float_1', s1.value);
        await window.ConfigManager.set('promo_float_2', s2.value);
        
        btn.disabled = false;
        btn.innerHTML = "<i class='bx bx-save'></i> Salvar Flutuantes";
        window.showToast("Produtos flutuantes salvos!", "success");
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
        savedBanners.forEach(b => {
            let promo_ids = b.promo_ids || [];
            if (promo_ids.length === 0) {
                if (b.promo1_id) promo_ids.push(b.promo1_id);
                if (b.promo2_id) promo_ids.push(b.promo2_id);
            }
            addBannerItem(list, allProducts, b.url || '', b.style || 'novo', promo_ids);
        });
    } else {
        addBannerItem(list, allProducts, '', 'novo', []);
    }

    addBtn.addEventListener('click', () => addBannerItem(list, allProducts, '', 'novo', []));

    saveBtn.addEventListener('click', async () => {
        const items = list.querySelectorAll('.banner-item');
        const banners = [];
        saveBtn.disabled = true;
        saveBtn.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> Salvando...";

        for (const item of items) {
            const fileInput = item.querySelector('.banner-file-input');
            const existingUrl = item.dataset.existingUrl || '';
            let style = item.querySelector('.banner-style-select') ? item.querySelector('.banner-style-select').value : 'novo';
            let promo_ids = Array.from(item.querySelectorAll('.selected-product-item')).map(el => el.dataset.id);

            if (fileInput && fileInput.files.length > 0) {
                const formData = new FormData();
                formData.append('banner', fileInput.files[0]);
                try {
                    const resp = await fetch('api/config.php?action=upload-banner', {
                        method: 'POST',
                        body: formData,
                        credentials: 'include',
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    const result = await resp.json();
                    if (result.status === 'success') {
                        banners.push({ url: result.url, style, promo_ids });
                    } else {
                        window.showToast(result.message || 'Erro no upload', 'error');
                    }
                } catch (err) {
                    console.error('Upload error:', err);
                    window.showToast('Erro ao fazer upload da imagem. Verifique o tamanho e formato.', 'error');
                }
            } else if (existingUrl) {
                banners.push({ url: existingUrl, style, promo_ids });
            }
        }

        const result = await window.ConfigManager.set('hero_banners', JSON.stringify(banners));
        saveBtn.disabled = false;
        saveBtn.innerHTML = "<i class='bx bx-save'></i> Salvar Banners";
        if (result && result.status === 'error') {
            window.showToast(result.message || 'Erro ao salvar banners', 'error');
        } else {
            window.showToast(`${banners.length} banner(s) salvo(s) com sucesso!`, "success");
            // Reload page to refresh everything
            setTimeout(() => window.location.reload(), 1500);
        }
    });
}

function addBannerItem(list, allProducts, url, style, promo_ids) {
    const item = document.createElement('div');
    item.className = 'banner-item';
    if (url) item.dataset.existingUrl = url;

    item.innerHTML = `
        <button class="banner-remove-btn" title="Remover"><i class='bx bx-x'></i></button>
        
        <div class="banner-preview-container">
            <img class="banner-preview" src="${url || 'assets/img/placeholder-banner.png'}" style="${url ? 'display:block;' : 'display:none;'}">
        </div>

        <div class="form-group">
            <label>Imagem do Banner</label>
            <input type="file" class="premium-input banner-file-input" accept="image/*" style="padding: 0.5rem 1rem; font-size: 0.8rem;">
        </div>

        <div class="form-group">
            <label>Produtos Flutuantes (Máx 3)</label>
            <div class="banner-product-search" style="position:relative; margin-bottom: 0.5rem;">
                <input type="text" class="premium-input banner-search-input" placeholder="Pesquisar produto..." autocomplete="off" style="font-size: 0.85rem; padding: 0.6rem;">
                <div class="banner-search-results" style="display:none; position:absolute; top:100%; left:0; right:0; background:var(--clr-surface); border:1px solid var(--clr-border); border-radius:12px; max-height:200px; overflow-y:auto; z-index:100; box-shadow:var(--shadow-lg);"></div>
            </div>
            <div class="selected-products-container" style="display: flex; flex-wrap: wrap; gap: 0.5rem;"></div>
        </div>

        <div class="form-group">
            <label>Estilo dos Destaques Flutuantes no Banner</label>
            <select class="premium-input banner-style-select">
                <option value="novo" ${style === 'novo' || !style ? 'selected' : ''}>Aparência: Estilo Novo (Polaroid Branca)</option>
                <option value="antigo" ${style === 'antigo' ? 'selected' : ''}>Aparência: Estilo Antigo (Cápsula Escura)</option>
            </select>
        </div>
    `;

    const container = item.querySelector('.selected-products-container');

    function addSelectedProduct(id) {
        if (container.querySelectorAll('.selected-product-item').length >= 3) {
            window.showToast("Máximo de 3 produtos atingido.", "error");
            return;
        }
        if (container.querySelector(`.selected-product-item[data-id="${id}"]`)) return;

        const p = allProducts.find(prod => prod.id == id);
        if (!p) return;

        const pill = document.createElement('div');
        pill.className = 'selected-product-item';
        pill.dataset.id = id;
        pill.style.cssText = 'background: var(--clr-surface-alt); border: 1px solid var(--clr-border); padding: 0.4rem 0.8rem; border-radius: 20px; font-size: 0.8rem; display: flex; align-items: center; gap: 0.5rem; color: var(--clr-text);';
        pill.innerHTML = `
            <span>${p.name}</span>
            <i class='bx bx-trash' style="color: var(--clr-danger); cursor: pointer; font-size: 1rem;"></i>
        `;
        pill.querySelector('.bx-trash').addEventListener('click', () => pill.remove());
        container.appendChild(pill);
    }

    if (promo_ids && promo_ids.length > 0) {
        promo_ids.forEach(id => addSelectedProduct(id));
    }

    const inputEl = item.querySelector('.banner-search-input');
    const resultsEl = item.querySelector('.banner-search-results');

    inputEl.addEventListener('input', () => {
        const query = inputEl.value.trim().toLowerCase();
        if (query.length < 1) { resultsEl.style.display = 'none'; return; }
        const matches = allProducts.filter(p => p.name.toLowerCase().includes(query)).slice(0, 10);
        
        if (matches.length === 0) {
            resultsEl.innerHTML = '<div style="padding:0.75rem; color:var(--clr-text-light); font-size:0.85rem;">Nenhum produto encontrado</div>';
        } else {
            resultsEl.innerHTML = matches.map(p =>
                `<div class="banner-search-item" data-id="${p.id}" style="padding:0.6rem 1rem; cursor:pointer; font-size:0.85rem; border-bottom:1px solid var(--clr-border); display:flex; align-items:center; gap:0.75rem; transition: 0.2s;">
                    <img src="${p.image}" style="width:32px; height:32px; object-fit:cover; border-radius:6px;" onerror="this.src='assets/img/logoPNG.png'">
                    <span style="flex:1;">${p.name}</span>
                </div>`
            ).join('');
        }
        resultsEl.style.display = 'block';

        resultsEl.querySelectorAll('.banner-search-item').forEach(el => {
            el.addEventListener('click', () => {
                const id = el.dataset.id;
                addSelectedProduct(id);
                inputEl.value = '';
                resultsEl.style.display = 'none';
            });
        });
    });

    inputEl.addEventListener('focus', () => {
        if (inputEl.value.trim().length >= 1) inputEl.dispatchEvent(new Event('input'));
    });

    inputEl.addEventListener('blur', () => {
        setTimeout(() => { resultsEl.style.display = 'none'; }, 200);
    });

    item.querySelector('.banner-remove-btn').addEventListener('click', () => item.remove());

    const fileInput = item.querySelector('.banner-file-input');
    fileInput.addEventListener('change', () => {
        if (fileInput.files.length > 0) {
            const reader = new FileReader();
            reader.onload = (e) => {
                const preview = item.querySelector('.banner-preview');
                if (preview) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
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
        const isZip = file.name.endsWith('.zip');
        const isSql = file.name.endsWith('.sql');
        if (!isZip && !isSql) {
            window.showToast("Selecione um arquivo .zip ou .sql válido", "error");
            fileInput.value = '';
            return;
        }

        if (!confirm("Tem certeza que deseja restaurar este backup?\n\nTodos os dados atuais serão substituídos.")) {
            fileInput.value = '';
            return;
        }

        restoreBtn.disabled = true;
        const originalHTML = restoreBtn.innerHTML;
        
        if (statusEl) {
            statusEl.style.display = 'block';
            statusEl.style.color = 'var(--clr-text-light)';
            statusEl.textContent = 'Iniciando upload fragmentado...';
        }

        try {
            const chunkSize = 1024 * 1024; // 1MB chunks
            const totalChunks = Math.ceil(file.size / chunkSize);
            const identifier = 'backup_' + Date.now() + '_' + Math.floor(Math.random() * 1000);

            for (let i = 0; i < totalChunks; i++) {
                const start = i * chunkSize;
                const end = Math.min(file.size, start + chunkSize);
                const chunk = file.slice(start, end);

                const formData = new FormData();
                formData.append('chunk', chunk);
                formData.append('index', i);
                formData.append('identifier', identifier);

                if (statusEl) {
                    statusEl.textContent = `Enviando parte ${i + 1} de ${totalChunks}... (${Math.round((i / totalChunks) * 100)}%)`;
                }

                const resp = await fetch('api/upload-chunk.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'include'
                });

                if (!resp.ok) {
                    throw new Error(`Falha no upload da parte ${i + 1}`);
                }
            }

            if (statusEl) {
                statusEl.textContent = 'Processando restauração no servidor... Isso pode levar um minuto.';
            }

            const finalResp = await fetch('api/restore-final.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    identifier: identifier,
                    total: totalChunks,
                    filename: file.name
                }),
                credentials: 'include'
            });

            const result = await finalResp.json();

            if (result.status === 'success') {
                if (statusEl) {
                    statusEl.style.color = '#10B981';
                    statusEl.textContent = result.message;
                }
                window.showToast("Backup restaurado com sucesso!", "success");
            } else {
                throw new Error(result.message || 'Erro ao processar restauração final');
            }

        } catch (err) {
            console.error(err);
            if (statusEl) {
                statusEl.style.color = '#EF4444';
                statusEl.textContent = 'Erro: ' + err.message;
            }
            window.showToast(err.message, "error");
        }

        restoreBtn.disabled = false;
        restoreBtn.innerHTML = originalHTML;
        fileInput.value = '';
    });
}
