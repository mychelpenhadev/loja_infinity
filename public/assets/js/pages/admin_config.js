async function initConfig() {
    console.log("[Admin] Iniciando Configurações...");
    try {
        const response = await fetch('api/auth?action=check');
        const data = await response.json();
        if (!data.loggedIn || data.role !== 'admin') {
            window.location.href = '/';
            return;
        }
    } catch(err) {
        window.location.href = '/';
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
}

document.addEventListener('DOMContentLoaded', initConfig);
if (document.readyState !== 'loading') initConfig();

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

                    } else {
                        window.showToast(result.message || 'Erro no upload', 'error');
                    }
                } catch (err) {
                    console.error('Upload error:', err);
                    window.showToast('Erro ao fazer upload da imagem. Verifique o tamanho e formato.', 'error');
                }
            } else if (existingUrl) {
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
            const response = await fetch('api/backup', {
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

                const resp = await fetch('api/upload-chunk', {
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

            const finalResp = await fetch('api/restore-final', {
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
