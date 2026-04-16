
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <base href="{{ url('/') }}/">
    <title>Configurações | Painel Admin</title>
    <script>
        (function() {
            document.documentElement.setAttribute('data-theme', 'dark');
            window.APP_URL = "{{ url('/') }}";
        })();
    </script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css?v=31') }}">
    <script>
        (function() {
            document.documentElement.setAttribute('data-theme', 'dark');
        })();
    </script>
    <style>
        :root {
            --sidebar-width: 280px;
            --glass-bg: rgba(0, 56, 56, 0.85);
            --glass-border: rgba(0, 212, 212, 0.2);
        }

        *, *::before, *::after { box-sizing: border-box; }

        .admin-layout {
            display: grid;
            grid-template-columns: var(--sidebar-width) 1fr;
            min-height: 100vh;
            background-color: var(--clr-bg);
        }

        /* --- SIDEBAR GLASSMORPHISM --- */
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
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Overlay para fechar sidebar no mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(2px);
            z-index: 999;
        }
        .sidebar-overlay.active { display: block; }

        .admin-menu {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

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

        .admin-link i {
            font-size: 1.4rem;
        }

        .admin-link:hover {
            background-color: rgba(0, 212, 212, 0.1);
            color: var(--clr-primary);
            border-color: rgba(0, 212, 212, 0.1);
        }

        .admin-link.active {
            background: linear-gradient(135deg, rgba(0, 212, 212, 0.15) 0%, rgba(0, 212, 212, 0.05) 100%);
            color: var(--clr-primary);
            border: 1px solid var(--glass-border);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .admin-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 25%;
            height: 50%;
            width: 4px;
            background: var(--clr-primary);
            border-radius: 0 4px 4px 0;
            box-shadow: 0 0 10px var(--clr-primary);
        }

        /* --- MAIN CONTENT --- */
        .admin-content {
            padding: 2.5rem 3rem;
            background-color: var(--clr-bg);
        }

        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
            gap: 1.5rem;
            align-items: start;
        }

        .settings-card {
            background: var(--clr-surface);
            border-radius: 20px;
            padding: 1.5rem;
            border: 1px solid var(--clr-border);
            transition: var(--transition);
        }

        .settings-card-title {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 1.25rem;
            color: var(--clr-text);
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--clr-border);
        }

        .settings-card-title i {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0, 212, 212, 0.08);
            color: var(--clr-primary);
            font-size: 1.25rem;
        }

        .premium-input {
            width: 100%;
            background: rgba(0, 0, 0, 0.03);
            border: 1px solid var(--clr-border);
            padding: 0.85rem 1.1rem;
            border-radius: 14px;
            color: var(--clr-text);
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .premium-input:focus {
            outline: none;
            border-color: var(--clr-primary);
            background: transparent;
            box-shadow: 0 0 15px rgba(0, 212, 212, 0.05);
        }

        @media (max-width: 992px) {
            .admin-layout { grid-template-columns: 1fr; }
            .admin-sidebar { 
                position: fixed;
                left: 0;
                top: 0;
                transform: translateX(-100%);
                width: min(var(--sidebar-width), 85vw);
                height: 100vh;
                z-index: 1000;
                overflow-y: auto;
                transition: transform 0.3s ease;
            }
            .admin-sidebar.active { transform: translateX(0); }
            .admin-content { padding: 5.5rem 1rem 3rem; }
            .settings-grid { grid-template-columns: 1fr; gap: 1rem; }
            #banners-list { grid-template-columns: 1fr !important; }
            .backup-actions { flex-direction: column !important; }
            .backup-inner { grid-template-columns: 1fr !important; gap: 1rem !important; }
        }

        @media (max-width: 480px) {
            .admin-content { padding: 5rem 0.75rem 3rem; }
        }

        .admin-mobile-header {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border);
            z-index: 1001;
            padding: 0 1.25rem;
            align-items: center;
            justify-content: space-between;
        }

        @media (max-width: 992px) {
            .admin-mobile-header { display: flex; }
        }

        .menu-toggle {
            font-size: 2rem;
            color: var(--clr-primary);
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 8px;
            transition: background 0.2s;
        }
        .menu-toggle:active { background: rgba(0,212,212,0.1); }
    </style>
