<?php
require_once 'api/security.php';
if (!isAdmin()) {
    header('Location: login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações | Painel Admin</title>
    <script>
        (function() {
            document.documentElement.setAttribute('data-theme', 'dark');
        })();
    </script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="assets/css/style.css?v=29">
    <script>
        (function() {
            document.documentElement.setAttribute('data-theme', 'dark');
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
            .admin-content { padding: 1.5rem 1rem; }
        }
        @media (max-width: 768px) {
            .admin-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            .admin-header > div:last-child {
                width: 100%;
                flex-direction: column;
                align-items: stretch !important;
            }
            /* --- APP-LIKE MENU GRID --- */
            .admin-menu {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 0.75rem;
                padding-bottom: 0;
            }
            .admin-link {
                flex-direction: column;
                justify-content: center;
                text-align: center;
                padding: 1.25rem 0.5rem;
                gap: 0.5rem;
                font-size: 0.85rem;
                border: 1px solid var(--clr-border);
                background: var(--clr-surface);
                box-shadow: 0 4px 6px rgba(0,0,0,0.05);
                white-space: normal;
            }
            .admin-link i { font-size: 1.75rem; margin-bottom: 0.25rem; color: var(--clr-primary); }
            button#theme-toggle { grid-column: 1 / -1; }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <a href="index.php" class="nav-brand" style="margin-bottom: 2rem;">
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
                <a href="index.php" class="admin-link">
                    <i class='bx bx-store-alt'></i> Voltar para Loja
                </a>

            </nav>
        </aside>
        <main class="admin-content">
            <div class="admin-header">
                <div>
                    <h1 class="section-title" style="margin-bottom: 0;">Configurações da Loja</h1>
                    <p style="color: var(--clr-text-light);">Ajuste os parâmetros dinâmicos visíveis para os clientes.</p>
                </div>
            </div>
            <div class="settings-card" style="max-width: 700px;">
                <form id="configForm">
                    <h3 style="margin-bottom: 1.5rem; color: var(--clr-primary); border-bottom: 1px solid var(--clr-border); padding-bottom: 1rem;">
                        <i class='bx bxl-whatsapp'></i> Contato
                    </h3>
                    <div class="form-group">
                        <label>Número do WhatsApp da Loja</label>
                        <input type="text" id="config-whatsapp" class="form-control" placeholder="Ex: 5511999999999">
                        <small style="color: var(--clr-text-light); display: block; margin-top: 0.5rem; font-size: 0.8rem;">Número com DDI e DDD (ex: 5511999999999) para receber os pedidos.</small>
                    </div>
                    <button type="submit" class="btn btn-primary" style="margin-top: 0.5rem;">
                        <i class='bx bx-save'></i> Salvar WhatsApp
                    </button>
                </form>
            </div>

            <div class="settings-card" style="max-width: 700px; margin-top: 2rem;">
                <h3 style="margin-bottom: 1.5rem; color: var(--clr-primary); border-bottom: 1px solid var(--clr-border); padding-bottom: 1rem;">
                    <i class='bx bx-images'></i> Banners da Home
                </h3>
                <p style="color: var(--clr-text-light); font-size: 0.9rem; margin-bottom: 1.5rem;">
                    Adicione imagens que aparecerão como slider na página inicial. Cada banner pode ter um link opcional ao ser clicado.
                </p>
                <div id="banners-list" style="display: flex; flex-direction: column; gap: 1rem; margin-bottom: 1.5rem;"></div>
                <button type="button" id="add-banner-btn" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.25rem; border: 2px dashed var(--clr-border); border-radius: var(--radius-md); background: transparent; color: var(--clr-text-light); cursor: pointer; width: 100%; justify-content: center; transition: var(--transition);">
                    <i class='bx bx-plus-circle'></i> Adicionar Banner
                </button>
                <button type="button" id="save-banners-btn" class="btn btn-primary" style="margin-top: 1.5rem; width: 100%;">
                    <i class='bx bx-save'></i> Salvar Banners
                </button>
            </div>

            <div class="settings-card" style="max-width: 700px; margin-top: 2rem;">
                <h3 style="margin-bottom: 1.5rem; color: var(--clr-primary); border-bottom: 1px solid var(--clr-border); padding-bottom: 1rem;">
                    <i class='bx bx-download'></i> Backup e Restauração
                </h3>
                <p style="color: var(--clr-text-light); font-size: 0.9rem; margin-bottom: 1.5rem;">
                    Exporte todos os dados (produtos, clientes, pedidos, configurações) e imagens da loja em um arquivo ZIP, ou restaure a partir de um backup anterior.
                </p>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <button type="button" id="backup-btn" class="btn btn-primary" style="flex:1; min-width: 200px;">
                        <i class='bx bx-data'></i> Gerar Backup
                    </button>
                    <button type="button" id="restore-btn" class="btn" style="flex:1; min-width: 200px; border: 2px solid var(--clr-primary); color: var(--clr-primary); background: transparent;">
                        <i class='bx bx-upload'></i> Restaurar Backup
                    </button>
                    <input type="file" id="restore-file-input" accept=".zip" style="display:none;">
                </div>
                <p id="backup-status" style="margin-top: 0.75rem; font-size: 0.85rem; color: var(--clr-text-light); display: none;"></p>
            </div>

            <style>
                .banner-item {
                    display: grid;
                    grid-template-columns: 1fr 1fr auto;
                    gap: 0.75rem;
                    align-items: center;
                    padding: 1rem;
                    background: var(--clr-bg);
                    border: 1px solid var(--clr-border);
                    border-radius: var(--radius-md);
                }
                .banner-preview {
                    width: 80px;
                    height: 50px;
                    object-fit: cover;
                    border-radius: var(--radius-md);
                    border: 1px solid var(--clr-border);
                    display: none;
                }
                .banner-remove-btn {
                    width: 36px; height: 36px;
                    border-radius: 50%;
                    border: 1px solid var(--clr-border);
                    background: transparent;
                    color: #EF4444;
                    cursor: pointer;
                    display: flex; align-items: center; justify-content: center;
                    font-size: 1.25rem;
                    transition: var(--transition);
                    flex-shrink: 0;
                }
                .banner-remove-btn:hover { background: #EF4444; color: white; }
                #add-banner-btn:hover { border-color: var(--clr-primary); color: var(--clr-primary); }
            </style>
        </main>
    </div>
    <script src="assets/js/core/db.js?v=29"></script>
    <script src="assets/js/core/app.js?v=29"></script>
    <script src="assets/js/pages/admin_config.js?v=4"></script>
    <script src="assets/js/core/admin_notifications.js?v=4"></script>
</body>
</html>
