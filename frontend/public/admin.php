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
    <title>Painel Admin | Infinity Variedades</title>
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
        .prod-cell {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .prod-thumb {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-md);
            object-fit: cover;
            background-color: var(--clr-bg);
            border: 1px solid var(--clr-border);
        }
        .action-btns {
            display: flex;
            gap: 0.5rem;
        }
        .action-btns button {
            width: 32px;
            height: 32px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.125rem;
            color: var(--clr-text-light);
            transition: var(--transition);
        }
        .action-btns .edit-btn:hover {
            background-color: rgba(168, 192, 255, 0.2);
            color: var(--clr-secondary);
        }
        .action-btns .delete-btn:hover {
            background-color: rgba(239, 68, 68, 0.1);
            color:
        }

        .modal-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            pointer-events: none;
            transition: var(--transition);
        }
        .modal-overlay.active {
            opacity: 1;
            pointer-events: all;
        }
        .modal-content {
            background-color: var(--clr-surface);
            padding: 2.5rem;
            border-radius: var(--radius-lg);
            width: 100%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: var(--shadow-lg);
            transform: translateY(20px);
            transition: var(--transition);
        }
        .modal-overlay.active .modal-content {
            transform: translateY(0);
        }
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            font-size: 0.875rem;
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
        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--clr-border);
        }
        .form-row-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
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
            .admin-content {
                padding: 1.5rem 1rem;
            }
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
            .admin-search-box input {
                width: 100% !important;
            }
            .form-row-2 {
                grid-template-columns: 1fr;
            }
            .modal-content {
                padding: 1.5rem;
                margin: 1rem;
                max-width: calc(100% - 2rem);
            }
            .modal-actions {
                flex-direction: column-reverse;
                gap: 0.5rem;
            }
            .modal-actions button {
                width: 100%;
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
            /* Específico para Produtos */
            .admin-table td:nth-child(1) { border-bottom: 1px dashed var(--clr-border); padding-bottom: 0.75rem; margin-bottom: 0.5rem; justify-content: flex-start; }
            .admin-table td:nth-child(2)::before { content: "Categoria"; }
            .admin-table td:nth-child(3)::before { content: "Preço"; }
            .admin-table td:nth-child(4) { margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px dashed var(--clr-border); justify-content: center; }
            .admin-table td:nth-child(4) .action-btns { width: 100%; justify-content: space-evenly; gap: 1rem; display: flex; }
            .admin-table td:nth-child(4) .action-btns button { width: 45px; height: 45px; font-size: 1.5rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: var(--clr-bg); border: 1px solid var(--clr-border); }
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
                <a href="admin.php" class="admin-link active">
                    <i class='bx bx-cube-alt'></i> Produtos
                </a>
                <a href="admin_pedidos.php" class="admin-link">
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
                    <h1 class="section-title" style="margin-bottom: 0;">Gerenciar Produtos</h1>
                    <p style="color: var(--clr-text-light);">Crie, edite e remova produtos do catálogo.</p>
                </div>
                <div style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
                    <div class="admin-search-box" style="position: relative;">
                        <i class='bx bx-search' style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--clr-text-light); font-size: 1.25rem;"></i>
                        <input type="text" id="admin-search" placeholder="Pesquisar produto pelo nome..."
                            style="padding: 0.75rem 1rem 0.75rem 2.75rem; border-radius: var(--radius-md); border: 1px solid var(--clr-border); background: var(--clr-surface); color: var(--clr-text); width: 300px; font-family: inherit;">
                    </div>
                    <button class="btn btn-primary" onclick="openModal()">
                        <i class='bx bx-plus'></i> Novo Produto
                    </button>
                </div>
            </div>
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Categoria</th>
                            <th>Preço</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    <div class="modal-overlay" id="productFormModal">
        <div class="modal-content">
            <h2 style="margin-bottom: 1.5rem;" id="modal-title">Novo Produto</h2>
            <form id="productForm">
                <input type="hidden" id="prod-id">
                <div class="form-group">
                    <label>Nome do Produto *</label>
                    <input type="text" id="prod-nome" class="form-control" required placeholder="Ex: Caderno Inteligente">
                </div>
                <div class="form-group form-row-2">
                    <div>
                        <label>Preço (R$) *</label>
                        <input type="number" id="prod-preco" class="form-control" required min="0" step="0.01" placeholder="Ex: 89.90">
                    </div>
                    <div>
                        <label>Categoria *</label>
                        <select id="prod-categoria" class="form-control" required>
                            <option value="promocoes">Promoções</option>
                            <option value="novidades">Novidades</option>
                            <option value="criancas">Crianças</option>
                            <option value="cadernos">Cadernos</option>
                            <option value="canetas">Canetas & Lápis</option>
                            <option value="mochilas">Mochilas</option>
                            <option value="materiais escolares">Materiais Escolares</option>
                            <optgroup label="Costura e Bordados">
                                <option value="costura">Geral Costura</option>
                                <option value="agulhas">Agulhas</option>
                                <option value="armarinhos">Armarinhos</option>
                                <option value="botoes">Botões e Zíper</option>
                                <option value="barbantes">Barbantes</option>
                                <option value="bordados">Bordados e Viés</option>
                                <option value="cama">Cama, Mesa e Banho</option>
                                <option value="croche">Crochê e Tricô</option>
                                <option value="fitas">Fitas e Laços</option>
                                <option value="las">Lãs e Fios</option>
                                <option value="linhas">Linhas Costura e Bordar</option>
                                <option value="embalagens">Papelaria e Embalagens</option>
                            </optgroup>
                            <option value="outros">Outros</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Marca (Para Costura e Canetas)</label>
                    <input type="text" id="prod-marca" class="form-control" placeholder="Ex: Amigurumi, Bic...">
                </div>
                <div class="form-group">
                    <label>Imagem do Produto (Do seu PC) *</label>
                    <input type="file" id="prod-imagem-file" class="form-control" accept="image/*">
                    <input type="hidden" id="prod-imagem-base64">
                    <small style="color: var(--clr-text-light); display: block; margin-top: 0.5rem; font-size: 0.75rem;">Selecione uma foto salva no seu computador.</small>
                    <div id="image-preview-container" style="margin-top: 1rem; display: none; position: relative; width: 100px;">
                        <img id="image-preview" style="max-width: 100px; max-height: 100px; border-radius: 8px; border: 1px solid var(--clr-border); object-fit: cover;">
                        <button type="button" id="btn-remove-prod-img" style="position: absolute; top: -10px; right: -10px; background: #EF4444; color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; box-shadow: var(--shadow-sm);" title="Remover Foto">
                            <i class='bx bx-x'></i>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label>URL do Vídeo (Opcional, preferência YouTube embed)</label>
                    <input type="url" id="prod-video" class="form-control" placeholder="https://www.youtube.com/embed/...">
                </div>
                <div class="form-group">
                    <label>Descrição Completa *</label>
                    <textarea id="prod-desc" class="form-control" required rows="4" placeholder="Detalhes do produto..."></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn" style="background: var(--clr-bg); color: var(--clr-text);" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Produto</button>
                </div>
            </form>
        </div>
    </div>
    <script src="assets/js/core/db.js?v=30"></script>
    <script src="assets/js/core/app.js?v=29"></script>
    <script src="assets/js/pages/admin.js?v=4"></script>
    <script src="assets/js/core/admin_notifications.js?v=4"></script>
</body>
</html>
