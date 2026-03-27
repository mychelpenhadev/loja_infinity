<?php
require_once '../../backend/api/security.php';
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
    <title>Pedidos | Painel Admin</title>
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
        .admin-table-container {
            background-color: var(--clr-surface);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--clr-border);
            overflow: hidden;
        }
        .admin-table {
            width: 100%;
            min-width: 650px;
            border-collapse: collapse;
        }
        .admin-table th, .admin-table td {
            text-align: left;
            padding: 1rem;
            border-bottom: 1px solid var(--clr-border);
            white-space: nowrap;
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

            /* --- APP-LIKE TABLE CARDS --- */
            .admin-table-container { 
                background: transparent;
                border: none;
                box-shadow: none;
                overflow: visible;
            }
            .admin-table, .admin-table tbody, .admin-table tr, .admin-table td {
                display: block; width: 100%; min-width: 0;
            }
            .admin-table thead { display: none; }
            .admin-table tr {
                background: var(--clr-surface);
                border: 1px solid var(--clr-border);
                border-radius: var(--radius-lg);
                margin-bottom: 1rem;
                padding: 1rem;
                box-shadow: 0 4px 10px rgba(0,0,0,0.03);
                width: 100%;
                box-sizing: border-box;
            }
            .admin-table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.5rem 0;
                border: none;
                white-space: normal;
                font-size: 0.9rem;
                box-sizing: border-box;
            }
            .admin-table td::before {
                font-weight: 600;
                color: var(--clr-text-light);
                font-size: 0.75rem;
                text-transform: uppercase;
            }
            /* Específico para Pedidos */
            .admin-table td:nth-child(1) { border-bottom: 1px dashed var(--clr-border); padding-bottom: 0.75rem; margin-bottom: 0.5rem; justify-content: space-between; font-weight: bold; font-size: 1.1rem; }
            .admin-table td:nth-child(2)::before { content: "Cliente"; }
            .admin-table td:nth-child(3)::before { content: "Data"; }
            .admin-table td:nth-child(4)::before { content: "Valor Total"; }
            .admin-table td:nth-child(5)::before { content: "Itens"; }
            .admin-table td:nth-child(6) { margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px dashed var(--clr-border); justify-content: center; }
            .admin-table td:nth-child(6) .btn { width: 100%; }
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
                <a href="admin_pedidos.php" class="admin-link active">
                    <i class='bx bx-cart-alt'></i> Pedidos (Retirada)
                </a>
                <a href="admin_config.php" class="admin-link">
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
    <script src="assets/js/core/db.js?v=29"></script>
    <script src="assets/js/core/app.js?v=29"></script>
    <script src="assets/js/pages/admin_pedidos.js?v=4"></script>
    <script src="assets/js/core/admin_notifications.js?v=4"></script>
</body>
</html>
