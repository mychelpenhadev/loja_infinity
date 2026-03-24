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
    <title>Painel Admin | Infinity Variedades</title>
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
            color: #EF4444;
        }
        /* Modal */
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
                <a href="admin.php" class="admin-link active">
                    <i class='bx bx-cube-alt'></i> Produtos
                </a>
                <a href="admin_pedidos.php" class="admin-link">
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
                    <h1 class="section-title" style="margin-bottom: 0;">Gerenciar Produtos</h1>
                    <p style="color: var(--clr-text-light);">Crie, edite e remova produtos do catálogo.</p>
                </div>
                <button class="btn btn-primary" onclick="openModal()">
                    <i class='bx bx-plus'></i> Novo Produto
                </button>
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
                <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
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
                            <option value="costura">Costura & Bordados</option>
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
                    <div id="image-preview-container" style="margin-top: 1rem; display: none;">
                        <img id="image-preview" style="max-width: 100px; max-height: 100px; border-radius: 8px; border: 1px solid var(--clr-border); object-fit: cover;">
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
    <script src="assets/js/core/db.js?v=4"></script>
    <script src="assets/js/core/app.js?v=4"></script>
    <script src="assets/js/pages/admin.js?v=4"></script>
</body>
</html>
