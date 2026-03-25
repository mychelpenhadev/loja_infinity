<?php
require_once 'api/security.php';
if (!isAdmin()) {
    header('Location: login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações | Painel Admin</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="assets/css/style.css?v=22">
    <script>
        (function() {
            const savedTheme = localStorage.getItem('papelaria_theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    <style>
        .admin-layout {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }
        .admin-sidebar {
            background-color: var(--clr-surface);
            border-right: 1px solid var(--clr-border);
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 2rem;
            position: sticky;
            top: 0;
            height: 100vh;
        }
        .admin-menu {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .admin-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: var(--radius-md);
            color: var(--clr-text-light);
            font-weight: 500;
            transition: var(--transition);
        }
        .admin-link:hover, .admin-link.active {
            background-color: rgba(168, 192, 255, 0.15);
            color: var(--clr-secondary);
        }
        .admin-content {
            padding: 2rem 3rem;
            background-color: var(--clr-bg);
            overflow-y: auto;
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--clr-border);
        }
        .settings-card {
            background-color: var(--clr-surface);
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--clr-border);
            max-width: 600px;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--clr-border);
            border-radius: var(--radius-md);
            background-color: var(--clr-bg);
            color: var(--clr-text);
            font-family: var(--font-body);
            transition: var(--transition);
        }
        .form-control:focus {
            outline: none;
            border-color: var(--clr-primary);
            box-shadow: 0 0 0 3px rgba(255, 182, 193, 0.2);
        }
        @media (max-width: 992px) {
            .admin-layout { grid-template-columns: 1fr; }
            .admin-sidebar { height: auto; position: relative; padding: 1rem; }
            .admin-menu { flex-direction: row; overflow-x: auto; }
            .admin-content { padding: 1rem; }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <a href="index.html" class="nav-brand" style="margin-bottom: 2rem;">
                <img src="assets/img/logoPNG.png" alt="Infinity Variedades" style="height: 90px; object-fit: contain;">
            </a>
            <nav class="admin-menu">
                <a href="admin.php" class="admin-link">
                    <i class='bx bx-cube-alt'></i> Produtos
                </a>
                <a href="admin_pedidos.php" class="admin-link">
                    <i class='bx bx-shopping-bag'></i> Pedidos (Retirada)
                </a>
                <a href="admin_config.php" class="admin-link active">
                    <i class='bx bx-cog'></i> Configurações
                </a>
                <a href="index.html" class="admin-link">
                    <i class='bx bx-store-alt'></i> Voltar para Loja
                </a>
                <button class="admin-link" id="theme-toggle" style="width: 100%; text-align: left; border: none; background: transparent;">
                    <i class='bx bxs-moon'></i> Alternar Tema
                </button>
            </nav>
        </aside>
        <main class="admin-content">
            <div class="admin-header">
                <div>
                    <h1 class="section-title" style="margin-bottom: 0;">Configurações da Loja</h1>
                    <p style="color: var(--clr-text-light);">Ajuste os parâmetros dinâmicos visíveis para os clientes.</p>
                </div>
            </div>
            <div class="settings-card">
                <form id="configForm">
                    <h3 style="margin-bottom: 1.5rem; color: var(--clr-primary); border-bottom: 1px solid var(--clr-border); padding-bottom: 1rem;">
                        <i class='bx bxl-whatsapp'></i> Contato
                    </h3>
                    <div class="form-group">
                        <label>Número do WhatsApp da Loja</label>
                        <input type="text" id="config-whatsapp" class="form-control" placeholder="Ex: 5511999999999">
                        <small style="color: var(--clr-text-light); display: block; margin-top: 0.5rem; font-size: 0.8rem;">Número com DDI e DDD (ex: 5511999999999) para receber os pedidos.</small>
                    </div>
                    <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">
                        <i class='bx bx-save'></i> Salvar Configurações
                    </button>
                </form>
            </div>
        </main>
    </div>
    <script src="assets/js/core/db.js?v=22"></script>
    <script src="assets/js/core/app.js?v=22"></script>
    <script src="assets/js/pages/admin_config.js?v=4"></script>
    <script src="assets/js/core/admin_notifications.js?v=4"></script>
</body>
</html>
