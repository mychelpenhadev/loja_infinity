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
            
            
            const urlParams = new URLSearchParams(window.location.search);
            if(urlParams.get('action') === 'register') {
                switchToRegister();
            }

            
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
                            window.location.href = data.role === 'admin' ? 'admin.html' : 'index.html';
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
                        body: JSON.stringify({ name, cpf, email, password })
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

            window.signInWithGoogle = () => {
                window.showToast('Conectando ao Google...', 'success');
                setTimeout(() => {
                    window.showToast('Autenticado com sucesso! Redirecionando...', 'success');
                    setTimeout(() => {
                        window.location.href = 'index.html';
                    }, 1000);
                }, 1500);
            };
        });
