function initProfile() {
    console.log("Iniciando Perfil Premium...");
    
    // Auth Check
    fetch('api/auth?action=check&_t=' + new Date().getTime(), { cache: 'no-store' })
        .then(r => r.json())
        .then(data => {
            if (!data.loggedIn) {
                window.location.href = (window.APP_URL || '') + '/login';
                return;
            }
            
            // Setup inicial
            const welcomeNames = document.querySelectorAll('#prof-welcome-name');
            welcomeNames.forEach(el => el.innerText = data.name);
            
            const profNome = document.getElementById('prof-nome');
            if (profNome) profNome.value = data.name;
            
            if (document.getElementById('sec-email')) document.getElementById('sec-email').value = data.email || '';
            if (document.getElementById('sec-cpf') && data.cpf) document.getElementById('sec-cpf').value = data.cpf;
            if (document.getElementById('sec-telefone') && data.telefone) document.getElementById('sec-telefone').value = data.telefone;
            
            const previews = ['prof-pic-preview', 'prof-pic-preview-large'];
            previews.forEach(id => {
                const picPreview = document.getElementById(id);
                if (picPreview) {
                    if (data.profile_picture) {
                        let pic = data.profile_picture;
                        // Keep relative path so it respects subdirectory deployments like XAMPP htdocs
                        picPreview.src = pic;
                    } else {
                        picPreview.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(data.name)}&background=random`;
                    }
                }
            });
            
            loadUserOrders(data.id);
            loadMiniPromos();
            setupWhatsAppSupport(data.name);
        });

    // Password Toggle
    window.togglePassword = (inputId, iconElement) => {
        const input = document.getElementById(inputId);
        if (input.type === 'password') {
            input.type = 'text';
            iconElement.classList.remove('bx-show');
            iconElement.classList.add('bx-hide');
        } else {
            input.type = 'password';
            iconElement.classList.remove('bx-hide');
            iconElement.classList.add('bx-show');
        }
    };

    // Tab Navigation Logic — selectors updated to match new .pf-tab / .pf-panel classes
    const tabs = document.querySelectorAll('.pf-tab');
    const sections = document.querySelectorAll('.pf-panel');
    if (tabs.length && sections.length) {
        tabs.forEach(tab => {
            tab.onclick = () => {
                const target = tab.dataset.tab;
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                sections.forEach(s => {
                    s.classList.remove('active');
                    if (s.id === `tab-${target}`) s.classList.add('active');
                });
            };
        });
    }

    // Profile Pic Logic
    const picInput = document.getElementById('prof-pic-input');
    const previews = ['prof-pic-preview', 'prof-pic-preview-large'];
    const btnEditPic = document.getElementById('btn-edit-pic');
    const btnDeletePic = document.getElementById('btn-delete-pic');
    
    window.pendingProfilePic = null;
    window.deleteProfilePic = false;

    if (btnEditPic && picInput) btnEditPic.onclick = () => picInput.click();
    if (btnDeletePic) {
        btnDeletePic.onclick = () => {
            const userName = document.getElementById('prof-nome') ? document.getElementById('prof-nome').value : 'User';
            previews.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(userName)}&background=random`;
            });
            window.pendingProfilePic = null;
            window.deleteProfilePic = true;
            if (picInput) picInput.value = '';
            window.showToast("Foto removida. Salve para confirmar.", "success");
        };
    }

    if (picInput) {
        picInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = new Image();
                    img.onload = () => {
                        const canvas = document.createElement('canvas');
                        const ctx = canvas.getContext('2d');
                        const MAX = 400;
                        let w = img.width, h = img.height;
                        if (w > h) { if (w > MAX) { h *= MAX/w; w = MAX; } }
                        else { if (h > MAX) { w *= MAX/h; h = MAX; } }
                        canvas.width = w; canvas.height = h;
                        ctx.drawImage(img, 0, 0, w, h);
                        const url = canvas.toDataURL('image/jpeg', 0.9);
                        previews.forEach(id => {
                            const el = document.getElementById(id);
                            if (el) el.src = url;
                        });
                        window.pendingProfilePic = url;
                        window.deleteProfilePic = false;
                    };
                    img.src = e.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    // Forms
    const profileForm = document.getElementById('profile-form');
    if (profileForm) {
        profileForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = e.target.querySelector('button[type="submit"]');
            const oldHtml = btn.innerHTML;
            btn.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> Salvando...";
            btn.disabled = true;
            
            const fd = new FormData();
            fd.append('name', document.getElementById('prof-nome').value.trim());
            if (window.pendingProfilePic) fd.append('profile_picture', window.pendingProfilePic);
            if (window.deleteProfilePic) fd.append('delete_photo', '1');
            
            try {
                const r = await fetch('api/auth?action=update_profile', { method: 'POST', body: fd });
                const json = await r.json();
                if (json.status === 'success') {
                    window.showToast("Perfil atualizado!", "success");
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    window.showToast(json.message, "error");
                }
            } catch(err) { window.showToast("Erro ao salvar.", "error"); }
            
            btn.innerHTML = oldHtml;
            btn.disabled = false;
        });
    }

    const securityForm = document.getElementById('security-form');
    if (securityForm) {
        securityForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = e.target.querySelector('button[type="submit"]');
            btn.disabled = true;
            
            const fd = new FormData();
            const email = document.getElementById('sec-email').value.trim();
            const cpf = document.getElementById('sec-cpf').value.trim();
            const tel = document.getElementById('sec-telefone').value.trim();
            const curr = document.getElementById('sec-senha-atual').value;
            const next = document.getElementById('sec-nova-senha').value;
            
            if (email) fd.append('email', email);
            if (cpf) fd.append('cpf', cpf);
            if (tel) fd.append('telefone', tel);
            if (curr) fd.append('current_password', curr);
            if (next) fd.append('new_password', next);
            
            try {
                const r = await fetch('api/auth?action=update_security', { method: 'POST', body: fd });
                const json = await r.json();
                if (json.status === 'success') {
                    window.showToast("Dados atualizados!", "success");
                    document.getElementById('sec-senha-atual').value = '';
                    document.getElementById('sec-nova-senha').value = '';
                } else { window.showToast(json.message, "error"); }
            } catch(e) { window.showToast("Erro ao atualizar.", "error"); }
            btn.disabled = false;
        });
    }

    const logoutBtn = document.getElementById('btn-logout');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async () => {
            const r = await fetch('api/auth?action=logout');
            const data = await r.json();
            if (data.status === 'success') window.location.href = window.APP_URL || '/';
        });
    }

    async function loadUserOrders(userId) {
        const list = document.getElementById('profile-orders-list');
        if (!list) return;
        try {
            const userOrders = await window.OrderManager.getByUser(userId);
            if (!userOrders || userOrders.length === 0) {
                list.innerHTML = `<p style="text-align:center; padding:4rem; color:var(--clr-text-light); border:1px dashed var(--profile-border); border-radius:20px;">Você ainda não realizou nenhum pedido em nossa loja.</p>`;
                return;
            }
            list.innerHTML = userOrders.map(o => {
                const date = new Date(o.created_at).toLocaleDateString('pt-BR');
                const total = window.formatCurrency(o.total);
                const isEntregue = o.status === 'entregue' || o.status === 'concluido';
                const statusClass = isEntregue ? 'delivered' : 'pending';
                const deleteBtn = isEntregue ? `<button onclick="window.deleteUserOrder(${o.id}, ${userId})" style="background:rgba(239, 68, 68, 0.1); color:#ef4444; border:1px solid rgba(239, 68, 68, 0.2); border-radius:10px; width:36px; height:36px; cursor:pointer;" title="Excluir"><i class='bx bx-trash'></i></button>` : '';
                return `
                <div class="order-card-premium">
                    <div style="display:flex; align-items:center; gap:1.5rem;">
                        <div style="text-align:center; padding-right:1.5rem; border-right:1px solid var(--profile-border);">
                            <div style="font-size:0.75rem; color:var(--clr-text-light); text-transform:uppercase; font-weight:800; margin-bottom:2px;">Pedido</div>
                            <div style="font-size:1.1rem; font-weight:900; color:var(--clr-text);">#${o.id}</div>
                        </div>
                        <div>
                            <div style="font-weight:700; font-size:1rem; margin-bottom:4px;">${date}</div>
                            <span class="status-badge ${statusClass}">${o.status}</span>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:2rem;">
                        <div style="text-align:right;">
                            <div style="font-size:0.75rem; color:var(--clr-text-light); font-weight:700;">VALOR TOTAL</div>
                            <div style="font-size:1.4rem; font-weight:900; color:var(--profile-accent);">${total}</div>
                        </div>
                        ${deleteBtn}
                    </div>
                </div>`;
            }).join('');
        } catch(e) { list.innerHTML = "Erro ao carregar pedidos."; }
    }

    window.deleteUserOrder = async (oid, uid) => {
        if (!confirm("Excluir pedido?")) return;
        const res = await window.OrderManager.deleteUserOrder(oid, uid);
        if (res.status === 'success') {
            window.showToast("Pedido excluído!", "success");
            loadUserOrders(uid);
        }
    };

    async function loadMiniPromos() {
        const list = document.getElementById('profile-promos-list');
        if (!list) return;
        try {
            const products = await window.ProductManager.getAll();
            const promos = products.filter(p => p.original_price && p.original_price > p.price);
            if (promos.length === 0) { list.innerHTML = "<p style='color:var(--clr-text-light); font-size:0.9rem;'>Descubra nossas novidades em breve!</p>"; return; }
            list.innerHTML = promos.slice(0, 6).map(p => `
                <a href="${window.APP_URL || ''}/detalhes/${p.id}" class="promo-mini-card">
                    <img src="${p.image}" alt="">
                    <h4>${p.name}</h4>
                    <span>${window.formatCurrency(p.price)}</span>
                </a>
            `).join('');
        } catch(e) {}
    }

    function setupWhatsAppSupport(name) {
        const btn = document.getElementById('profile-wa-btn');
        if (!btn) return;
        let num = window.ConfigManager.get('whatsappNumber') || '5598985269184';
        num = num.replace(/\D/g, '');
        if (num.length <= 11) num = '55' + num;
        btn.href = `https://wa.me/${num}?text=Ol%C3%A1!%20Sou%20${encodeURIComponent(name || '')}%20e%20preciso%20de%20ajuda.`;
    }
}

document.addEventListener('DOMContentLoaded', initProfile);
if (document.readyState !== 'loading') initProfile();
