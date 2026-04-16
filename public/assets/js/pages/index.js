        document.addEventListener('DOMContentLoaded', async () => {
            // Banner Slider (Principal)
            const bannerSlider = document.getElementById('hero-banner-slider');
            const bannerDots = document.getElementById('hb-dots');
            const bannerPrev = document.getElementById('hb-prev');
            const bannerNext = document.getElementById('hb-next');
            const bannerPlaceholder = document.getElementById('hero-banner-placeholder');

            if (bannerSlider) {
                let banners = [];
                let topProductsForFallback = [];
                let allProductsMap = {};
                try {
                    // Espera o ConfigManager carregar os dados da API (através do db.js)
                    await window.ConfigManager.waitReady();
                    const raw = window.ConfigManager.get('hero_banners');
                    banners = typeof raw === 'string' ? JSON.parse(raw) : (raw || []);

                    const pData = await window.ProductManager.getAll({ limit: 500 });
                    const pList = pData.products || [];
                    topProductsForFallback = pList.slice(0, 3);
                    pList.forEach(p => allProductsMap[p.id] = p);
                } catch(e) {
                    console.error("Erro ao carregar banners:", e);
                }

                if (banners && banners.length > 0) {
                    // Only render if not already server-rendered by Blade to avoid flickering
                    const hasSlides = bannerSlider.querySelectorAll('.hb-slide').length > 0;
                    if (!hasSlides) {
                        if (bannerPlaceholder) bannerPlaceholder.remove();
                        bannerSlider.innerHTML = banners.map((b, i) => {
                            const tag = b.link ? `a href="${b.link}"` : 'div';
                            let bannerUrl = b.url;
                            if (bannerUrl.includes('uploads/banners/')) {
                                const filename = bannerUrl.split('/').pop();
                                bannerUrl = (window.APP_URL || '').replace(/\/$/, '') + '/uploads/banners/' + filename;
                            }
                            return `<${tag} class="hb-slide ${i === 0 ? 'active' : ''}">
                                <img src="${bannerUrl}" alt="Banner ${i+1}">
                            </${tag.endsWith('a') ? 'a' : 'div'}>`;
                        }).join('');
                    } else if (bannerPlaceholder) {
                        bannerPlaceholder.remove();
                    }

                    const slides = bannerSlider.querySelectorAll('.hb-slide');
                    let current = 0;

                    function showBanner(idx) {
                        slides.forEach(s => s.classList.remove('active'));
                        slides[idx].classList.add('active');
                        current = idx;

                        const activeStyle = banners[idx].style || 'novo';
                        let promoIds = banners[idx].promo_ids || [];

                        if (promoIds.length === 0) {
                            if (banners[idx].promo1_id) promoIds.push(banners[idx].promo1_id);
                            if (banners[idx].promo2_id) promoIds.push(banners[idx].promo2_id);
                        }

                        let activeProducts = promoIds.map(id => allProductsMap[id]).filter(p => !!p);

                        if (activeProducts.length === 0 && topProductsForFallback.length > 0) {
                            activeProducts = topProductsForFallback.slice(0, 3);
                        }

                        const floatContainer = document.getElementById('dynamic-floating-promos');
                        if (floatContainer && activeProducts.length > 0) {
                            let htmlContent = '';
                            const isMobile = window.innerWidth <= 768;
                            
                            activeProducts.forEach((p, i) => {
                                // Permite que todos os 3 produtos flutuantes apareçam no mobile também, ajustando o espaçamento lateral
                                const rightOffsetPolaroid = isMobile ? `calc(2% + ${i * 90}px)` : `calc(6% + ${i * 160}px)`;
                                const rotations = [6, -4, 2];
                                const rot = rotations[i % 3];
                                const zPolaroid = 10 + i;
                                
                                // Nova lógica de posicionamento para o estilo Antigo/Clássico (separados nos cantos)
                                let posAntigo = '';
                                if (i === 0) {
                                    posAntigo = 'top: 15%; left: 5%;';
                                } else if (i === 1) {
                                    posAntigo = 'bottom: 15%; right: 5%;';
                                } else {
                                    posAntigo = 'top: 15%; right: 5%;';
                                }

                                htmlContent += `
                                <div id="floating-promo-${i}" class="style-${activeStyle}">
                                    <!-- Polaroid (Manteve o estilo atual) -->
                                    <a href="${window.APP_URL || ''}/detalhes/${p.id}" class="floating-polaroid layout-polaroid" style="bottom: 12%; right: ${rightOffsetPolaroid}; transform: rotate(${rot}deg); pointer-events: auto; z-index: ${zPolaroid}; ${isMobile ? 'width: 85px; font-size: 0.7rem; padding: 4px;' : ''}">
                                        <div style="position: relative; overflow: hidden; border-radius: 2px; height: ${isMobile ? '85px' : '145px'};">
                                            <img src="${p.image}" alt="${p.name}" style="width: 100%; height: 100%; object-fit: cover; background: #f8f9fa;">
                                            <div style="position: absolute; bottom: 0; left: 0; right: 0; padding: 25px 5px 5px; background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0) 100%); display: flex; align-items: flex-end; gap: 4px;">
                                                <span style="color: #ffffff; font-weight: 800; font-size: ${isMobile ? '0.75rem' : '1.1rem'}; line-height: 1; text-shadow: 0 1px 3px rgba(0,0,0,0.5);">R$${parseFloat(p.price).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</span>
                                            </div>
                                        </div>
                                    </a>
                                    <!-- Cápsula / Estilo Antigo (Redesenhado e Separado) -->
                                    <a href="${window.APP_URL || ''}/detalhes/${p.id}" class="floating-element layout-antigo" style="position: absolute; ${posAntigo} z-index: 10; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(10px); color: #ffffff; border-radius: var(--radius-md); padding: 5px 8px; box-shadow: 0 12px 35px rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1); text-decoration: none; display: flex; align-items: center; gap: 8px; pointer-events: auto; transition: all 0.3s ease; ${isMobile ? 'transform: scale(0.65); transform-origin: top left;' : ''}">
                                        <div style="width: ${isMobile ? '35px' : '45px'}; height: ${isMobile ? '35px' : '45px'}; background: #fff; border-radius: 6px; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">
                                            <img src="${p.image}" alt="${p.name}" style="max-width: 85%; max-height: 85%; object-fit: contain;">
                                        </div>
                                        <div style="text-align: left;">
                                            <span style="display: block; font-weight: 700; font-size: 0.8rem; color: var(--clr-primary); margin-bottom: 1px;">Destaque</span>
                                            <span style="display: block; font-weight: 800; font-size: 0.9rem; line-height: 1;">R$ ${parseFloat(p.price).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</span>
                                        </div>
                                    </a>
                                </div>
                                `;
                            });
                            
                            floatContainer.innerHTML = htmlContent;
                        }
                    }
                    
                    // Initialize the floating highlights for the first slide immediately
                    showBanner(0);

                    let bannerInterval = setInterval(() => {
                        showBanner((current + 1) % slides.length);
                    }, 5000);

                    if (bannerPrev) bannerPrev.addEventListener('click', () => {
                        showBanner((current - 1 + slides.length) % slides.length);
                        clearInterval(bannerInterval);
                        bannerInterval = setInterval(() => showBanner((current + 1) % slides.length), 5000);
                    });
                    if (bannerNext) bannerNext.addEventListener('click', () => {
                        showBanner((current + 1) % slides.length);
                        clearInterval(bannerInterval);
                        bannerInterval = setInterval(() => showBanner((current + 1) % slides.length), 5000);
                    });
                }
            }

            // Products Initialization (only if not already rendered by Blade)
            const gridContainer = document.getElementById('featured-products');
            const heroSlider = document.getElementById('hero-slider');
            const sliderDots = document.getElementById('slider-dots');

            // Detect if products are already present to avoid duplication
            const alreadyInGrid = gridContainer && gridContainer.querySelectorAll('.ali-style-card').length > 0;
            const alreadyInSlider = heroSlider && heroSlider.querySelectorAll('.slider-item').length > 0;

            if (!alreadyInGrid || !alreadyInSlider) {
                if (!alreadyInGrid && gridContainer) {
                    let skelHtml = '';
                    for(let i=0; i<4; i++) {
                        skelHtml += `
                            <div class="skeleton-card ali-style-card">
                                <div class="skeleton-img"></div>
                                <div class="skeleton-text" style="width: 70%;"></div>
                                <div class="skeleton-text" style="width: 40%; margin-bottom: 20px;"></div>
                                <div class="skeleton-price"></div>
                                <div class="skeleton-text" style="width: 90%; height: 35px; border-radius: 8px;"></div>
                            </div>`;
                    }
                    gridContainer.innerHTML = skelHtml;
                }
                
                try {
                    const data = await window.ProductManager.getAll({ limit: 8 });
                    const allProducts = data.products || [];

                    if (!alreadyInGrid && gridContainer) {
                        const topProducts = allProducts.slice(0, 4);
                        if (topProducts.length > 0) {
                            gridContainer.innerHTML = topProducts.map(product => {
                                const oldPrice = product.price * 1.45;
                                const discount = 31;
                                const soldCount = parseInt(product.sold_quantity || 0);
                                const isOutOfStock = parseInt(product.stock_quantity || 0) <= 0;
                                return `
                                <a href="detalhes/${product.id}" class="ali-style-card" id="prod-${product.id}">
                                    <!-- Promo Header Banner -->
                                    <div class="card-promo-header" style="background: var(--grad-primary); padding: 5px 12px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(0,0,0,0.05);">
                                        <span style="font-weight: 800; font-size: 0.85rem; color: #003838;">Combos de ofertas</span>
                                        <i class='bx bx-basket' style="color: #003838; font-size: 1.2rem; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));"></i>
                                    </div>

                                    <div class="ali-img-box">
                                        <img src="${product.image}" alt="${product.name}" loading="lazy">
                                    </div>
                                    <div class="ali-card-content">
                                        <div class="ali-prod-title">
                                            ${product.name}
                                        </div>
                                        
                                        <!-- New Image-Matched Price Layout -->
                                        <div style="display: flex; align-items: flex-start; gap: 8px; margin-top: 8px;">
                                            <div style="color: var(--clr-accent); font-size: 1.7rem; font-weight: 900; line-height: 1; letter-spacing: -1px;">
                                                <span style="font-size: 1.1rem; font-weight: 800; margin-right: 1px;">R$</span>${parseFloat(product.price).toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                                            </div>
                                            
                                            <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 5px; padding-top: 1px;">
                                                <span style="background: rgba(239, 68, 68, 0.08); color: var(--clr-accent); font-size: 0.65rem; font-weight: 800; padding: 3px 6px; border-radius: 4px; line-height: 1; white-space: nowrap;">Oferta destaque</span>
                                                <span style="color: #9ca3af; text-decoration: line-through; font-size: 0.75rem; font-weight: 600; line-height: 1;">R$ ${parseFloat(oldPrice).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</span>
                                            </div>
                                        </div>

                                        <!-- Add to cart and standard info -->
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                                                <div style="display: flex; align-items: center; gap: 4px; color: ${parseInt(product.stock_quantity || 0) <= 5 ? '#ef4444' : 'var(--clr-text-light)'};">
                                                    <i class='bx bx-archive' style="font-size: 10px;"></i>
                                                    <span style="font-size: 0.75rem;">${parseInt(product.stock_quantity || 0)} em estoque</span>
                                                </div>
                                            </div>
                                            <button class="ali-float-cart" style="margin-left: 0; width: 34px; height: 34px; transform: translateY(-5px);" onclick="event.preventDefault(); window.handleAddToCart('${product.id}')" title="Adicionar ao Carrinho">
                                                <i class='bx bx-cart-add'></i>
                                            </button>
                                        </div>

                                        <button class="ali-buy-btn" onclick="event.preventDefault(); window.handleBuyNow('${product.id}')">
                                            Comprar Agora
                                        </button>
                                    </div>
                                </a>
                            `; }).join('');
                        }
                    }

                    if (!alreadyInSlider && heroSlider && sliderDots) {
                        const slides = allProducts.filter(p => p.rating >= 4.8).slice(0, 5);
                        if (slides.length > 0) {
                            heroSlider.innerHTML = slides.map((p, i) => `
                                <div class="slider-item ${i === 0 ? 'active' : ''}" data-id="${p.id}" onclick="window.location.href='detalhes/${p.id}'" style="flex-direction: column; gap: 0.75rem; padding: 0.5rem;">
                                    <div style="width: 100%; height: 180px; display: flex; align-items: center; justify-content: center; background: #fff; border-radius: var(--radius-md); border: 1px solid var(--clr-border);">
                                        <img src="${p.image}" alt="${p.name}" style="max-height: 90%; max-width: 90%; object-fit: contain; filter: drop-shadow(0 10px 15px rgba(0,0,0,0.05));">
                                    </div>
                                    <div class="sidebar-caption" style="display: flex; flex-direction: column; width: 100%; text-align: left; gap: 8px;">
                                        <h4 class="sidebar-title" style="color: #fff; margin: 0; font-size: 0.95rem;">${p.name}</h4>
                                        
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <div style="color: var(--clr-primary); font-size: 1.4rem; font-weight: 900; line-height: 1; letter-spacing: -1px; text-shadow: 0 2px 10px rgba(0,212,212,0.3);">
                                                <span style="font-size: 0.85rem; font-weight: 800; margin-right: 1px;">R$</span>${parseFloat(p.price).toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                                            </div>
                                            <span style="background: rgba(0, 212, 212, 0.15); border: 1px solid rgba(0, 212, 212, 0.4); color: var(--clr-primary); font-size: 0.55rem; font-weight: 800; padding: 2px 4px; border-radius: 4px; line-height: 1; white-space: nowrap;">-31% OFF</span>
                                        </div>
                                        <span style="color: rgba(255,255,255,0.45); text-decoration: line-through; font-size: 0.7rem; font-weight: 500; line-height: 1; margin-top: -4px;">R$ ${parseFloat(p.price * 1.45).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</span>

                                        <button class="ali-buy-btn" onclick="event.stopPropagation(); window.handleBuyNow('${p.id}')" style="width: 100%; margin: 4px 0 0; padding: 0.5rem; font-size: 0.85rem; border-radius: var(--radius-full); background: var(--grad-primary); color: #003838; border: none; cursor: pointer; font-weight: 800; box-shadow: 0 4px 12px rgba(0,212,212,0.2);">Comprar</button>
                                    </div>
                                </div>
                            `).join('');
                        }
                    }
                } catch (e) {
                    console.error("Erro ao carregar produtos:", e);
                }
            }

            // Slider Logic (for Novidades slider on the right)
            if (heroSlider) {
                const items = heroSlider.querySelectorAll('.slider-item');
                if (items.length > 1) {
                    let currentSlide = 0;
                    function showSlide(idx) {
                        items.forEach(it => it.classList.remove('active'));
                        items[idx].classList.add('active');
                        currentSlide = idx;
                    }
                    setInterval(() => showSlide((currentSlide + 1) % items.length), 4000);
                }
            }
        });
