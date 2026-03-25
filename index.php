<?php
define('CACHE_FILE', __DIR__ . '/api/cache/home_data.json');
define('CACHE_TIME', 600);
$data = null;
if (file_exists(CACHE_FILE) && (time() - filemtime(CACHE_FILE) < CACHE_TIME)) {
    $data = json_decode(file_get_contents(CACHE_FILE), true);
}
if (!$data) {
    try {
        require_once 'api/db.php';

        $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 8");
        $featuredProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->query("SELECT * FROM products WHERE rating >= 4.8 ORDER BY created_at DESC LIMIT 5");
        $sliderProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $data = [
            'featured' => $featuredProducts,
            'slider' => $sliderProducts,
            'timestamp' => time()
        ];
        file_put_contents(CACHE_FILE, json_encode($data));
    } catch (Exception $e) {

        $featuredProducts = [];
        $sliderProducts = [];
    }
} else {
    $featuredProducts = $data['featured'];
    $sliderProducts = $data['slider'];
}

function formatCurrency($val) {
    return 'R$ ' . number_format($val, 2, ',', '.');
}
function generateStars($rating) {
    $rating = (float)$rating;
    $full = floor($rating);
    $half = ($rating - $full) >= 0.5;
    $html = "";
    for($i=1; $i<=5; $i++) {
        if($i <= $full) $html .= "<i class='bx bxs-star'></i>";
        else if($i == $full + 1 && $half) $html .= "<i class='bx bxs-star-half'></i>";
        else $html .= "<i class='bx bx-star'></i>";
    }
    return $html;
}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Infinity Variedades</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="assets/css/style.css?v=29">

    <link rel="preload" href="assets/img/logoPNG.png" as="image">
    <script>
        (function() {
            const savedTheme = localStorage.getItem('papelaria_theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);

            if (window.location.hash && window.location.hash.startsWith('
                const style = document.createElement('style');
                style.id = 'fast-scroll-hide';
                style.textContent = 'body { visibility: hidden !important; }';
                document.head.appendChild(style);
                let attempts = 0;
                const check = () => {
                    attempts++;
                    const target = document.querySelector(window.location.hash);
                    if (target) {
                        target.scrollIntoView();
                        if (style.parentNode) style.parentNode.removeChild(style);
                    } else if (attempts < 20) {
                        setTimeout(check, 50);
                    } else {
                        if (style.parentNode) style.parentNode.removeChild(style);
                    }
                };
                check();
            }
        })();
    </script>
</head>
<body>
    <header class="header">
        <div class="container nav">
            <a href="index.php" class="nav-brand">
                <img src="assets/img/logoPNG.png" alt="Infinity Variedades" style="height: 90px; object-fit: contain;">
            </a>
            <nav class="nav-links">
                <a href="index.php" class="nav-link active">Home</a>
                <a href="produtos.html?cat=promocoes" class="nav-link">Promoções</a>
                <a href="produtos.html?cat=novidades" class="nav-link">Novidades</a>
                <a href="produtos.html?cat=criancas" class="nav-link">Crianças</a>
                <a href="produtos.html" class="nav-link">Todos os Produtos</a>
            </nav>
            <div class="nav-actions">
                <button class="action-btn" id="search-toggle" title="Pesquisar">
                    <i class='bx bx-search'></i>
                </button>
                <a href="login.html" class="action-btn" title="Minha Conta">
                    <i class='bx bx-user'></i>
                </a>
                <a href="admin.php" class="action-btn" title="Painel Admin" style="display: none;">
                    <i class='bx bx-cog'></i>
                </a>
                <button class="action-btn" id="theme-toggle" title="Mudar Tema">
                    <i class='bx bxs-moon'></i>
                </button>
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
                        <div class="notification-list" id="notification-list">

                        </div>
                        <a href="produtos.html" class="view-all-notif">Ver todos os Produtos</a>
                    </div>
                </div>
                <a href="carrinho.html" class="action-btn" title="Carrinho">
                    <i class='bx bx-shopping-bag'></i>
                    <span class="cart-badge" id="cart-badge" style="display: none;">0</span>
                </a>
            </div>
        </div>
    </header>
    <section class="hero">
        <div class="floating-element el-1">
            <i class='bx bxs-notepad' style="color: var(--clr-primary); font-size: 1.5rem;"></i>
        </div>
        <div class="floating-element el-2">
            <i class='bx bxs-star' style="color: var(--clr-primary); font-size: 1.5rem;"></i>
            <span style="font-weight: 600; font-size: 0.875rem;">Premium</span>
        </div>
        <div class="container hero-grid">
            <div class="hero-content">
                <h1>Organize suas <span>ideias com estilo</span></h1>
                <p>Descubra nossa coleção exclusiva de cadernos inteligentes, canetas em tons pastéis e organizadores que vão transformar sua rotina criativa.</p>
                <a href="produtos.html" class="btn btn-primary">
                    Explorar Coleção <i class='bx bx-right-arrow-alt'></i>
                </a>
            </div>
            <div class="hero-image">
                <div class="hero-slider" id="hero-slider">
                    <?php if (empty($sliderProducts)): ?>
                        <div class="slider-loading"><i class='bx bx-loader-alt bx-spin'></i></div>
                    <?php else: ?>
                        <?php foreach($sliderProducts as $index => $p): ?>
                            <div class="slider-item <?= $index === 0 ? 'active' : '' ?>" data-id="<?= $p['id'] ?>" onclick="window.location.href='detalhes.html?id=<?= $p['id'] ?>'">
                                <img src="<?= $p['image'] ?>" alt="<?= htmlspecialchars($p['name']) ?>" fetchpriority="<?= $index === 0 ? 'high' : 'low' ?>">
                                <div class="slider-caption">
                                    <h3 class="slider-title"><?= htmlspecialchars($p['name']) ?></h3>
                                    <span class="slider-price"><?= formatCurrency($p['price']) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="slider-dots" id="slider-dots">
                    <?php foreach($sliderProducts as $index => $p): ?>
                        <span class="dot <?= $index === 0 ? 'active' : '' ?>" data-index="<?= $index ?>"></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
    <section class="section">
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Destaques da Semana</h2>
                <p class="section-subtitle">Os produtos mais amados pelos nossos criativos</p>
            </div>
            <div class="product-grid" id="featured-products">
                <?php if (empty($featuredProducts)): ?>
                    <p>Nenhum produto em destaque encontrado.</p>
                <?php else: ?>
                    <?php foreach(array_slice($featuredProducts, 0, 4) as $product): ?>
                        <div class="product-card" id="prod-<?= $product['id'] ?>">
                            <?php if ($product['rating'] >= 4.8): ?>
                                <span class="product-badge">Novidade</span>
                            <?php endif; ?>
                            <div class="product-actions">
                                <button class="icon-btn" onclick="window.location.href='detalhes.html?id=<?= $product['id'] ?>'" title="Ver Detalhes">
                                    <i class='bx bx-show'></i>
                                </button>
                            </div>
                            <a href="detalhes.html?id=<?= $product['id'] ?>" class="product-image-container">
                                <img src="<?= $product['image'] ?>" alt="<?= htmlspecialchars($product['name']) ?>" loading="eager" fetchpriority="high">
                            </a>
                            <div class="product-info">
                                <span class="product-category"><?= htmlspecialchars($product['category']) ?></span>
                                <a href="detalhes.html?id=<?= $product['id'] ?>" class="product-title"><?= htmlspecialchars($product['name']) ?></a>
                                <div class="product-rating">
                                    <?= generateStars($product['rating']) ?>
                                    <span style="color: var(--clr-text-light); margin-left: auto; font-size: 0.75rem;">(<?= rand(10, 60) ?>)</span>
                                </div>
                                <div class="product-footer">
                                    <span class="product-price"><?= formatCurrency($product['price']) ?></span>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <button class="btn" style="padding: 0.5rem 0.75rem; font-size: 0.875rem; background-color: var(--clr-primary); color: white;" onclick="window.handleBuyNow('<?= $product['id'] ?>')">
                                            Comprar
                                        </button>
                                        <button class="btn-add" style="padding: 0.5rem 0.75rem; font-size: 0.875rem;" onclick="window.handleAddToCart('<?= $product['id'] ?>')" title="Adicionar ao Carrinho">
                                            <i class='bx bx-cart-add'></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <section class="section" style="background-color: var(--clr-surface); border-top: 1px solid var(--clr-border);">
        <div class="container">
            <div class="hero-grid" style="align-items: center;">
                <div>
                    <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg);">
                        <iframe style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none;" src="https://www.youtube.com/embed/jfKfPfyJRdk?autoplay=0&controls=1&mute=1" allowfullscreen></iframe>
                    </div>
                </div>
                <div>
                    <h2 class="section-title" style="font-size: 2rem;">A inspiração começa aqui.</h2>
                    <p style="color: var(--clr-text-light); margin-bottom: 1.5rem;">Na Infinity Variedades, acreditamos que ferramentas bonitas e funcionais são o combustível para a criatividade. Cada produto é escolhido a dedo para trazer cor e eficiência ao seu universo.</p>
                    <ul style="display: flex; flex-direction: column; gap: 1rem; color: var(--clr-text);">
                        <li style="display: flex; align-items: center; gap: 0.5rem;"><i class='bx bxs-check-circle' style="color: var(--clr-secondary); font-size: 1.25rem;"></i> Produtos selecionados a dedo</li>
                        <li style="display: flex; align-items: center; gap: 0.5rem;"><i class='bx bxs-check-circle' style="color: var(--clr-secondary); font-size: 1.25rem;"></i> Cores que acalmam e inspiram</li>
                        <li style="display: flex; align-items: center; gap: 0.5rem;"><i class='bx bxs-check-circle' style="color: var(--clr-secondary); font-size: 1.25rem;"></i> Qualidade premium garantida</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <a href="index.php" class="nav-brand" style="margin-bottom: 1.5rem; display: inline-flex;">
                        <img src="assets/img/logoPNG.png" alt="Infinity Variedades" style="height: 80px; object-fit: contain;">
                    </a>
                    <p>Sua vida mais colorida e organizada. Entregamos criatividade em forma de papelaria para todo o Brasil.</p>
                    <div class="social-links">
                        <a href="https://instagram.com/infinityvariedades_" target="_blank" class="social-link"><i class='bx bxl-instagram'></i></a>
                    </div>
                </div>
                <div class="footer-col">
                    <h3>Atendimento</h3>
                    <div class="footer-links">
                        <a href="javascript:void(0)" onclick="window.handleWhatsApp()">Fale Conosco</a>
                        <a href="javascript:void(0)" onclick="window.handleWhatsApp()">Dúvidas Frequentes</a>
                    </div>
                </div>
                <div class="footer-col">
                    <h3>Explore</h3>
                    <div class="footer-links">
                        <a href="produtos.html?cat=novidades">Lançamentos</a>
                        <a href="produtos.html?sort=populares">Mais Vendidos</a>
                        <a href="produtos.html?cat=promocoes">Kits Promocionais</a>
                    </div>
                </div>
                <div class="footer-col">
                    <h3>Nossa Loja</h3>
                    <div class="footer-links">
                        <a href="https://www.google.com/maps?q=-3.3217251,-45.0119846" target="_blank">
                            <i class='bx bx-map' style="margin-right: 0.5rem; color: var(--clr-accent);"></i>
                            Ver no Google Maps
                        </a>
                    </div>
                </div>
                </div>
            </div>
            <div class="footer-bottom">
                &copy; <script>document.write(new Date().getFullYear())</script> Infinity Variedades. Todos os direitos reservados.
            </div>
        </div>
    </footer>

    <div id="costura-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class='bx bxs-palette'></i> Costura & Bordados</h3>
                <button class="modal-close" id="close-costura-modal">&times;</button>
            </div>
            <div class="sub-categories-grid">
                <a href="produtos.html?cat=agulhas" class="sub-cat-item">
                    <i class='bx bx-pin'></i>
                    <span>Agulhas</span>
                </a>
                <a href="produtos.html?cat=armarinhos" class="sub-cat-item">
                    <i class='bx bx-cabinet'></i>
                    <span>Armarinhos</span>
                </a>
                <a href="produtos.html?cat=botoes" class="sub-cat-item">
                    <i class='bx bx-radio-circle-marked'></i>
                    <span>Botões e Zíper</span>
                </a>
                <a href="produtos.html?cat=barbantes" class="sub-cat-item">
                    <i class='bx bx-shuffle'></i>
                    <span>Barbantes</span>
                </a>
                <a href="produtos.html?cat=bordados" class="sub-cat-item">
                    <i class='bx bxs-image'></i>
                    <span>Bordados e Viés</span>
                </a>
                <a href="produtos.html?cat=cama" class="sub-cat-item">
                    <i class='bx bx-bed'></i>
                    <span>Cama, Mesa e Banho</span>
                </a>
                <a href="produtos.html?cat=croche" class="sub-cat-item">
                    <i class='bx bx-outline'></i>
                    <span>Crochê e Tricô</span>
                </a>
                <a href="produtos.html?cat=fitas" class="sub-cat-item">
                    <i class='bx bx-ribbon'></i>
                    <span>Fitas e Laços</span>
                </a>
                <a href="produtos.html?cat=las" class="sub-cat-item">
                    <i class='bx bx-water'></i>
                    <span>Lãs e Fios</span>
                </a>
                <a href="produtos.html?cat=linhas" class="sub-cat-item">
                    <i class='bx bx-minus'></i>
                    <span>Linhas Costura e Bordar</span>
                </a>
                <a href="produtos.html?cat=embalagens" class="sub-cat-item">
                    <i class='bx bx-package'></i>
                    <span>Papelaria e Embalagens</span>
                </a>
                <a href="produtos.html?cat=costura" class="sub-cat-item" style="grid-column: 1 / -1; background: var(--clr-primary); color: white;">
                    <i class='bx bx-show'></i>
                    <span>Ver Tudo em Costura</span>
                </a>
            </div>
        </div>
    </div>
    <style>
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .modal-overlay.active { display: flex; }
        .modal-content {
            background: var(--clr-surface);
            border-radius: var(--radius-lg);
            width: 100%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            padding: 2rem;
            animation: modalIn 0.3s ease-out;
        }
        @keyframes modalIn {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--clr-border);
        }
        .modal-header h3 { display: flex; align-items: center; gap: 0.75rem; color: var(--clr-primary); }
        .modal-close {
            font-size: 2rem;
            color: var(--clr-text-light);
            transition: var(--transition);
        }
        .modal-close:hover { color: var(--clr-primary); }
        .sub-categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
        }
        .sub-cat-item {
            background: var(--clr-bg);
            border-radius: var(--radius-md);
            padding: 1.5rem 1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
            text-align: center;
            border: 1px solid var(--clr-border);
            transition: var(--transition);
        }
        .sub-cat-item i { font-size: 1.75rem; color: var(--clr-primary); }
        .sub-cat-item:hover {
            transform: translateY(-5px);
            border-color: var(--clr-primary);
            box-shadow: var(--shadow-md);
        }
        @media (max-width: 576px) {
            .sub-categories-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btnCostura = document.getElementById('btn-costura-modal');
            const modal = document.getElementById('costura-modal');
            const btnClose = document.getElementById('close-costura-modal');
            if (btnCostura && modal) {
                btnCostura.addEventListener('click', (e) => {
                    e.preventDefault();
                    modal.classList.add('active');
                    document.body.style.overflow = 'hidden';
                });
            }
            if (btnClose && modal) {
                btnClose.addEventListener('click', () => {
                    modal.classList.remove('active');
                    document.body.style.overflow = 'auto';
                });
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        modal.classList.remove('active');
                        document.body.style.overflow = 'auto';
                    }
                });
            }
        });
    </script>
    <script src="assets/js/core/db.js?v=29"></script>
    <script src="assets/js/core/app.js?v=29"></script>
    <script src="assets/js/pages/index.js?v=13"></script>
    <?php require_once 'api/security.php'; if(isAdmin()): ?>
    <script src="assets/js/core/admin_notifications.js?v=4"></script>
    <?php endif; ?>
</body>
</html>
