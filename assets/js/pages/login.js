document.addEventListener('DOMContentLoaded', () => {
            const loginView = document.getElementById('login-view');
            const registerView = document.getElementById('register-view');
            const btnShowRegister = document.getElementById('show-register');
            const btnShowLogin = document.getElementById('show-login');
            
            const tabLogin = document.getElementById('tab-login');
            const tabRegister = document.getElementById('tab-register');
            
            const loginForm = document.getElementById('login-form');
            const registerForm = document.getElementById('register-form');

            
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

            
            const switchToRegister = () => {
                loginView.style.display = 'none';
                registerView.style.display = 'block';
                tabLogin.classList.remove('active');
                tabRegister.classList.add('active');
                
                
                window.history.replaceState(null, '', '?action=register');
            };

            const switchToLogin = () => {
                registerView.style.display = 'none';
                loginView.style.display = 'block';
                tabRegister.classList.remove('active');
                tabLogin.classList.add('active');
                
                window.history.replaceState(null, '', '?action=login');
            };

            
            tabRegister.addEventListener('click', switchToRegister);
            tabLogin.addEventListener('click', switchToLogin);

            btnShowRegister.addEventListener('click', (e) => {
                e.preventDefault();
                switchToRegister();
            });

            btnShowLogin.addEventListener('click', (e) => {
                e.preventDefault();
                switchToLogin();
            });
            
            fetch('api/auth.php?action=check')
                .then(r => r.json())
                .then(data => {
                    if(data.loggedIn) {
                        document.querySelector('.auth-card').style.maxWidth = '900px';
                        document.querySelector('.auth-tabs').style.display = 'none';
                        loginView.style.display = 'none';
                        registerView.style.display = 'none';
                        const profileView = document.getElementById('profile-view');
                            if(profileView) {
                            profileView.style.display = 'block';
                            document.getElementById('prof-nome').value = data.name;
                            if(document.getElementById('prof-cpf') && data.cpf) document.getElementById('prof-cpf').value = data.cpf;
                            if(document.getElementById('prof-telefone') && data.telefone) document.getElementById('prof-telefone').value = data.telefone;
                            
                            const welcomeName = document.getElementById('prof-welcome-name');
                            if(welcomeName) welcomeName.innerText = data.name;
                            
                            const picPreview = document.getElementById('prof-pic-preview');
                            if(picPreview) {
                                if(data.profile_picture) {
                                    picPreview.src = data.profile_picture;
                                } else {
                                    picPreview.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(data.name)}&background=random`;
                                }
                            }
                            
                            loadUserOrders(data.id);
                            loadMiniPromos();
                            setupWhatsAppSupport();
                        }
                    } else {
                        const urlParams = new URLSearchParams(window.location.search);
                        if(urlParams.get('action') === 'register') {
                            switchToRegister();
                        }
                    }
                });

            
            loginForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const email = document.getElementById('login-email').value;
                const password = document.getElementById('login-password').value;

                try {
                    const res = await fetch('api/auth.php?action=login', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ email, password })
                    });
                    const data = await res.json();
                    if (data.status === 'success') {
                        window.showToast(data.message, 'success');
                        setTimeout(() => {
                            window.location.href = data.role === 'admin' ? 'admin.php' : 'index.html';
                        }, 1000);
                    } else {
                        window.showToast(data.message, 'error');
                    }
                } catch (err) {
                    window.showToast('Erro ao se conectar ao servidor.', 'error');
                }
            });

            
            const isCPFValid = (cpf) => {
                cpf = cpf.replace(/[^\d]+/g,'');
                if(cpf == '') return false;
                if (cpf.length != 11 || 
                    cpf == "00000000000" || cpf == "11111111111" || 
                    cpf == "22222222222" || cpf == "33333333333" || 
                    cpf == "44444444444" || cpf == "55555555555" || 
                    cpf == "66666666666" || cpf == "77777777777" || 
                    cpf == "88888888888" || cpf == "99999999999")
                        return false;
                let add = 0;
                for (let i=0; i < 9; i ++) add += parseInt(cpf.charAt(i)) * (10 - i);
                let rev = 11 - (add % 11);
                if (rev == 10 || rev == 11) rev = 0;
                if (rev != parseInt(cpf.charAt(9))) return false;
                add = 0;
                for (let i = 0; i < 10; i ++) add += parseInt(cpf.charAt(i)) * (11 - i);
                rev = 11 - (add % 11);
                if (rev == 10 || rev == 11) rev = 0;
                if (rev != parseInt(cpf.charAt(10))) return false;
                return true;
            };

            registerForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const name = document.getElementById('reg-name').value;
                const cpf = document.getElementById('reg-cpf').value;
                const telefone = document.getElementById('reg-telefone').value;
                const email = document.getElementById('reg-email').value;
                const password = document.getElementById('reg-password').value;
                const confirmPassword = document.getElementById('reg-password-confirm').value;
                
                
                if (!isCPFValid(cpf)) {
                    window.showToast('Por favor, informe um CPF válido.', 'error');
                    return;
                }
                
                if (!email.toLowerCase().endsWith('@gmail.com')) {
                    window.showToast('O e-mail precisa terminar com @gmail.com', 'error');
                    return;
                }
                
                const passRegex = /^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
                if (!passRegex.test(password)) {
                    window.showToast('A senha não atende aos requisitos de segurança.', 'error');
                    return;
                }
                
                if (password !== confirmPassword) {
                    window.showToast('As senhas não coincidem.', 'error');
                    return;
                }

                try {
                    const res = await fetch('api/auth.php?action=register', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ name, cpf, telefone, email, password })
                    });
                    const data = await res.json();
                    if (data.status === 'success') {
                        window.showToast(data.message, 'success');
                        setTimeout(() => {
                            window.location.href = 'index.html';
                        }, 1500);
                    } else {
                        window.showToast(data.message, 'error');
                    }
                } catch (err) {
                    window.showToast('Erro de conexão.', 'error');
                }
            });

            window.handleGoogleLogin = async (response) => {
                window.showToast('Autenticando com o Google...', 'success');
                try {
                    const res = await fetch('api/auth.php?action=google_login', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ token: response.credential })
                    });
                    const data = await res.json();
                    if (data.status === 'success') {
                        window.showToast(data.message, 'success');
                        setTimeout(() => {
                            window.location.href = data.role === 'admin' ? 'admin.php' : 'index.html';
                        }, 1000);
                    } else {
                        window.showToast(data.message, 'error');
                    }
                } catch(err) {
                    window.showToast('Erro de conexão ao Google.', 'error');
                }
            };

            window.pendingProfilePic = null;
            window.deleteProfilePic = false;
            const picInput = document.getElementById('prof-pic-input');
            const picPreview = document.getElementById('prof-pic-preview');
            const btnEditPic = document.getElementById('btn-edit-pic');
            const btnDeletePic = document.getElementById('btn-delete-pic');

            if(btnEditPic && picInput) {
                btnEditPic.onclick = () => picInput.click();
            }

            if(btnDeletePic && picPreview) {
                btnDeletePic.onclick = () => {
                    const userName = document.getElementById('prof-nome').value || 'User';
                    picPreview.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(userName)}&background=random`;
                    window.pendingProfilePic = null;
                    window.deleteProfilePic = true;
                    if(picInput) picInput.value = ''; // Clear file input
                    window.showToast("Foto removida. Salve o perfil para confirmar.", "success");
                };
            }

            if(picInput) {
                picInput.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        const file = this.files[0];
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const img = new Image();
                            img.onload = function() {
                                const canvas = document.createElement('canvas');
                                const ctx = canvas.getContext('2d');
                                const MAX_WIDTH = 300;
                                const MAX_HEIGHT = 300;
                                let width = img.width;
                                let height = img.height;
                                
                                if (width > height) {
                                  if (width > MAX_WIDTH) {
                                    height *= MAX_WIDTH / width;
                                    width = MAX_WIDTH;
                                  }
                                } else {
                                  if (height > MAX_HEIGHT) {
                                    width *= MAX_HEIGHT / height;
                                    height = MAX_HEIGHT;
                                  }
                                }
                                canvas.width = width;
                                canvas.height = height;
                                ctx.drawImage(img, 0, 0, width, height);
                                
                                const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
                                picPreview.src = dataUrl;
                                window.pendingProfilePic = dataUrl;
                                window.deleteProfilePic = false; // Reset delete flag if new pic chosen
                            };
                            img.src = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }

            const profileForm = document.getElementById('profile-form');
            if(profileForm) {
                profileForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const name = document.getElementById('prof-nome').value.trim();
                    const cpf = document.getElementById('prof-cpf').value.trim();
                    const telefone = document.getElementById('prof-telefone').value.trim();
                    const passVal = document.getElementById('prof-senha').value;
                    
                    if(passVal) {
                        const passRegex = /^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
                        if(!passRegex.test(passVal)) {
                            window.showToast("A senha nova deve ter no mínimo 8 caracteres, 1 maiúscula, 1 número e 1 especial.", "error");
                            return;
                        }
                    }
                    
                    const btn = e.target.querySelector('button[type="submit"]');
                    const originalText = btn.innerHTML;
                    btn.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> Salvando...";
                    btn.disabled = true;

                    const fd = new FormData();
                    fd.append('name', name);
                    fd.append('cpf', cpf);
                    fd.append('telefone', telefone);
                    fd.append('password', passVal);
                    if(window.pendingProfilePic) {
                        fd.append('profile_picture', window.pendingProfilePic);
                    }
                    if(window.deleteProfilePic) {
                        fd.append('delete_photo', '1');
                    }

                    try {
                        const res = await fetch('api/auth.php?action=update_profile', { method: 'POST', body: fd });
                        const json = await res.json();
                        if (json.status === 'success') {
                            window.showToast(json.message, 'success');
                            setTimeout(() => window.location.href = 'index.html', 1000);
                        } else {
                            window.showToast(json.message, 'error');
                        }
                    } catch(err) {
                        window.showToast("Erro de rede ao salvar perfil.", 'error');
                    } finally {
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }
                });
            }

            const btnLogout = document.getElementById('btn-logout');
            if(btnLogout) {
                btnLogout.addEventListener('click', async () => {
                    if(confirm("Tem certeza que deseja sair de sua conta?")) {
                        await fetch('api/auth.php?action=logout');
                        window.location.href = 'login.html';
                    }
                });
            }

            async function loadUserOrders(userId) {
                const list = document.getElementById('profile-orders-list');
                if(!list) return;
                try {
                    const userOrders = await window.OrderManager.getAll();
                    const filtered = userOrders.filter(o => String(o.user_id) === String(userId));
                    
                    if(filtered.length === 0) {
                        list.innerHTML = `<p style="color: var(--clr-text-light); font-size: 0.9rem; text-align: center; padding: 2rem 0;">Você ainda não tem nenhum pedido realizado.</p>`;
                        return;
                    }
                    
                    filtered.sort((a,b) => new Date(b.created_at) - new Date(a.created_at));
                    
                    list.innerHTML = filtered.map(o => {
                        const dateObj = new Date(o.created_at);
                        const dateStr = dateObj.toLocaleDateString('pt-BR');
                        const items = typeof o.items_json === 'string' ? JSON.parse(o.items_json) : o.items_json;
                        const itemsMsg = items.map(i => `${i.quantity}x ${i.name}`).join(', ');
                        const isEntregue = o.status === 'entregue' || o.status === 'concluido';
                        
                        return `
                            <div style="border-bottom: 1px solid var(--clr-border); padding: 1rem 0;">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                    <div>
                                        <strong style="font-size: 1rem;">Pedido #${o.id}</strong>
                                        <div style="font-size: 0.8rem; color: var(--clr-text-light);">${dateStr}</div>
                                    </div>
                                    <span style="display:inline-block; padding:0.25rem 0.5rem; border-radius:1rem; background: ${isEntregue ? 'rgba(16, 185, 129, 0.1)' : 'rgba(245, 158, 11, 0.1)'}; color: ${isEntregue ? '#10B981' : '#F59E0B'}; font-size:0.75rem; font-weight:600;">
                                        ${o.status.charAt(0).toUpperCase() + o.status.slice(1)}
                                    </span>
                                </div>
                                <div style="font-size: 0.85rem; color: var(--clr-text-light); margin-bottom: 0.5rem; line-height: 1.4;">
                                    ${itemsMsg}
                                </div>
                                <div style="font-weight: 700; color: var(--clr-primary); font-size: 0.95rem;">
                                    ${window.formatCurrency(o.total)}
                                </div>
                            </div>
                        `;
                    }).join('');
                } catch (e) {
                    console.error("Erro ao carregar pedidos:", e);
                }
            }

            async function loadMiniPromos() {
                const list = document.getElementById('profile-promos-list');
                if(!list) return;
                try {
                    const allProducts = await window.ProductManager.getAll();
                    const promoProducts = allProducts.filter(p => {
                        const cat = (p.category || "").toLowerCase();
                        return cat.includes('promo') || cat === 'promocoes';
                    });
                    
                    if(promoProducts.length === 0) {
                        list.innerHTML = `<p style="color: var(--clr-text-light); font-size: 0.85rem; padding: 0.5rem 0;">Nenhuma promoção ativa no momento.</p>`;
                        return;
                    }
                    
                    list.innerHTML = promoProducts.slice(0, 6).map(p => `
                        <a href="detalhes.html?id=${p.id}" class="promo-mini-card">
                            <img src="${p.image}" alt="${p.name}">
                            <h4 title="${p.name}">${p.name}</h4>
                            <span>${window.formatCurrency(p.price)}</span>
                        </a>
                    `).join('');
                } catch (e) {
                    console.error("Erro ao carregar promoções:", e);
                }
            }

            function setupWhatsAppSupport() {
                const btn = document.getElementById('profile-wa-btn');
                if(btn) {
                    let waNum = window.ConfigManager.get('whatsappNumber') || '+5598985269184';
                    waNum = waNum.replace(/\D/g, ''); 
                    if (waNum && !waNum.startsWith('55') && waNum.length <= 11) {
                        waNum = '55' + waNum;
                    }
                    btn.href = `https://wa.me/${waNum}?text=Ol%C3%A1!%20Me%20chamo%20${encodeURIComponent(window.userName || '')}%20e%20preciso%20de%20um%20suporte%20sobre%20meu%20pedido.`;
                }
            }
        });
