function initProfile() {
    console.log("Iniciando Perfil...");
    
    // Auth Check (Redundante mas seguro)
    fetch('api/auth.php?action=check')
        .then(r => r.json())
        .then(data => {
            if (!data.loggedIn) {
                window.location.href = 'login.html';
                return;
            }
            
            // Setup inicial
            const welcomeName = document.getElementById('prof-welcome-name');
            if (welcomeName) welcomeName.innerText = data.name;
            
            const profNome = document.getElementById('prof-nome');
            if (profNome) profNome.value = data.name;
            
            if (document.getElementById('sec-email')) document.getElementById('sec-email').value = data.email || '';
            if (document.getElementById('sec-cpf') && data.cpf) document.getElementById('sec-cpf').value = data.cpf;
            if (document.getElementById('sec-telefone') && data.telefone) document.getElementById('sec-telefone').value = data.telefone;
            
            const picPreview = document.getElementById('prof-pic-preview');
            if (picPreview) {
                if (data.profile_picture) {
                    let pic = data.profile_picture;
                    if (!pic.startsWith('http') && !pic.startsWith('api/') && pic.startsWith('uploads/')) {
                        pic = 'api/uploads.php?file=' + pic.replace('uploads/', '');
                    }
                    picPreview.src = pic;
                } else {
                    picPreview.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(data.name)}&background=random`;
                }
            }
            
            if (data.role === 'admin') {
                const adminBtn = document.getElementById('admin-btn');
                if (adminBtn) adminBtn.style.display = 'inline-flex';
            }

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

    // Toggle Security
    const toggleSecurityBtn = document.getElementById('btn-toggle-security');
    const securityContent = document.getElementById('security-content');
    if (toggleSecurityBtn && securityContent) {
        toggleSecurityBtn.addEventListener('click', () => {
            securityContent.style.display = (securityContent.style.display === 'none' || securityContent.style.display === '') ? 'block' : 'none';
        });
    }

    // Profile Pic Logic
    const picInput = document.getElementById('prof-pic-input');
    const picPreview = document.getElementById('prof-pic-preview');
    const btnEditPic = document.getElementById('btn-edit-pic');
    const btnDeletePic = document.getElementById('btn-delete-pic');
    
    window.pendingProfilePic = null;
    window.deleteProfilePic = false;

    if (btnEditPic && picInput) btnEditPic.onclick = () => picInput.click();
    if (btnDeletePic && picPreview) {
        btnDeletePic.onclick = () => {
            const userName = document.getElementById('prof-nome') ? document.getElementById('prof-nome').value : 'User';
            picPreview.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(userName)}&background=random`;
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
                        const MAX = 300;
                        let w = img.width, h = img.height;
                        if (w > h) { if (w > MAX) { h *= MAX/w; w = MAX; } }
                        else { if (h > MAX) { w *= MAX/h; h = MAX; } }
                        canvas.width = w; canvas.height = h;
                        ctx.drawImage(img, 0, 0, w, h);
                        const url = canvas.toDataURL('image/jpeg', 0.8);
                        picPreview.src = url;
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
                const r = await fetch('api/auth.php?action=update_profile', { method: 'POST', body: fd });
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
                const r = await fetch('api/auth.php?action=update_security', { method: 'POST', body: fd });
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
            const r = await fetch('api/auth.php?action=logout');
            const data = await r.json();
            if (data.status === 'success') window.location.href = 'index.php';
        });
    }

    async function loadUserOrders(userId) {
        const list = document.getElementById('profile-orders-list');
        if (!list) return;
        try {
            const userOrders = await window.OrderManager.getByUser(userId);
            if (!userOrders || userOrders.length === 0) {
                list.innerHTML = `<p style="text-align:center; padding:2rem; color:var(--clr-text-light);">Nenhum pedido realizado.</p>`;
                return;
            }
            list.innerHTML = userOrders.map(o => {
                const date = new Date(o.created_at).toLocaleDateString('pt-BR');
                const total = window.formatCurrency(o.total);
                const isEntregue = o.status === 'entregue' || o.status === 'concluido';
                const statusColor = isEntregue ? '#10B981' : '#F59E0B';
                const deleteBtn = isEntregue ? `<button onclick="window.deleteUserOrder(${o.id}, ${userId})" style="background:#EF4444; color:white; border:none; border-radius:var(--radius-md); width:32px; height:32px; cursor:pointer; display:flex; align-items:center; justify-content:center; align-self: flex-end;" title="Excluir Pedido"><i class='bx bx-trash'></i></button>` : '';
                return `<div style="display:flex; justify-content: space-between; gap:1rem; padding:1.25rem 0; border-bottom:1px solid var(--clr-border);">
                    <div style="display:flex; flex-direction:column; gap:0.25rem;">
                        <div style="font-weight:600; font-size:0.95rem;">Pedido #${o.id} - ${date}</div>
                        <div style="font-size:0.8rem; color:${statusColor}; font-weight:700; text-transform:uppercase;">${o.status}</div>
                    </div>
                    <div style="display:flex; flex-direction:column; align-items:flex-end; justify-content:space-between; gap:0.5rem;">
                        <div style="font-weight:700; color:var(--clr-primary); font-size:1.1rem;">${total}</div>
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
            const promos = products.filter(p => (p.category || "").toLowerCase().includes('promo'));
            if (promos.length === 0) { list.innerHTML = "Sem promoções no momento."; return; }
            list.innerHTML = promos.slice(0, 6).map(p => `
                <a href="detalhes.html?id=${p.id}" class="promo-mini-card">
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
