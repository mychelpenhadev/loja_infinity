<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Infinity Variedades') }}</title>
    <script>
        (function() {
            window.APP_URL = "{{ url('/') }}";
        })();
    </script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ time() }}">
    <link rel="preload" href="{{ asset('assets/img/logoPNG.png') }}" as="image">
    @stack('styles')
</head>
<body>
    <!-- Premium Infinity Loader -->
    <div id="infinity-loader">
        <div class="loader-content">
            <div class="loader-graphic-area">
                <div class="loader-ring"></div>
                <div class="loader-ring-inner"></div>
                <div class="loader-logo-container">
                    <img src="{{ asset('assets/img/logoPNG.png') }}" alt="Infinity" class="loader-logo-img">
                </div>
            </div>
            <div class="loader-text">Carregando</div>
        </div>
    </div>

    <header class="header">
        <div class="container nav" style="position: relative;">
            <!-- Logo principal na esquerda -->
            <a href="{{ url('/') }}" class="nav-brand nav-brand-left" style="
                display: flex;
                align-items: center;
                justify-content: center;
                background: rgba(255,255,255,0.06);
                border: 1px solid rgba(255,255,255,0.12);
                border-radius: 50px;
                padding: 5px 18px 5px 5px;
                gap: 10px;
                backdrop-filter: blur(8px);
                transition: all 0.3s ease;
                text-decoration: none;
            " onmouseover="this.style.background='rgba(0,212,212,0.08)'; this.style.borderColor='rgba(0,212,212,0.25)';"
               onmouseout="this.style.background='rgba(255,255,255,0.06)'; this.style.borderColor='rgba(255,255,255,0.12)';">
                <span style="
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: #fff;
                    border-radius: 40px;
                    width: 42px;
                    height: 42px;
                    overflow: hidden;
                    flex-shrink: 0;
                ">
                    <img src="{{ asset('assets/img/logoPNG.png') }}" alt="Infinity Variedades"
                         style="width: 38px; height: 38px; object-fit: contain; border-radius: 36px;">
                </span>
                <span style="
                    font-family: var(--font-display);
                    font-size: 1rem;
                    font-weight: 700;
                    color: var(--clr-text);
                    white-space: nowrap;
                    letter-spacing: 0.01em;
                ">Infinity Variedades</span>
            </a>
            
            <div class="header-search-container">
                <form action="{{ url('/produtos') }}" method="GET" class="header-search-form" id="header-search-form">
                    <i class='bx bx-search'></i>
                    <input type="text" name="q" id="header-search-input" placeholder="O que você está procurando hoje?" autocomplete="off">
                </form>
                <div class="search-suggestions" id="header-search-suggestions"></div>
            </div>

            <div class="nav-actions">
                @if(auth()->check())
                    <a href="{{ url('/perfil') }}" class="action-btn" title="Meu Perfil">
                        <i class='bx bx-user-circle'></i>
                    </a>
                @else
                    <div class="action-btn" id="auth-modal-target-btn" title="Login" style="cursor: pointer;">
                        <i class='bx bx-user-circle'></i>
                    </div>
                @endif
                
                @if(auth()->check() && auth()->user()->role === 'admin')
                <a href="{{ url('/admin') }}" class="action-btn" title="Painel Admin" style="background: var(--clr-primary); color: #000;">
                    <i class='bx bx-cog'></i>
                </a>
                @endif

                <div class="notification-wrapper" id="notif-wrapper" style="position: relative; display: flex; align-items: center; justify-content: center; width: 40px; height: 40px;">
                    <i class='bx bx-bell' id="notification-btn" title="Notificações" style="font-size: 1.6rem; cursor: pointer; color: var(--clr-accent); transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'"></i>
                    <span class="notification-badge" id="notification-badge" style="position: absolute; top: 2px; right: 2px; background: #ff4b2b; color: #fff; width: 18px; height: 18px; border-radius: 50%; display: none; align-items: center; justify-content: center; font-size: 10px; font-weight: 800; border: 2px solid var(--clr-surface); pointer-events: none; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">0</span>
                    <div class="notification-dropdown" id="notification-dropdown">
                        <div class="notification-header">
                            <h4>Notificações</h4>
                            <span class="mark-read-btn" id="mark-read-btn">Limpar</span>
                        </div>
                        <div class="notification-list" id="notification-list"></div>
                        <a href="{{ url('/produtos') }}" class="view-all-notif">Ver Novidades</a>
                    </div>
                </div>

                <a href="{{ url('/carrinho') }}" class="action-btn" id="cart-btn-nav" title="Carrinho" style="background: var(--clr-accent); color: #fff;">
                    <i class='bx bx-shopping-bag'></i>
                    <span class="cart-badge" id="cart-badge">0</span>
                </a>
            </div>
        </div>
    </header>

    <a href="https://wa.me/5511999999999" target="_blank" class="whatsapp-fab" title="Fale Conosco no WhatsApp">
        <i class='bx bxl-whatsapp'></i>
    </a>

    <main>
        <div class="fade-in">
            @yield('content')
        </div>
    </main>

    <footer class="footer bg-dark-premium" style="padding: 2rem 0 1.5rem; border-top: 1px solid rgba(255,255,255,0.1);">
        <div class="container text-center">
            <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 4rem; margin-bottom: 2rem;">
                <div class="footer-col" style="text-align: center;">
                    <h3 style="font-family: var(--font-display); font-size: 1.1rem; margin-bottom: 1rem;">Links Úteis</h3>
                    <div class="footer-links" style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
                        <a href="{{ url('/produtos') }}">Todos os Produtos</a>
                        <a href="{{ url('/perfil') }}">Minha Conta</a>
                    </div>
                </div>
                <div class="footer-col" style="text-align: center;">
                    <h3 style="font-family: var(--font-display); font-size: 1.1rem; margin-bottom: 1rem;">Siga-nos</h3>
                    <div class="social-links" style="display: flex; justify-content: center;">
                        <a href="https://instagram.com/infinityvariedades_" target="_blank" class="social-link" style="font-size: 2rem; color: var(--clr-accent);">
                            <i class='bx bxl-instagram-alt'></i>
                        </a>
                    </div>
                </div>
                <div class="footer-col" style="text-align: center;">
                    <h3 style="font-family: var(--font-display); font-size: 1.1rem; margin-bottom: 1rem;">Onde Estamos</h3>
                    <div class="footer-links" style="display: flex; justify-content: center;">
                        <a href="https://www.google.com/maps?q=-3.3217251,-45.0119846" target="_blank" style="display: flex; align-items: center; gap: 0.5rem;">
                            <i class='bx bx-map-pin' style="color: var(--clr-accent);"></i> Ver localização
                        </a>
                    </div>
                </div>
            </div>
            <div style="display: flex; flex-direction: column; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.1);">
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.15em; color: var(--clr-text-light);">Formas de Pagamento</span>
                <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; justify-content: center;">
                    <!-- PIX -->
                    <div title="Pix" style="background: #fff; border-radius: 8px; padding: 6px 12px; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 0L6.5 5.5L9.5 8.5L12 6L14.5 8.5L17.5 5.5L12 0Z" fill="#32BCAD"/>
                            <path d="M24 12L18.5 6.5L15.5 9.5L18 12L15.5 14.5L18.5 17.5L24 12Z" fill="#32BCAD"/>
                            <path d="M12 24L17.5 18.5L14.5 15.5L12 18L9.5 15.5L6.5 18.5L12 24Z" fill="#32BCAD"/>
                            <path d="M0 12L5.5 17.5L8.5 14.5L6 12L8.5 9.5L5.5 6.5L0 12Z" fill="#32BCAD"/>
                            <path d="M9.5 9.5L12 7L14.5 9.5L12 12L9.5 9.5Z" fill="#32BCAD"/>
                            <path d="M14.5 14.5L12 17L9.5 14.5L12 12L14.5 14.5Z" fill="#32BCAD"/>
                        </svg>
                        <span style="font-size: 0.8rem; font-weight: 800; color: #32BCAD;">PIX</span>
                    </div>
                    <!-- VISA -->
                    <div title="Visa" style="background: #1a1f71; border-radius: 8px; padding: 6px 14px; display: flex; align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
                        <span style="font-size: 1rem; font-weight: 900; color: #fff; font-style: italic; letter-spacing: -0.03em; font-family: 'Georgia', serif;">VISA</span>
                    </div>
                    <!-- MASTERCARD -->
                    <div title="Mastercard" style="background: #fff; border-radius: 8px; padding: 6px 10px; display: flex; align-items: center; gap: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
                        <div style="width: 20px; height: 20px; background: #EB001B; border-radius: 50%;"></div>
                        <div style="width: 20px; height: 20px; background: #F79E1B; border-radius: 50%; margin-left: -10px; opacity: 0.85;"></div>
                        <span style="font-size: 0.65rem; font-weight: 800; color: #333; margin-left: 4px;">Master</span>
                    </div>
                </div>
            </div>
            <div class="footer-bottom" style="font-size: 0.85rem; padding-top: 1rem;">
                &copy; {{ date('Y') }} Infinity Variedades.
            </div>
        </div>
    </footer>

    <script>
        // Loader Handler
        window.addEventListener('load', function() {
            const loader = document.getElementById('infinity-loader');
            if (loader) {
                setTimeout(() => {
                    loader.classList.add('hidden');
                }, 800);
            }
        });
    </script>

    <script src="{{ asset('assets/js/core/db.js?v=' . time()) }}"></script>
    <script src="{{ asset('assets/js/core/app.js?v=' . time()) }}"></script>
    
    @if(!auth()->check())
    <!-- Auth Modal Overlay -->
    <div class="auth-modal-overlay" id="auth-modal-overlay">
        <div class="auth-modal" id="auth-modal">
            <button class="auth-modal-close" id="auth-modal-close"><i class='bx bx-x'></i></button>
            <div class="auth-modal-header">
                <h2>Entrar ou cadastrar-se</h2>
                <p>Acesse sua conta para aproveitar ofertas exclusivas e comprar mais rápido.</p>
            </div>
            
            <div id="modal-social-section">
                <div style="display: flex; justify-content: center; margin-bottom: 0.5rem; width: 100%;">
                    <div id="googleDDBtn"></div>
                </div>
                <div class="auth-modal-divider">OU</div>
            </div>

            <form id="modal-login-form">
                <div id="modal-register-name-field" style="display: none;">
                    <input type="text" id="modal-register-name" class="auth-modal-input" placeholder="Nome Completo">
                </div>
                
                <input type="email" id="modal-login-email" class="auth-modal-input" placeholder="Endereço de e-mail" required>
                <input type="password" id="modal-login-password" class="auth-modal-input" placeholder="Senha" required>
                
                <div id="modal-register-confirm-field" style="display: none;">
                    <input type="password" id="modal-register-confirm" class="auth-modal-input" placeholder="Confirmar Senha">
                </div>

                <button type="submit" class="auth-btn-continue" id="modal-submit-btn">Continuar</button>
            </form>

            <div class="auth-modal-footer">
                <button type="button" class="auth-btn-outline" id="modal-toggle-btn" onclick="window.toggleAuthMode()">Criar nova conta</button>
            </div>
        </div>
    </div>

    <script src="https://accounts.google.com/gsi/client" async defer onload="initGoogleAuth()"></script>
    <script>
        window.handleGoogleLoginGlobal = async (response) => {
            let notify = window.showToast || alert;
            notify('Autenticando com o Google...', 'success');
            try {
                const res = await fetch('{{ url("api/auth?action=google_login") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ token: response.credential })
                });
                
                const textRes = await res.text();
                
                try {
                    const data = JSON.parse(textRes);
                    if (data.status === 'success') {
                        notify('Login realizado com sucesso!', 'success');
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        notify(data.message, 'error');
                    }
                } catch(parseErr) {
                    console.error("RAW SERVER ERROR:", textRes);
                    alert("Erro interno do servidor! Abra o Console (F12) para ver os detalhes do problema.");
                }
            } catch(err) {
                notify('Erro de conexão ao Google.', 'error');
            }
        };

        function initGoogleAuth() {
            if (window.google) {
                google.accounts.id.initialize({
                    client_id: "375279591438-7uirtbvgbtsd2c2pjti9kmmhal8r2sr3.apps.googleusercontent.com",
                    callback: handleGoogleLoginGlobal,
                    context: "use"
                });
                
                const googleBtnWrapper = document.getElementById("googleDDBtn");
                if (googleBtnWrapper) {
                    google.accounts.id.renderButton(
                        googleBtnWrapper,
                        { theme: "filled_black", size: "large", type: "standard", shape: "pill", text: "continue_with", width: 376 }
                    );
                }
            }
        }

        window.isRegisterMode = false;
        window.toggleAuthMode = function() {
            window.isRegisterMode = !window.isRegisterMode;
            
            const socialSection = document.getElementById('modal-social-section');
            const nameField = document.getElementById('modal-register-name-field');
            const confirmField = document.getElementById('modal-register-confirm-field');
            const submitBtn = document.getElementById('modal-submit-btn');
            const toggleBtn = document.getElementById('modal-toggle-btn');
            const modalTitle = document.querySelector('.auth-modal-header h2');
            const modalDesc = document.querySelector('.auth-modal-header p');
            
            const nameInput = document.getElementById('modal-register-name');
            const confirmInput = document.getElementById('modal-register-confirm');
            
            if(window.isRegisterMode) {
                socialSection.style.display = 'none';
                
                nameField.style.animation = 'fadeInUp 0.3s ease-out forwards';
                confirmField.style.animation = 'fadeInUp 0.3s ease-out forwards';
                nameField.style.display = 'block';
                confirmField.style.display = 'block';
                
                submitBtn.innerText = 'Cadastrar';
                modalTitle.innerText = 'Criar nova conta';
                modalDesc.innerText = 'Preencha os dados abaixo para se juntar a nós.';
                toggleBtn.innerText = 'Fazer Login';
                
                nameInput.required = true;
                confirmInput.required = true;
            } else {
                socialSection.style.display = 'block';
                nameField.style.display = 'none';
                confirmField.style.display = 'none';
                
                submitBtn.innerText = 'Continuar';
                modalTitle.innerText = 'Entrar ou cadastrar-se';
                modalDesc.innerText = 'Acesse sua conta para aproveitar ofertas exclusivas e comprar mais rápido.';
                toggleBtn.innerText = 'Criar nova conta';
                
                nameInput.required = false;
                confirmInput.required = false;
            }
        };

        document.addEventListener('DOMContentLoaded', () => {
            const authBtn = document.getElementById('auth-modal-target-btn');
            const authOverlay = document.getElementById('auth-modal-overlay');
            const authModal = document.getElementById('auth-modal');
            const authClose = document.getElementById('auth-modal-close');

            if(authBtn && authOverlay) {
                authBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    authOverlay.classList.add('active');
                });

                authClose.addEventListener('click', () => {
                    authOverlay.classList.remove('active');
                });

                authOverlay.addEventListener('click', (e) => {
                    if (e.target === authOverlay) {
                        authOverlay.classList.remove('active');
                    }
                });
            }

            const loginForm = document.getElementById('modal-login-form');
            if(loginForm) {
                loginForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const email = document.getElementById('modal-login-email').value;
                    const password = document.getElementById('modal-login-password').value;
                    const submitBtn = document.getElementById('modal-submit-btn');
                    
                    if(window.isRegisterMode) {
                        const confirmPassword = document.getElementById('modal-register-confirm').value;
                        if(password !== confirmPassword) {
                            if(window.showToast) window.showToast('As senhas não conferem.', 'error');
                            else alert('As senhas não conferem.');
                            return;
                        }
                    }

                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Aguarde...';
                    submitBtn.disabled = true;

                    const actionUrl = window.isRegisterMode ? '{{ url("api/auth?action=register") }}' : '{{ url("api/auth?action=login") }}';
                    const payload = { email, password };
                    
                    if(window.isRegisterMode) {
                        payload.name = document.getElementById('modal-register-name').value;
                    }

                    try {
                        const res = await fetch(actionUrl, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(payload)
                        });
                        
                        const text = await res.text();
                        let data;
                        try {
                            data = JSON.parse(text);
                        } catch (e) {
                            console.error('Resposta não é JSON:', text);
                            // Se estiver em modo debug, o HTML vai aparecer no console
                            if(window.showToast) window.showToast('Erro interno no servidor (Veja o console)', 'error');
                            return;
                        }
                        
                        if (data.status === 'success') {
                            if(window.showToast) window.showToast(window.isRegisterMode ? 'Cadastro realizado com sucesso!' : 'Login realizado com sucesso!', 'success');
                            setTimeout(() => window.location.reload(), 800);
                        } else {
                            if(window.showToast) window.showToast(data.message, 'error');
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                        }
                    } catch (err) {
                        if(window.showToast) window.showToast('Erro de conexão.', 'error');
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }
                });
            }
        });
    </script>
    @endif
    
    <!-- Mobile Bottom Navigation Bar -->
    <nav class="mobile-bottom-nav">
        <a href="{{ url('/') }}" class="m-nav-item {{ request()->is('/') ? 'active' : '' }}">
            <i class='bx {{ request()->is('/') ? 'bxs-home' : 'bx-home' }}'></i>
            <span>Início</span>
        </a>
        <a href="{{ url('/produtos') }}" class="m-nav-item {{ request()->is('produtos') ? 'active' : '' }}">
            <i class='bx {{ request()->is('produtos') ? 'bxs-grid-alt' : 'bx-grid-alt' }}'></i>
            <span>Catálogo</span>
        </a>
        <a href="{{ url('/carrinho') }}" class="m-nav-item {{ request()->is('carrinho') ? 'active' : '' }}">
            <div style="position: relative;">
                <i class='bx {{ request()->is('carrinho') ? 'bxs-shopping-bag' : 'bx-shopping-bag' }}'></i>
                <span class="cart-badge m-nav-badge" id="m-nav-cart-badge">0</span>
            </div>
            <span>Carrinho</span>
        </a>
        @if(auth()->check())
            <a href="{{ url('/perfil') }}" class="m-nav-item {{ request()->is('perfil') ? 'active' : '' }}">
                <i class='bx {{ request()->is('perfil') ? 'bxs-user' : 'bx-user' }}'></i>
                <span>Perfil</span>
            </a>
        @else
            <a href="javascript:void(0)" class="m-nav-item" onclick="document.getElementById('auth-modal-overlay') && document.getElementById('auth-modal-overlay').classList.add('active')">
                <i class='bx bx-user-circle'></i>
                <span>Entrar</span>
            </a>
        @endif
    </nav>

    @stack('scripts')
</body>
</html>
