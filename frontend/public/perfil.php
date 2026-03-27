<?php
require_once '../../backend/api/security.php';
if (!isLoggedIn()) {
    header('Location: login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil | Infinity Variedades</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="assets/css/style.css?v=29">
    <style>
        .profile-container {
            min-height: calc(100vh - 80px - 200px);
            padding: 4rem 1.5rem;
            background-color: var(--clr-bg);
        }
        .profile-card {
            background-color: var(--clr-surface);
            padding: 3rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            border: 1px solid var(--clr-border);
        }
        .profile-title {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            text-align: center;
            color: var(--clr-text);
        }
        .profile-subtitle {
            text-align: center;
            color: var(--clr-text-light);
            margin-bottom: 2rem;
        }
        .profile-layout {
            display: grid;
            grid-template-columns: 1fr 2.2fr;
            gap: 2.5rem;
            align-items: start;
            margin-top: 1.5rem;
        }
        .profile-sidebar {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        .profile-box {
            background: var(--clr-bg);
            border: 1px solid var(--clr-border);
            border-radius: var(--radius-md);
            padding: 1.5rem;
        }
        .profile-box-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--clr-text);
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--clr-text);
            font-size: 0.85rem;
        }
        .form-control {
            width: 100%;
            padding: 0.6rem 0.8rem;
            border: 1px solid var(--clr-border);
            border-radius: var(--radius-md);
            background-color: transparent;
            color: var(--clr-text);
            font-family: inherit;
            font-size: 0.9rem;
            transition: var(--transition);
        }
        .form-control:focus {
            outline: none;
            border-color: var(--clr-secondary);
            box-shadow: 0 0 0 3px rgba(0, 191, 255, 0.1);
        }
        .promo-scroll {
            display: flex;
            gap: 1rem;
            overflow-x: auto;
            padding-bottom: 1rem;
        }
        .promo-mini-card {
            min-width: 120px;
            max-width: 140px;
            border: 1px solid var(--clr-border);
            border-radius: var(--radius-md);
            padding: 0.5rem;
            text-align: center;
            background: var(--clr-surface);
            text-decoration: none;
            color: var(--clr-text);
            transition: var(--transition);
        }
        .promo-mini-card:hover {
            border-color: var(--clr-primary);
            transform: translateY(-2px);
        }
        .promo-mini-card img {
            width: 100%;
            height: 100px;
            object-fit: contain;
            border-radius: var(--radius-sm);
            margin-bottom: 0.5rem;
        }
        .promo-mini-card h4 {
            font-size: 0.8rem;
            margin-bottom: 0.25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .promo-mini-card span {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--clr-primary);
        }
        @media (max-width: 992px) {
            .profile-layout { grid-template-columns: 1fr; }
            .profile-card { padding: 2rem 1.5rem; }
        }
        .password-container {
            position: relative;
            display: flex;
            align-items: center;
        }
        .password-container input {
            padding-right: 2.5rem;
        }
        .password-toggle {
            position: absolute;
            right: 1rem;
            cursor: pointer;
            color: var(--clr-text-light);
            font-size: 1.25rem;
            transition: var(--transition);
        }
        .password-toggle:hover { color: var(--clr-secondary); }
    </style>
