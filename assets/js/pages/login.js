function initLogin() {
    const loginView = document.getElementById('login-view');
    const registerView = document.getElementById('register-view');
    const btnShowRegister = document.getElementById('show-register');
    const btnShowLogin = document.getElementById('show-login');
    const tabLogin = document.getElementById('tab-login');
    const tabRegister = document.getElementById('tab-register');
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');

    if (!loginView || !registerView) return;

    // Check if already logged in -> redirect to perfil.php
    fetch('api/auth.php?action=check')
        .then(r => r.json())
        .then(data => {
            if (data.loggedIn) {
                window.location.href = 'perfil.php';
            }
        });

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

    if (tabRegister) tabRegister.addEventListener('click', switchToRegister);
    if (tabLogin) tabLogin.addEventListener('click', switchToLogin);
    if (btnShowRegister) btnShowRegister.addEventListener('click', (e) => { e.preventDefault(); switchToRegister(); });
    if (btnShowLogin) btnShowLogin.addEventListener('click', (e) => { e.preventDefault(); switchToLogin(); });

    if (loginForm) {
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
                        window.location.href = data.role === 'admin' ? 'admin.php' : 'perfil.php';
                    }, 1000);
                } else if (data.require_verification) {
                    window.showToast(data.message, 'warning');
                    setTimeout(() => {
                        window.location.href = `verificar.html?email=${encodeURIComponent(data.email)}`;
                    }, 1500);
                } else {
                    window.showToast(data.message, 'error');
                }
            } catch (err) {
                window.showToast('Erro ao se conectar ao servidor.', 'error');
            }
        });
    }

    const isCPFValid = (cpf) => {
        cpf = cpf.replace(/[^\d]+/g,'');
        if (cpf == '' || cpf.length != 11) return false;
        if (/^(\d)\1{10}$/.test(cpf)) return false;
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

    if (registerForm) {
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
                        window.location.href = 'perfil.php';
                    }, 1500);
                } else {
                    window.showToast(data.message, 'error');
                }
            } catch (err) {
                window.showToast('Erro de conexão.', 'error');
            }
        });
    }

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('action') === 'register') {
        switchToRegister();
    }
    if (urlParams.has('error')) {
        const error = urlParams.get('error');
        let msg = 'Ocorreu um erro na autenticação.';
        if (error === 'no_token') msg = 'Token do Google não recebido.';
        else if (error === 'invalid_token') msg = 'Token do Google inválido.';
        else msg = decodeURIComponent(error);
        if (window.showToast) window.showToast(msg, 'error');
        else alert(msg);
        window.history.replaceState(null, '', window.location.pathname);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLogin);
} else {
    initLogin();
}
