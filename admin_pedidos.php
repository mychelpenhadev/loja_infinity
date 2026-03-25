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
    <title>Pedidos | Painel Admin</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="assets/css/style.css">
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
        .admin-table-container {
            background-color: var(--clr-surface);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--clr-border);
            overflow: hidden;
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }
        .admin-table th, .admin-table td {
            text-align: left;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--clr-border);
        }
        .admin-table th {
            background-color: rgba(var(--clr-bg), 0.5);
            font-weight: 600;
            color: var(--clr-text-light);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }
        .admin-table tbody tr:hover {
            background-color: rgba(168, 192, 255, 0.05);
        }
        
        @media (max-width: 992px) {
            .admin-layout {
                grid-template-columns: 1fr;
            }
            .admin-sidebar {
                height: auto;
                position: relative;
                padding: 1rem;
            }
            .admin-menu {
                flex-direction: row;
                overflow-x: auto;
            }
            .admin-content {
                padding: 1rem;
            }
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
                <a href="admin_pedidos.php" class="admin-link active">
                    <i class='bx bx-shopping-bag'></i> Pedidos (Retirada)
                </a>
                <a href="admin_config.php" class="admin-link">
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
                    <h1 class="section-title" style="margin-bottom: 0;">Pedidos para Retirada</h1>
                    <p style="color: var(--clr-text-light);">Acompanhe e confirme os pedidos feitos pelos clientes para retirada física.</p>
                </div>
            </div>
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nº do Pedido</th>
                            <th>Cliente</th>
                            <th>Data</th>
                            <th>Valor Total</th>
                            <th>Itens</th>
                        </tr>
                    </thead>
                    <tbody id="table-orders-body">
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <script src="assets/js/core/db.js?v=4"></script>
    <script src="assets/js/core/app.js?v=4"></script>
    <script src="assets/js/pages/admin_pedidos.js?v=4"></script>
    <script src="assets/js/core/admin_notifications.js?v=4"></script>
</body>
</html>
