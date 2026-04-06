
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <base href="{{ url('/') }}/">
    <title>Painel Admin | Infinity Variedades</title>
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
            --card-hover-bg: rgba(168, 192, 255, 0.08);
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
            animation: fadeOverlay 0.3s ease;
        }
        .sidebar-overlay.active { display: block; }
        @keyframes fadeOverlay { from { opacity: 0; } to { opacity: 1; } }

        .admin-menu { display: flex; flex-direction: column; gap: 0.5rem; }

        .admin-link {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.85rem 1.25rem;
            border-radius: 14px;
            color: var(--clr-text-light);
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: 1px solid transparent;
        }
        .admin-link i { font-size: 1.4rem; transition: transform 0.3s; }
        .admin-link:hover { background-color: rgba(0, 212, 212, 0.1); color: var(--clr-primary); border-color: rgba(0, 212, 212, 0.1); }
        .admin-link.active { background: linear-gradient(135deg, rgba(0, 212, 212, 0.15) 0%, rgba(0, 212, 212, 0.05) 100%); color: var(--clr-primary); border: 1px solid var(--glass-border); box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); }
        .admin-link.active::before { content: ''; position: absolute; left: 0; top: 25%; height: 50%; width: 4px; background: var(--clr-primary); border-radius: 0 4px 4px 0; box-shadow: 0 0 10px var(--clr-primary); }

        /* ===================== MAIN CONTENT ===================== */
        .admin-content {
            padding: 2.5rem 3rem;
            background-color: var(--clr-bg);
            min-width: 0; /* prevent grid blowout */
        }

        .admin-header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
            gap: 1rem;
        }

        /* ===================== STATS ===================== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--clr-surface);
            padding: 1.25rem;
            border-radius: 20px;
            border: 1px solid var(--clr-border);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: var(--transition);
        }

        .stat-icon {
            flex-shrink: 0;
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            background: rgba(0, 212, 212, 0.08);
            color: var(--clr-primary);
        }

        .stat-info h3 { font-size: 0.7rem; color: var(--clr-text-light); margin-bottom: 0.15rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .stat-info p { font-size: 1.4rem; font-weight: 700; }

        /* ===================== SEARCH ===================== */
        .search-section {
            margin-bottom: 1.5rem;
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        .search-section .search-container { flex: 1; }

        /* ===================== TABLE (Desktop) ===================== */
        .admin-table-premium {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 0.75rem;
        }
        .admin-table-premium th { padding: 0 1.25rem 0.75rem; text-align: left; color: var(--clr-text-light); font-weight: 600; font-size: 0.75rem; text-transform: uppercase; }
        .admin-table-premium tbody tr { background: var(--clr-surface); box-shadow: 0 2px 10px rgba(0,0,0,0.02); transition: transform 0.2s; }
        .admin-table-premium tbody tr td:first-child { border-radius: 16px 0 0 16px; border-left: 1px solid var(--clr-border); }
        .admin-table-premium tbody tr td:last-child { border-radius: 0 16px 16px 0; border-right: 1px solid var(--clr-border); }
        .admin-table-premium tbody tr td { border-top: 1px solid var(--clr-border); border-bottom: 1px solid var(--clr-border); }
        .admin-table-premium td { padding: 1rem 1.25rem; vertical-align: middle; }

        /* ===================== PRODUCT CARDS (Mobile) ===================== */
        .product-cards-list { display: none; flex-direction: column; gap: 0.85rem; }

        .product-card-item {
            background: var(--clr-surface);
            border-radius: 18px;
            border: 1px solid var(--clr-border);
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            animation: fadeInUp 0.35s ease forwards;
            opacity: 0;
        }
        .product-card-item:active { transform: scale(0.985); }

        .product-card-thumb {
            width: 60px;
            height: 60px;
            border-radius: 14px;
            object-fit: cover;
            border: 2px solid var(--clr-border);
            flex-shrink: 0;
        }

        .product-card-info { flex: 1; min-width: 0; }
        .product-card-name { font-size: 0.95rem; font-weight: 700; color: var(--clr-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .product-card-meta { font-size: 0.75rem; color: var(--clr-text-light); margin-top: 0.15rem; display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap; }
        .product-card-price { font-size: 1rem; font-weight: 700; color: var(--clr-primary); margin-top: 0.35rem; }

        .product-card-actions { display: flex; flex-direction: column; gap: 0.4rem; flex-shrink: 0; }
        .product-card-actions .circle-btn { width: 36px; height: 36px; border-radius: 10px; }

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
            font-size: 1.9rem;
            color: var(--clr-primary);
            cursor: pointer;
            padding: 0.3rem;
            border-radius: 8px;
            transition: background 0.2s;
            display: flex;
            align-items: center;
        }
        .menu-toggle:active { background: rgba(0,212,212,0.12); }

        /* ===================== MODAL (Desktop) ===================== */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(10px);
            z-index: 2000;
            display: none;
            padding: 1.5rem;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }
        .modal-overlay.active { display: flex; }

        .modal-content {
            background: var(--clr-surface);
            border-radius: 30px;
            width: 100%;
            max-width: 900px;
            margin: auto;
            padding: 3rem;
            position: relative;
        }

        /* ===================== MODAL FORM GRIDS ===================== */
        #productForm { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; }

        .price-grid { display: grid; grid-template-columns: 1fr 0.8fr 1fr; gap: 1rem; margin-top: 1.5rem; }
        .qty-grid   { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1.5rem; }
        .meta-grid  { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1.5rem; }

        /* ===================== FAB (Mobile floating Novo Produto) ===================== */
        .fab-new-product {
            display: none;
            position: fixed;
            bottom: 1.5rem;
            right: 1.25rem;
            z-index: 800;
            background: var(--clr-primary);
            color: #000;
            border-radius: 50px;
            padding: 0.85rem 1.4rem;
            font-size: 0.95rem;
            font-weight: 700;
            box-shadow: 0 8px 25px rgba(0,212,212,0.4);
            align-items: center;
            gap: 0.5rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .fab-new-product i { font-size: 1.4rem; }
        .fab-new-product:active { transform: scale(0.95); box-shadow: 0 4px 12px rgba(0,212,212,0.3); }

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
                padding: calc(var(--mobile-header-h) + 1.25rem) 1rem 6rem;
            }

            /* Header: compact */
            .admin-header-flex {
                flex-direction: column;
                align-items: stretch;
                gap: 0.75rem;
                margin-bottom: 1.5rem;
            }
            .admin-header-flex h1 { font-size: 1.5rem !important; }
            .admin-header-flex p   { font-size: 0.9rem !important; }
            /* Hide desktop "Novo Produto" button */
            .admin-header-flex .btn-new-product { display: none; }

            /* Stats: 3 cols compact */
            .stats-grid { grid-template-columns: repeat(3, 1fr); gap: 0.6rem; margin-bottom: 1.25rem; }
            .stat-card { padding: 0.75rem; border-radius: 14px; gap: 0.6rem; }
            .stat-icon { width: 38px; height: 38px; font-size: 1.2rem; border-radius: 10px; }
            .stat-info h3 { font-size: 0.6rem; }
            .stat-info p  { font-size: 1.1rem; }

            /* Search */
            .search-section { flex-direction: column; gap: 0.75rem; }
            .search-section .search-container { width: 100%; }

            /* Table → hide, show cards */
            .admin-table-wrapper { display: none; }
            .product-cards-list  { display: flex; }
            #admin-pagination { padding: 1rem 0 !important; }

            /* FAB */
            .fab-new-product { display: flex; }

            /* Modal → Bottom Sheet */
            .modal-overlay {
                padding: 0;
                align-items: flex-end;
            }
            .modal-content {
                border-radius: 28px 28px 0 0;
                max-width: 100%;
                margin: 0;
                padding: 1.5rem 1.25rem;
                max-height: 92dvh;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
            }
            /* Sheet handle */
            .modal-content::before {
                content: '';
                display: block;
                width: 40px;
                height: 4px;
                background: var(--clr-border);
                border-radius: 2px;
                margin: 0 auto 1.25rem;
            }
            .modal-header { margin-bottom: 1.25rem !important; }
            #modal-title { font-size: 1.5rem !important; }
            .modal-header p { font-size: 0.9rem !important; }

            #productForm { grid-template-columns: 1fr !important; gap: 0 !important; }
            .price-grid  { grid-template-columns: 1fr 1fr !important; }
            .qty-grid    { grid-template-columns: 1fr 1fr !important; }
            .meta-grid   { grid-template-columns: 1fr !important; }

            .modal-column + .modal-column { border-top: 1px solid var(--clr-border); padding-top: 1.5rem; margin-top: 0.5rem; }

            /* Modal close btn */
            .modal-close { top: 1rem !important; right: 1rem !important; }
        }

        @media (max-width: 480px) {
            .admin-content { padding: calc(var(--mobile-header-h) + 1rem) 0.75rem 6rem; }
            .stats-grid { grid-template-columns: 1fr 1fr; gap: 0.5rem; }
            .stat-card { padding: 0.7rem 0.6rem; }
            .price-grid { grid-template-columns: 1fr !important; }
        }

        /* ===================== ANIMATIONS ===================== */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideUp {
            from { transform: translateY(100%); }
            to   { transform: translateY(0); }
        }

        @media (max-width: 992px) {
            .modal-overlay.active .modal-content {
                animation: slideUp 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            }
        }
    </style>