</head>
<body>
    <header class="header">
        <div class="container nav">
            <a href="index.php" class="nav-brand">
                <img src="assets/img/logoPNG.png" alt="Infinity Variedades" style="height: 90px; object-fit: contain;">
            </a>
            <div class="nav-actions">
                <div class="header-search-container">
                    <form action="produtos.html" method="GET" class="header-search-form" id="header-search-form">
                        <i class='bx bx-search'></i>
                        <input type="text" name="q" id="header-search-input" placeholder="Buscar produtos..." autocomplete="off">
                    </form>
                    <div class="search-suggestions" id="header-search-suggestions"></div>
                </div>
                <a href="perfil.php" class="action-btn active" title="Meu Perfil">
                    <i class='bx bx-user'></i>
                </a>
                <a href="admin.php" class="action-btn" id="admin-btn" title="Painel Admin" style="display: none;">
                    <i class='bx bx-cog'></i>
                </a>
                <div class="notification-wrapper" id="notif-wrapper" style="position: relative;">
                    <button class="action-btn" id="notification-btn" title="Notificações">
                        <i class='bx bx-bell'></i>
                        <span class="notification-badge" id="notification-badge" style="display: none;">0</span>
                    </button>
                    <div class="notification-dropdown" id="notification-dropdown">
                        <div class="notification-header">
                            <h4 style="font-size: 1rem; margin: 0;">Novidades</h4>
                            <span class="mark-read-btn" id="mark-read-btn">Limpar</span>
                        </div>
                        <div class="notification-list" id="notification-list"></div>
                        <a href="produtos.html" class="view-all-notif">Ver todos os Produtos</a>
                    </div>
                </div>
                <a href="carrinho.html" class="action-btn" title="Carrinho">
                    <i class='bx bx-cart-alt'></i>
                    <span class="cart-badge" id="cart-badge" style="display: none;">0</span>
                </a>
            </div>
        </div>
    </header>

    <main class="profile-container">
        <div class="profile-card">
            <h1 class="profile-title">Meu Perfil</h1>
            <p class="profile-subtitle">Bem-vindo(a), <strong id="prof-welcome-name">...</strong></p>
            
            <div class="profile-layout">
                <div class="profile-sidebar">
                    <div class="profile-box">
                        <h3 class="profile-box-title"><i class='bx bx-user'></i> Meus Dados</h3>
                        <form class="auth-form" id="profile-form" style="gap: 1rem; display: flex; flex-direction: column;">
                            <div style="text-align: center; margin-bottom: 1rem;">
                                <div style="position: relative; display: inline-block; margin-bottom: 0.5rem;">
                                    <img id="prof-pic-preview" src="https://ui-avatars.com/api/?name=User&background=random" alt="Profile Picture" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid var(--clr-surface); box-shadow: var(--shadow-md);">
                                    <input type="file" id="prof-pic-input" accept="image/*" style="display: none;">
                                </div>
                                <div style="display: flex; justify-content: center; gap: 0.5rem;">
                                    <button type="button" class="btn" id="btn-edit-pic" style="padding: 0.4rem 0.8rem; font-size: 0.75rem; background: var(--clr-primary); color: white; border-radius: var(--radius-full); display: flex; align-items: center; gap: 0.25rem; border: none; cursor: pointer;">
                                        <i class='bx bx-edit-alt'></i> Alterar
                                    </button>
                                    <button type="button" class="btn" id="btn-delete-pic" style="padding: 0.4rem 0.8rem; font-size: 0.75rem; background: #EF4444; color: white; border-radius: var(--radius-full); display: flex; align-items: center; gap: 0.25rem; border: none; cursor: pointer;">
                                        <i class='bx bx-trash'></i> Remover
                                    </button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="prof-nome">Nome Completo</label>
                                <input type="text" id="prof-nome" class="form-control" required placeholder="Seu nome">
                            </div>
                            <button type="submit" class="btn btn-primary" style="padding: 0.8rem; border-radius: var(--radius-md); font-weight: 600; cursor: pointer; background: var(--clr-primary); color: white; border: none; width: 100%;">
                                Salvar Alterações <i class='bx bx-save' style="margin-left: 0.5rem;"></i>
                            </button>
                        </form>
                    </div>

                    <div class="profile-box">
                        <button id="btn-toggle-security" style="padding: 0.8rem; font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 0.5rem; background: linear-gradient(135deg, var(--clr-accent), var(--clr-secondary)); color: white; border: none; border-radius: var(--radius-full); width: 100%; cursor: pointer;">
                            <i class='bx bx-shield-quarter'></i> Privacidade e Segurança
                        </button>
                        <div id="security-content" style="display: none; margin-top: 1.5rem;">
                            <form class="auth-form" id="security-form" style="gap: 1rem; display: flex; flex-direction: column;">
                                <div class="form-group">
                                    <label for="sec-email">Gmail / E-mail</label>
                                    <input type="email" id="sec-email" class="form-control" placeholder="Seu e-mail">
                                </div>
                                <div class="form-group">
                                    <label for="sec-cpf">CPF</label>
                                    <input type="text" id="sec-cpf" class="form-control" placeholder="000.000.000-00">
                                </div>
                                <div class="form-group">
                                    <label for="sec-telefone">Telefone / WhatsApp</label>
                                    <input type="text" id="sec-telefone" class="form-control" placeholder="(00) 00000-0000">
                                </div>
                                <hr style="border: none; border-bottom: 1px solid var(--clr-border); margin: 0.5rem 0;">
                                <div class="form-group">
                                    <label for="sec-senha-atual">Senha Atual</label>
                                    <div class="password-container">
                                        <input type="password" id="sec-senha-atual" class="form-control" placeholder="Sua senha atual">
                                        <i class='bx bx-show password-toggle' onclick="togglePassword('sec-senha-atual', this)"></i>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="sec-nova-senha">Nova Senha</label>
                                    <div class="password-container">
                                        <input type="password" id="sec-nova-senha" class="form-control" placeholder="Mínimo 8 caracteres">
                                        <i class='bx bx-show password-toggle' onclick="togglePassword('sec-nova-senha', this)"></i>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary" style="padding: 0.8rem; border-radius: var(--radius-md); background: var(--clr-primary); color: white; border: none; cursor: pointer;">
                                    Atualizar Segurança <i class='bx bx-lock-alt' style="margin-left: 0.5rem;"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="profile-box">
                        <h3 class="profile-box-title"><i class='bx bx-headphone'></i> Suporte</h3>
                        <p style="font-size: 0.85rem; color: var(--clr-text-light); margin-bottom: 1.25rem;">Alguma dúvida? Fale conosco.</p>
                        <a href="#" id="profile-wa-btn" target="_blank" class="btn" style="background-color: #25D366; color: white; display: flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.8rem; text-decoration: none; border-radius: var(--radius-md); font-weight: 600;">
                            <i class='bx bxl-whatsapp' style="font-size: 1.25rem;"></i> Conversar no WhatsApp
                        </a>
                        <hr style="margin: 1.5rem 0; border: none; border-bottom: 1px solid var(--clr-border);">
                        <button id="btn-logout" class="btn" style="padding: 0.8rem; font-weight: 600; background: transparent; border: 2px solid #EF4444; color: #EF4444; border-radius: var(--radius-md); cursor: pointer; width: 100%; transition: var(--transition);">
                            Sair da Conta <i class='bx bx-log-out' style="margin-left: 0.5rem;"></i>
                        </button>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <div class="profile-box">
                        <h3 class="profile-box-title"><i class='bx bx-cart-alt'></i> Pedidos Realizados</h3>
                        <div id="profile-orders-list"></div>
                    </div>
                    <div class="profile-box" style="border-color: var(--clr-accent);">
                        <h3 class="profile-box-title" style="color: var(--clr-accent);"><i class='bx bxs-discount'></i> Sugestões para Você</h3>
                        <div class="promo-scroll" id="profile-promos-list"></div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container" style="text-align: center;">
            <p>&copy; <script>document.write(new Date().getFullYear())</script> Infinity Variedades. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="assets/js/core/db.js?v=41"></script>
    <script src="assets/js/core/app.js?v=42"></script>
    <script src="assets/js/pages/perfil.js?v=1"></script>
</body>
</html>