</head>
<body>
    <header class="admin-mobile-header">
        <a href="{{ url('/') }}">
            <img src="{{ asset('assets/img/logoPNG.png') }}" alt="Logo" style="height: 36px;">
        </a>
        <div class="menu-toggle" onclick="toggleSidebar()" aria-label="Abrir menu">
            <i class='bx bx-menu'></i>
        </div>
    </header>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <div class="admin-layout">
        <aside class="admin-sidebar">
            <a href="{{ url('/') }}" class="nav-brand">
                <img src="{{ asset('assets/img/logoPNG.png') }}" alt="Infinity Variedades" style="height: 60px; object-fit: contain;">
            </a>
            <nav class="admin-menu">
                <a href="{{ url('/admin') }}" class="admin-link">
                    <i class='bx bx-grid-alt'></i> Dashboard
                </a>
                <a href="{{ url('/admin') }}" class="admin-link">
                    <i class='bx bx-cube'></i> Produtos
                </a>
                <a href="{{ url('/admin_pedidos') }}" class="admin-link">
                    <i class='bx bx-shopping-bag'></i> Pedidos
                </a>
                <a href="{{ url('/admin_config') }}" class="admin-link active">
                    <i class='bx bx-cog'></i> Configurações
                </a>
                <div style="margin-top: auto; padding-top: 2rem; border-top: 1px solid var(--glass-border);">
                    <a href="{{ url('/') }}" class="admin-link">
                        <i class='bx bx-log-out'></i> Ver Loja
                    </a>
                </div>
            </nav>
        </aside>

        <main class="admin-content">
            <div class="admin-header-flex" style="margin-bottom: 3rem;">
                <div>
                    <h1 class="section-title" style="margin-bottom: 0.5rem; font-size: 2.25rem;">Configurações</h1>
                    <p style="color: var(--clr-text-light); font-size: 1.1rem;">Gerencie os parâmetros globais e visuais da sua loja.</p>
                </div>
            </div>

            <div class="settings-grid">
                <!-- CONTATO -->
                <div class="settings-card">
                    <div class="settings-card-title">
                        <i class='bx bxl-whatsapp'></i>
                        <span>Contato & Vendas</span>
                    </div>
                    <form id="configForm">
                        <div class="form-group">
                            <label>Número do WhatsApp (Receber Pedidos)</label>
                            <input type="text" id="config-whatsapp" class="premium-input" placeholder="Ex: 5511999999999">
                            <p style="font-size: 0.8rem; color: var(--clr-text-light); margin-top: 0.75rem;">Utilize o formato internacional com DDI (55) e DDD.</p>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width: 100%; border-radius: 14px; padding: 1rem;">
                            Salvar Configurações de Contato
                        </button>
                    </form>
                </div>




                <!-- BANNERS -->
                <div class="settings-card" style="grid-column: 1 / -1;">
                    <div class="settings-card-title">
                        <i class='bx bx-images'></i>
                        <span>Banners do Carrossel (Home)</span>
                    </div>
                    <p style="color: var(--clr-text-light); font-size: 0.9rem; margin-bottom: 2rem;">
                        Gerencie as imagens de destaque da página inicial. Recomenda-se imagens de alta resolução (1920x600px).
                    </p>
                    
                    <div id="banners-list" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;"></div>
                    
                    <div style="display: flex; gap: 1rem;">
                        <button type="button" id="add-banner-btn" class="btn" style="flex: 1; border: 2px dashed var(--clr-border); background: transparent; color: var(--clr-text-light); border-radius: 16px;">
                            <i class='bx bx-plus-circle'></i> Novo Banner
                        </button>
                        <button type="button" id="save-banners-btn" class="btn btn-primary" style="flex: 1.5; border-radius: 16px; padding: 1rem;">
                            <i class='bx bx-save'></i> Salvar Todas as Alterações de Banner
                        </button>
                    </div>
                </div>

                <!-- BACKUP -->
                <div class="settings-card" style="grid-column: 1 / -1; background: linear-gradient(135deg, var(--clr-surface) 0%, rgba(0, 212, 212, 0.05) 100%);">
                    <div class="settings-card-title">
                        <i class='bx bx-shield-quarter'></i>
                        <span>Segurança &amp; Dados</span>
                    </div>
                    <div class="backup-inner" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: center;">
                        <div>
                            <p style="color: var(--clr-text-light); font-size: 0.9rem;">
                                Exporte todo o banco de dados e arquivos de mídia da loja. É recomendável fazer backup regularmente.
                            </p>
                        </div>
                        <div class="backup-actions" style="display: flex; gap: 1rem;">
                            <button type="button" id="backup-btn" class="btn btn-primary" style="flex:1; border-radius: 14px;">
                                <i class='bx bx-download'></i> Exportar ZIP
                            </button>
                            <button type="button" id="restore-btn" class="btn" style="flex:1; border-radius: 14px; border: 1px solid var(--clr-primary); color: var(--clr-primary); background: transparent;">
                                <i class='bx bx-upload'></i> Restaurar
                            </button>
                            <input type="file" id="restore-file-input" accept=".zip,.sql" style="display:none;">
                        </div>
                    </div>
                    <p id="backup-status" style="margin-top: 1.5rem; padding: 1rem; border-radius: 12px; font-size: 0.85rem; display: none; background: rgba(0,0,0,0.2);"></p>
                </div>
            </div>


            <style>
                .banner-item {
                    display: flex;
                    flex-direction: column;
                    gap: 1.25rem;
                    padding: 1.5rem;
                    background: rgba(0,0,0,0.2);
                    border: 1px solid var(--clr-border);
                    border-radius: 20px;
                    position: relative;
                }
                .banner-preview {
                    width: 100%;
                    height: 150px;
                    object-fit: cover;
                    border-radius: 12px;
                    border: 2px solid var(--clr-border);
                    margin-bottom: 0.5rem;
                }
                .banner-remove-btn {
                    position: absolute;
                    top: -10px;
                    right: -10px;
                    width: 32px;
                    height: 32px;
                    border-radius: 50%;
                    background: #EF4444;
                    color: white;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    cursor: pointer;
                    box-shadow: var(--shadow-md);
                    z-index: 10;
                }
                .banner-search-item:hover {
                    background: var(--clr-primary) !important;
                    color: white !important;
                }
            </style>
        </main>
    </div>
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.admin-sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const toggleIcon = document.querySelector('.menu-toggle i');
            const isOpen = sidebar.classList.toggle('active');
            overlay.classList.toggle('active', isOpen);
            toggleIcon.className = isOpen ? 'bx bx-x' : 'bx bx-menu';
            document.body.style.overflow = isOpen ? 'hidden' : '';
        }
    </script>
    <script src="{{ asset('assets/js/core/db.js?v=31') }}"></script>
    <script src="{{ asset('assets/js/core/app.js?v=31') }}"></script>
    <script src="{{ asset('assets/js/pages/admin_config.js?v=32') }}"></script>
    <script src="{{ asset('assets/js/core/admin_notifications.js?v=31') }}"></script>
</body>
</html>