</head>
<body>
    <!-- ====== MOBILE HEADER ====== -->
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
        <!-- ====== SIDEBAR ====== -->
        <aside class="admin-sidebar">
            <a href="{{ url('/') }}" class="nav-brand">
                <img src="{{ asset('assets/img/logoPNG.png') }}" alt="Infinity Variedades" style="height: 56px; object-fit: contain;">
            </a>
            <nav class="admin-menu">
                <a href="{{ url('/admin') }}" class="admin-link active">
                    <i class='bx bx-grid-alt'></i> Dashboard
                </a>
                <a href="{{ url('/admin') }}" class="admin-link">
                    <i class='bx bx-cube'></i> Produtos
                </a>
                <a href="{{ url('/admin_pedidos') }}" class="admin-link">
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

        <!-- ====== MAIN ====== -->
        <main class="admin-content">
            <!-- Header -->
            <div class="admin-header-flex">
                <div>
                    <h1 class="section-title" style="margin-bottom: 0.35rem; font-size: 2.25rem;">Gerenciar Produtos</h1>
                    <p style="color: var(--clr-text-light); font-size: 1.05rem;">Controle total sobre o seu catálogo de ofertas.</p>
                </div>
                <button class="btn btn-primary btn-new-product" onclick="openModal()" style="border-radius: 18px; padding: 1rem 2rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class='bx bx-plus-circle' style="font-size: 1.4rem;"></i> Novo Produto
                </button>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i class='bx bx-box'></i></div>
                    <div class="stat-info">
                        <h3>Produtos</h3>
                        <p id="stat-total-products">0</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(168, 192, 255, 0.1); color: var(--clr-secondary);"><i class='bx bx-category'></i></div>
                    <div class="stat-info">
                        <h3>Categorias</h3>
                        <p id="stat-total-categories">0</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(var(--clr-accent-rgb), 0.1); color: var(--clr-accent);"><i class='bx bx-trending-up'></i></div>
                    <div class="stat-info">
                        <h3>Valor Médio</h3>
                        <p id="stat-avg-price">R$ 0</p>
                    </div>
                </div>
            </div>

            <!-- Search -->
            <div class="search-section">
                <div class="search-container">
                    <i class='bx bx-search'></i>
                    <input type="text" id="admin-search" class="premium-input" placeholder="Buscar por nome, categoria ou marca...">
                </div>
                <div id="filter-indicator" style="font-size: 0.85rem; color: var(--clr-text-light); white-space: nowrap;"></div>
            </div>

            <!-- TABLE: Desktop -->
            <div class="admin-table-wrapper">
                <table class="admin-table-premium">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Categoria</th>
                            <th>Preço</th>
                            <th style="text-align: right;">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="table-body"></tbody>
                </table>
            </div>

            <!-- CARDS: Mobile -->
            <div class="product-cards-list" id="product-cards-list"></div>
        </main>
    </div>

    <!-- ====== FAB (Mobile) ====== -->
    <button class="fab-new-product" onclick="openModal()" aria-label="Novo Produto">
        <i class='bx bx-plus-circle'></i> Novo Produto
    </button>

    <!-- ====== MODAL / BOTTOM SHEET ====== -->
    <div class="modal-overlay" id="productFormModal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal()" title="Fechar" style="position: absolute; top: 1.5rem; right: 1.5rem; z-index: 10;">
                <i class='bx bx-x'></i>
            </button>

            <div class="modal-header" style="text-align: left; margin-bottom: 2rem;">
                <h2 id="modal-title" style="font-size: 2rem; color: var(--clr-text); font-weight: 800; letter-spacing: -0.02em;">Novo Produto</h2>
                <p style="color: var(--clr-text-light); font-size: 1rem; margin-top: 0.25rem;">Preencha as informações para atualizar seu estoque.</p>
            </div>

            <form id="productForm">
                <input type="hidden" id="prod-id">

                <!-- Coluna 1: Identificação -->
                <div class="modal-column">
                    <div class="form-section" style="border: none; padding: 0;">
                        <div class="form-section-title"><i class='bx bx-info-circle'></i> Identificação</div>

                        <div class="form-group">
                            <label>Nome do Produto *</label>
                            <div style="position: relative;">
                                <i class='bx bx-purchase-tag' style="position: absolute; left: 1.25rem; top: 1.1rem; color: var(--clr-primary); font-size: 1.2rem;"></i>
                                <input type="text" id="prod-nome" class="premium-input" style="padding-left: 3.2rem;" required placeholder="Nome do item...">
                            </div>
                        </div>

                        <div class="price-grid">
                            <div class="form-group">
                                <label>Preço Original (R$)</label>
                                <div style="position: relative;">
                                    <i class='bx bx-dollar' style="position: absolute; left: 1.25rem; top: 1.1rem; color: var(--clr-primary); font-size: 1.2rem;"></i>
                                    <input type="number" id="prod-original-price" class="premium-input" style="padding-left: 3.2rem;" min="0" step="0.01" placeholder="0.00">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Desconto (%)</label>
                                <div style="position: relative;">
                                    <i class='bx bx-trending-down' style="position: absolute; left: 1.25rem; top: 1.1rem; color: var(--clr-accent); font-size: 1.2rem;"></i>
                                    <input type="number" id="prod-discount" class="premium-input" style="padding-left: 3.2rem;" min="0" max="100" placeholder="0">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Preço de Venda (R$) *</label>
                                <div style="position: relative;">
                                    <i class='bx bx-cart-alt' style="position: absolute; left: 1.25rem; top: 1.1rem; color: var(--clr-primary); font-size: 1.2rem;"></i>
                                    <input type="number" id="prod-preco" class="premium-input" style="padding-left: 3.2rem;" required min="0" step="0.01" placeholder="0.00">
                                </div>
                            </div>
                        </div>

                        <div class="qty-grid">
                            <div class="form-group">
                                <label>Qtde. em Estoque</label>
                                <div style="position: relative;">
                                    <i class='bx bx-archive' style="position: absolute; left: 1.25rem; top: 1.1rem; color: var(--clr-primary); font-size: 1.2rem;"></i>
                                    <input type="number" id="prod-stock" class="premium-input" style="padding-left: 3.2rem;" min="0" placeholder="0">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Qtde. Vendida</label>
                                <div style="position: relative;">
                                    <i class='bx bx-rocket' style="position: absolute; left: 1.25rem; top: 1.1rem; color: var(--clr-accent); font-size: 1.2rem;"></i>
                                    <input type="number" id="prod-sold" class="premium-input" style="padding-left: 3.2rem;" min="0" placeholder="0">
                                </div>
                            </div>
                        </div>

                        <div class="meta-grid">
                            <div class="form-group">
                                <label>Marca (Opcional)</label>
                                <div style="position: relative;">
                                    <i class='bx bx-copyright' style="position: absolute; left: 1.25rem; top: 1.1rem; color: var(--clr-primary); font-size: 1.2rem;"></i>
                                    <input type="text" id="prod-marca" class="premium-input" style="padding-left: 3.2rem;" placeholder="Ex: Infinity">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Categoria *</label>
                                <div style="position: relative;">
                                    <i class='bx bx-category' style="position: absolute; left: 1.25rem; top: 1.1rem; color: var(--clr-primary); font-size: 1.2rem; z-index: 1;"></i>
                                    <select id="prod-categoria" class="premium-input" style="padding-left: 3.2rem; cursor: pointer; appearance: none;" required>
                                        <option value="" disabled selected>Selecione uma categoria</option>
                                        <option value="promocoes">Promoções</option>
                                        <option value="novidades">Novidades</option>
                                        <option value="criancas">Crianças</option>
                                        <option value="cadernos">Cadernos</option>
                                        <option value="canetas">Canetas &amp; Lápis</option>
                                        <option value="mochilas">Mochilas</option>
                                        <option value="materiais escolares">Materiais Escolares</option>
                                        <optgroup label="Costura e Bordados">
                                            <option value="costura">Geral Costura</option>
                                            <option value="croche">Crochê e Tricô</option>
                                            <option value="las">Lãs e Fios</option>
                                            <option value="linhas">Linhas Costura</option>
                                        </optgroup>
                                        <option value="outros">Outros</option>
                                    </select>
                                    <i class='bx bx-chevron-down' style="position: absolute; right: 1.25rem; top: 1.1rem; color: var(--clr-text-light); pointer-events: none;"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 1.5rem;">
                        <div class="form-section-title"><i class='bx bx-detail'></i> Descrição</div>
                        <textarea id="prod-desc" class="premium-input" style="min-height: 130px; padding: 1.1rem;" required placeholder="Descreva os detalhes e benefícios..."></textarea>
                    </div>
                </div>

                <!-- Coluna 2: Multimedia + Botões -->
                <div class="modal-column">
                    <div class="form-section" style="border: none; padding: 0;">
                        <div class="form-section-title"><i class='bx bx-image'></i> Multimedia</div>

                        <div class="form-group">
                            <label>Imagem do Produto *</label>
                            <div class="image-upload-zone" onclick="document.getElementById('prod-imagem-file').click()" style="border: 2px dashed var(--clr-border); border-radius: 18px; padding: 1.75rem; text-align: center; cursor: pointer; transition: 0.3s; background: rgba(0,0,0,0.02);">
                                <input type="file" id="prod-imagem-file" style="display: none;" accept="image/*">
                                <input type="hidden" id="prod-imagem-base64">

                                <div id="upload-placeholder">
                                    <i class='bx bx-cloud-upload' style="font-size: 2.5rem; color: var(--clr-primary); margin-bottom: 0.75rem;"></i>
                                    <p style="font-size: 0.85rem; color: var(--clr-text-light);">Toque para fazer upload<br><small>PNG, JPG (Máx 2MB)</small></p>
                                </div>

                                <div id="image-preview-container" style="display: none; position: relative; width: 100%; aspect-ratio: 1; margin-top: 0;">
                                    <img id="image-preview" style="width: 100%; height: 100%; border-radius: 12px; object-fit: cover;">
                                    <button type="button" id="btn-remove-prod-img" style="position: absolute; top: 10px; right: 10px; background: #EF4444; color: white; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow-md); z-index: 10;">
                                        <i class='bx bx-trash'></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group" style="margin-top: 1.25rem;">
                            <label>Link de Vídeo</label>
                            <div style="position: relative;">
                                <i class='bx bxl-youtube' style="position: absolute; left: 1.25rem; top: 1.1rem; color: #FF0000; font-size: 1.2rem;"></i>
                                <input type="url" id="prod-video" class="premium-input" style="padding-left: 3.2rem;" placeholder="https://youtube.com/...">
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 1.5rem; display: flex; flex-direction: column; gap: 0.75rem;">
                        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.1rem; border-radius: 16px; font-size: 1rem; font-weight: 700; box-shadow: 0 10px 20px rgba(0, 212, 212, 0.2);">
                            Finalizar e Salvar
                        </button>
                        <button type="button" class="btn" style="width: 100%; padding: 1.1rem; border-radius: 16px; background: rgba(0,0,0,0.05); color: var(--clr-text-light);" onclick="closeModal()">
                            Cancelar
                        </button>
                    </div>
                </div>
            </form>
        </div>
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
    </script>
    <script src="{{ asset('assets/js/core/db.js?v=30') }}"></script>
    <script src="{{ asset('assets/js/core/app.js?v=29') }}"></script>
    <script src="{{ asset('assets/js/pages/admin.js?v=5') }}"></script>
    <script src="{{ asset('assets/js/core/admin_notifications.js?v=4') }}"></script>
</body>
</html>
