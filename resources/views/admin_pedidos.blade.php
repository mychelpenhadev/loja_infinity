
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <base href="{{ url('/') }}/">
    <title>Pedidos | Painel Admin</title>
    <script>
        (function() {
            document.documentElement.setAttribute('data-theme', 'dark');
            window.APP_URL = "{{ url('/') }}";
        })();
    </script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css?v=29') }}">
    <style>
        *, *::before, *::after { box-sizing: border-box; }

        :root {
            --sidebar-width: 280px;
            --glass-bg: rgba(0, 56, 56, 0.85);
            --glass-border: rgba(0, 212, 212, 0.2);
            --mobile-header-h: 60px;
        }

        /* ===================== LAYOUT ===================== */
        .admin-layout {
            display: grid;
            grid-template-columns: var(--sidebar-width) 1fr;
            min-height: 100vh;
            background-color: var(--clr-bg);
        }

        /* ===================== SIDEBAR ===================== */
        .admin-sidebar {
            background: var(--glass-bg);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border-right: 1px solid var(--glass-border);
            padding: 2.5rem 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 2.5rem;
            position: sticky;
            top: 0;
            height: 100vh;
            z-index: 1000;
            transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.65);
            backdrop-filter: blur(3px);
            z-index: 999;
        }
        .sidebar-overlay.active { display: block; }

        .admin-menu { display: flex; flex-direction: column; gap: 0.5rem; }

        .admin-link {
            display: flex; align-items: center; gap: 1rem;
            padding: 0.85rem 1.25rem; border-radius: 14px;
            color: var(--clr-text-light); font-weight: 500;
            transition: all 0.3s ease; position: relative;
            overflow: hidden; border: 1px solid transparent;
        }
        .admin-link i { font-size: 1.4rem; }
        .admin-link:hover { background-color: rgba(0,212,212,0.1); color: var(--clr-primary); border-color: rgba(0,212,212,0.1); }
        .admin-link.active { background: linear-gradient(135deg, rgba(0,212,212,0.15) 0%, rgba(0,212,212,0.05) 100%); color: var(--clr-primary); border: 1px solid var(--glass-border); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .admin-link.active::before { content: ''; position: absolute; left: 0; top: 25%; height: 50%; width: 4px; background: var(--clr-primary); border-radius: 0 4px 4px 0; box-shadow: 0 0 10px var(--clr-primary); }

        /* ===================== MAIN ===================== */
        .admin-content {
            padding: 2.5rem 3rem;
            background-color: var(--clr-bg);
            min-width: 0;
        }

        .admin-header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            gap: 1rem;
        }

        /* ===================== STATS ===================== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--clr-surface);
            padding: 1.1rem;
            border-radius: 18px;
            border: 1px solid var(--clr-border);
            display: flex; align-items: center; gap: 0.85rem;
            transition: var(--transition);
        }
        .stat-icon {
            flex-shrink: 0;
            width: 44px; height: 44px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem;
            background: rgba(0,212,212,0.08); color: var(--clr-primary);
        }
        .stat-info h3 { font-size: 0.68rem; color: var(--clr-text-light); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.1rem; }
        .stat-info p  { font-size: 1.3rem; font-weight: 700; }

        /* ===================== TABLE (Desktop) ===================== */
        .admin-table-premium {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 0.75rem;
        }
        .admin-table-premium th { padding: 0 1.25rem 0.75rem; text-align: left; color: var(--clr-text-light); font-weight: 600; font-size: 0.75rem; text-transform: uppercase; }
        .admin-table-premium tbody tr { background: var(--clr-surface); transition: transform 0.2s; }
        .admin-table-premium td { padding: 1rem 1.25rem; vertical-align: middle; }
        .admin-table-premium tbody tr td:first-child { border-radius: 16px 0 0 16px; border-left: 1px solid var(--clr-border); }
        .admin-table-premium tbody tr td:last-child  { border-radius: 0 16px 16px 0; border-right: 1px solid var(--clr-border); }
        .admin-table-premium tbody tr td { border-top: 1px solid var(--clr-border); border-bottom: 1px solid var(--clr-border); }

        /* ===================== ORDER CARDS (Mobile) ===================== */
        .order-cards-list {
            display: none;
            flex-direction: column;
            gap: 1rem;
        }

        .order-card {
            background: var(--clr-surface);
            border-radius: 20px;
            border: 1px solid var(--clr-border);
            overflow: hidden;
            animation: fadeInUp 0.35s ease forwards;
            opacity: 0;
            transition: box-shadow 0.2s;
        }
        .order-card:active { box-shadow: 0 0 0 2px var(--clr-primary); }

        /* Card header row */
        .order-card-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.1rem;
            cursor: pointer;
            user-select: none;
        }

        .order-card-status-dot {
            width: 10px; height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .dot-pending   { background: #F59E0B; box-shadow: 0 0 6px #F59E0B88; }
        .dot-delivered { background: #10B981; box-shadow: 0 0 6px #10B98188; }

        .order-card-main { flex: 1; min-width: 0; }
        .order-card-id   { font-size: 0.75rem; color: var(--clr-text-light); font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; }
        .order-card-name { font-size: 1rem; font-weight: 700; color: var(--clr-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .order-card-price {
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--clr-primary);
            flex-shrink: 0;
        }

        .order-card-chevron {
            font-size: 1.3rem;
            color: var(--clr-text-light);
            transition: transform 0.3s ease;
            flex-shrink: 0;
        }
        .order-card.open .order-card-chevron { transform: rotate(180deg); }

        /* Card body (expandable) */
        .order-card-body {
            display: none;
            padding: 0 1.1rem 1.1rem;
            border-top: 1px solid var(--clr-border);
            flex-direction: column;
            gap: 0.85rem;
        }
        .order-card.open .order-card-body { display: flex; }

        .order-card-row {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
        }
        .order-card-label {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--clr-text-light);
            letter-spacing: 0.05em;
            min-width: 60px;
            padding-top: 0.1rem;
        }
        .order-card-value { font-size: 0.9rem; color: var(--clr-text); flex: 1; }

        .order-card-items {
            background: rgba(0,0,0,0.15);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-size: 0.82rem;
            color: var(--clr-text-light);
            line-height: 1.7;
        }

        .order-card-actions {
            display: flex;
            gap: 0.65rem;
            margin-top: 0.35rem;
        }
        .order-card-actions .btn {
            flex: 1;
            padding: 0.75rem;
            border-radius: 14px;
            font-size: 0.85rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
        }
        .btn-confirm { background: var(--clr-primary); color: #000; }
        .btn-delete  { background: rgba(239,68,68,0.1); color: #EF4444; border: 1px solid rgba(239,68,68,0.25); }

        /* Status badges */
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .status-pendente  { background: rgba(245,158,11,0.12); color: #F59E0B; }
        .status-entregue  { background: rgba(16,185,129,0.12); color: #10B981; }

        /* ===================== MOBILE HEADER ===================== */
        .admin-mobile-header {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0;
            height: var(--mobile-header-h);
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border);
            z-index: 1001;
            padding: 0 1.25rem;
            align-items: center;
            justify-content: space-between;
        }
        .menu-toggle {
            font-size: 1.9rem; color: var(--clr-primary);
            cursor: pointer; padding: 0.3rem; border-radius: 8px;
            transition: background 0.2s; display: flex; align-items: center;
        }
        .menu-toggle:active { background: rgba(0,212,212,0.12); }

        /* ===================== FILTER BAR ===================== */
        .filter-bar {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        .filter-btn {
            padding: 0.45rem 1rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            border: 1px solid var(--clr-border);
            background: transparent;
            color: var(--clr-text-light);
            cursor: pointer;
            transition: all 0.2s;
        }
        .filter-btn.active,
        .filter-btn:hover {
            background: var(--clr-primary);
            color: #000;
            border-color: var(--clr-primary);
        }

        /* ===================== ANIMATIONS ===================== */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ===================== RESPONSIVE ===================== */
        @media (max-width: 992px) {
            .admin-mobile-header { display: flex; }

            .admin-layout { grid-template-columns: 1fr; }

            .admin-sidebar {
                position: fixed;
                left: 0; top: 0;
                transform: translateX(-100%);
                width: min(var(--sidebar-width), 85vw);
                height: 100dvh;
                z-index: 1000;
                overflow-y: auto;
            }
            .admin-sidebar.active { transform: translateX(0); }

            .admin-content {
                padding: calc(var(--mobile-header-h) + 1.25rem) 1rem 4rem;
            }

            .admin-header-flex { flex-direction: column; align-items: stretch; gap: 0.5rem; margin-bottom: 1.25rem; }
            .admin-header-flex h1 { font-size: 1.5rem !important; }
            .admin-header-flex p  { font-size: 0.88rem !important; }

            /* Stats: 2x2 grid */
            .stats-grid { grid-template-columns: 1fr 1fr; gap: 0.6rem; margin-bottom: 1.25rem; }
            .stat-card  { padding: 0.8rem; border-radius: 14px; gap: 0.65rem; }
            .stat-icon  { width: 38px; height: 38px; font-size: 1.2rem; border-radius: 10px; }
            .stat-info h3 { font-size: 0.6rem; }
            .stat-info p  { font-size: 1.1rem; }

            /* Hide table, show cards */
            .admin-table-wrapper { display: none; }
            .order-cards-list    { display: flex; }
        }

        @media (max-width: 480px) {
            .admin-content { padding: calc(var(--mobile-header-h) + 1rem) 0.75rem 4rem; }
            .stats-grid { grid-template-columns: 1fr 1fr; gap: 0.5rem; }
        }
    </style>
</head>
<body>
    <!-- MOBILE HEADER -->
    <header class="admin-mobile-header">
        <a href="{{ url('/') }}">
            <img src="{{ asset('assets/img/logoPNG.png') }}" alt="Logo" style="height: 34px;">
        </a>
        <div class="menu-toggle" onclick="toggleSidebar()" aria-label="Abrir menu">
            <i class='bx bx-menu'></i>
        </div>
    </header>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <div class="admin-layout">
        <!-- SIDEBAR -->
        <aside class="admin-sidebar">
            <a href="{{ url('/') }}" class="nav-brand">
                <img src="{{ asset('assets/img/logoPNG.png') }}" alt="Infinity Variedades" style="height: 56px; object-fit: contain;">
            </a>
            <nav class="admin-menu">
                <a href="{{ url('/admin') }}" class="admin-link">
                    <i class='bx bx-grid-alt'></i> Dashboard
                </a>
                <a href="{{ url('/admin') }}" class="admin-link">
                    <i class='bx bx-cube'></i> Produtos
                </a>
                <a href="{{ url('/admin_pedidos') }}" class="admin-link active">
                    <i class='bx bx-shopping-bag'></i> Pedidos
                </a>
                <a href="{{ url('/admin_config') }}" class="admin-link">
                    <i class='bx bx-cog'></i> Configurações
                </a>
                <div style="margin-top: auto; padding-top: 2rem; border-top: 1px solid var(--glass-border);">
                    <a href="{{ url('/') }}" class="admin-link">
                        <i class='bx bx-log-out'></i> Ver Loja
                    </a>
                </div>
            </nav>
        </aside>

        <!-- MAIN -->
        <main class="admin-content">
            <!-- Header -->
            <div class="admin-header-flex">
                <div>
                    <h1 class="section-title" style="margin-bottom: 0.35rem; font-size: 2.25rem;">Pedidos para Retirada</h1>
                    <p style="color: var(--clr-text-light); font-size: 1.05rem;">Acompanhe e confirme as retiradas na loja física.</p>
                </div>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i class='bx bx-spreadsheet'></i></div>
                    <div class="stat-info"><h3>Total</h3><p id="stat-orders-total">0</p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(245,158,11,0.1); color: #F59E0B;"><i class='bx bx-time-five'></i></div>
                    <div class="stat-info"><h3>Pendentes</h3><p id="stat-orders-pending">0</p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(16,185,129,0.1); color: #10B981;"><i class='bx bx-check-double'></i></div>
                    <div class="stat-info"><h3>Finalizados</h3><p id="stat-orders-completed">0</p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(34,197,94,0.1); color: #22c55e;"><i class='bx bx-money'></i></div>
                    <div class="stat-info"><h3>Vendas</h3><p id="stat-total-sales">R$ 0</p></div>
                </div>
            </div>

            <!-- Filter bar -->
            <div class="filter-bar">
                <button class="filter-btn active" data-filter="all"   onclick="applyFilter(this, 'all')">Todos</button>
                <button class="filter-btn"         data-filter="pendente"  onclick="applyFilter(this, 'pendente')">Pendentes</button>
                <button class="filter-btn"         data-filter="entregue"  onclick="applyFilter(this, 'entregue')">Finalizados</button>
            </div>

            <!-- TABLE: Desktop -->
            <div class="admin-table-wrapper">
                <table class="admin-table-premium">
                    <thead>
                        <tr>
                            <th>ID do Pedido</th>
                            <th>Cliente</th>
                            <th>Data e Status</th>
                            <th>Total</th>
                            <th>Itens</th>
                            <th style="text-align: right;">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="table-orders-body"></tbody>
                </table>
            </div>

            <!-- CARDS: Mobile -->
            <div class="order-cards-list" id="order-cards-list"></div>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.admin-sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const icon    = document.querySelector('.menu-toggle i');
            const isOpen  = sidebar.classList.toggle('active');
            overlay.classList.toggle('active', isOpen);
            icon.className = isOpen ? 'bx bx-x' : 'bx bx-menu';
            document.body.style.overflow = isOpen ? 'hidden' : '';
        }

        function toggleOrderCard(cardEl) {
            cardEl.classList.toggle('open');
        }

        let _currentFilter = 'all';
        function applyFilter(btn, filter) {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            _currentFilter = filter;
            window.renderOrdersTable && window.renderOrdersTable();
        }
        window.getCurrentFilter = () => _currentFilter;
    </script>
    <script src="{{ asset('assets/js/core/db.js?v=29') }}"></script>
    <script src="{{ asset('assets/js/core/app.js?v=29') }}"></script>
    <script src="{{ asset('assets/js/pages/admin_pedidos.js?v=5') }}"></script>
    <script src="{{ asset('assets/js/core/admin_notifications.js?v=4') }}"></script>
</body>
</html>
