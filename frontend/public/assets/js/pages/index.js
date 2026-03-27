        document.addEventListener('DOMContentLoaded', async () => {
            // Banner Slider
            const bannerSlider = document.getElementById('hero-banner-slider');
            const bannerDots = document.getElementById('hb-dots');
            const bannerPrev = document.getElementById('hb-prev');
            const bannerNext = document.getElementById('hb-next');
            const bannerPlaceholder = document.getElementById('hero-banner-placeholder');

            if (bannerSlider) {
                let banners = [];
                try {
                    const raw = window.ConfigManager.get('hero_banners');
                    banners = JSON.parse(raw) || [];
                } catch(e) {}

                if (banners.length > 0) {
                    if (bannerPlaceholder) bannerPlaceholder.remove();
                    bannerSlider.innerHTML = banners.map((b, i) => {
                        const tag = b.link ? `a href="${b.link}"` : 'div';
                        return `<${tag} class="hb-slide ${i === 0 ? 'active' : ''}">
                            <img src="${b.url}" alt="Banner ${i+1}">
                        </${tag.endsWith('a') ? 'a' : 'div'}>`;
                    }).join('');

                    if (bannerDots) {
                        bannerDots.innerHTML = banners.map((_, i) =>
                            `<span class="hb-dot ${i === 0 ? 'active' : ''}" data-index="${i}"></span>`
                        ).join('');
                    }

                    const slides = bannerSlider.querySelectorAll('.hb-slide');
                    const dots = bannerDots ? bannerDots.querySelectorAll('.hb-dot') : [];
                    let current = 0;

                    function showBanner(idx) {
                        slides.forEach(s => s.classList.remove('active'));
                        dots.forEach(d => d.classList.remove('active'));
                        slides[idx].classList.add('active');
                        if (dots[idx]) dots[idx].classList.add('active');
                        current = idx;
                    }

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
                    dots.forEach(dot => dot.addEventListener('click', () => {
                        showBanner(parseInt(dot.dataset.index));
                        clearInterval(bannerInterval);
                        bannerInterval = setInterval(() => showBanner((current + 1) % slides.length), 5000);
                    }));
                }
            }

            const container = document.getElementById('featured-products');
            const heroSlider = document.getElementById('hero-slider');
            const sliderDots = document.getElementById('slider-dots');

            const alreadyRendered = container && container.children.length > 2;
            let data = { products: [] };
            try {
                data = await window.ProductManager.getAll({ limit: 8 });
            } catch (e) {
                console.error("Erro ao carregar produtos iniciais:", e);
                if (container) container.innerHTML = '<p style="color: #EF4444; text-align: center; padding: 2rem;">Não foi possível carregar os produtos em destaque.</p>';
            }
            const allProducts = data.products || [];
            if (!alreadyRendered && container) {
                const products = allProducts.slice(0, 4);
                if (products.length === 0) {
                    container.innerHTML = '<p>Nenhum produto em destaque encontrado.</p>';
                } else {
                    container.innerHTML = '';
                    products.forEach(product => {
                        const isNew = product.rating >= 4.8;
                        container.innerHTML += `
                            <div class="product-card" id="prod-${product.id}">
                                ${isNew ? '<span class="product-badge">Novidade</span>' : ''}
                                <div class="product-actions">
                                    <button class="icon-btn" onclick="window.location.href='detalhes.html?id=${product.id}'" title="Ver Detalhes">
                                        <i class='bx bx-show'></i>
                                    </button>
                                </div>
                                <a href="detalhes.html?id=${product.id}" class="product-image-container">
                                    <img src="${product.image}" alt="${product.name}" loading="lazy" decoding="async">
                                </a>
                                <div class="product-info">
                                    <span class="product-category">${product.category}</span>
                                    <a href="detalhes.html?id=${product.id}" class="product-title">${product.name}</a>
                                    <div class="product-rating">
                                        ${window.generateStars(product.rating)}
                                        <span style="color: var(--clr-text-light); margin-left: auto; font-size: 0.75rem;">(${Math.floor(Math.random() * 50) + 10})</span>
                                    </div>
                                    <div class="product-footer">
                                        <span class="product-price">${window.formatCurrency(product.price)}</span>
                                        <div style="display: flex; gap: 0.5rem;">
                                            <button class="btn" style="padding: 0.5rem 0.75rem; font-size: 0.875rem; background-color: var(--clr-primary); color: white;" onclick="window.handleBuyNow('${product.id}')">
                                                Comprar
                                            </button>
                                            <button class="btn-add" style="padding: 0.5rem 0.75rem; font-size: 0.875rem;" onclick="window.handleAddToCart('${product.id}')" title="Adicionar ao Carrinho">
                                                <i class='bx bx-cart-add'></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                }
            }

            if (heroSlider && sliderDots) {
                if (!alreadyRendered) {
                    const novidades = allProducts.filter(p => p.rating >= 4.8).slice(0, 5);
                    if (novidades.length > 0) {
                        heroSlider.innerHTML = novidades.map((p, index) => `
                            <div class="slider-item ${index === 0 ? 'active' : ''}" data-id="${p.id}" onclick="window.location.href='detalhes.html?id=${p.id}'">
                                <img src="${p.image}" alt="${p.name}">
                                <div class="slider-caption">
                                    <h3 class="slider-title">${p.name}</h3>
                                    <span class="slider-price">${window.formatCurrency(p.price)}</span>
                                </div>
                            </div>
                        `).join('');
                        sliderDots.innerHTML = novidades.map((_, index) => `
                            <span class="dot ${index === 0 ? 'active' : ''}" data-index="${index}"></span>
                        `).join('');
                    }
                }
                const items = heroSlider.querySelectorAll('.slider-item');
                const dots = sliderDots.querySelectorAll('.dot');
                if (items.length > 0) {
                    let currentSlide = 0;
                    function showSlide(index) {
                        items.forEach(item => item.classList.remove('active'));
                        dots.forEach(dot => dot.classList.remove('active'));
                        items[index].classList.add('active');
                        dots[index].classList.add('active');
                        currentSlide = index;
                    }
                    let slideInterval = setInterval(() => {
                        let next = (currentSlide + 1) % items.length;
                        showSlide(next);
                    }, 5000);
                    dots.forEach(dot => {
                        dot.addEventListener('click', () => {
                            const index = parseInt(dot.getAttribute('data-index'));
                            showSlide(index);
                            clearInterval(slideInterval);
                            slideInterval = setInterval(() => {
                                let next = (currentSlide + 1) % items.length;
                                showSlide(next);
                            }, 5000);
                        });
                    });
                }
            }

            const notifBtn = document.getElementById('notification-btn');
            const notifDropdown = document.getElementById('notification-dropdown');
            const notifBadge = document.getElementById('notification-badge');
            const notifList = document.getElementById('notification-list');
            const markReadBtn = document.getElementById('mark-read-btn');
            if (notifBtn) {
                const latest = allProducts.slice(-2).reverse();
                const promos = allProducts.filter(p => p.category && p.category.toLowerCase().includes('promo'));
                let notifsHTML = '';
                let count = 0;
                latest.forEach(p => {
                    notifsHTML += `
                        <a href="detalhes.html?id=${p.id}" class="notif-item unread">
                            <div class="notif-icon">
                                <img src="${p.image}" alt="">
                            </div>
                            <div class="notif-content">
                                <span class="notif-title">Chegou Novidade!</span>
                                <span class="notif-desc">${p.name} foi adicionado à loja.</span>
                                <span class="notif-time">Agora mesmo</span>
                            </div>
                        </a>
                    `;
                    count++;
                });
                if (promos.length > 0) {
                    const p = promos[0];
                    notifsHTML += `
                        <a href="detalhes.html?id=${p.id}" class="notif-item unread">
                            <div class="notif-icon" style="background: rgba(239,68,68,0.1); color: #EF4444;">
                                <i class='bx bxs-hot'></i>
                            </div>
                            <div class="notif-content">
                                <span class="notif-title">Oferta em Destaque!</span>
                                <span class="notif-desc">${p.name}</span>
                                <span class="notif-time">Promoção</span>
                            </div>
                        </a>
                    `;
                    count++;
                }
                if(count === 0) {
                    if(notifList) notifList.innerHTML = `<div style="padding: 2rem; text-align: center; color: var(--clr-text-light);">Tudo lido por aqui! 🎉</div>`;
                    if(notifBadge) notifBadge.style.display = 'none';
                } else {
                    if(notifList) notifList.innerHTML = notifsHTML;
                    if(notifBadge) {
                        notifBadge.textContent = count;
                        notifBadge.style.display = 'flex';
                    }
                }
                if(notifBtn) {
                    notifBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        notifDropdown.classList.toggle('active');
                    });
                }
                document.addEventListener('click', (e) => {
                    if(notifBtn && !notifBtn.contains(e.target) && notifDropdown && !notifDropdown.contains(e.target)) {
                        notifDropdown.classList.remove('active');
                    }
                });
                if(markReadBtn) {
                    markReadBtn.addEventListener('click', () => {
                        notifList.innerHTML = `<div style="padding: 2rem; text-align: center; color: var(--clr-text-light);">Tudo limpo! 🎉</div>`;
                        notifBadge.style.display = 'none';
                    });
                }
            }
        });
