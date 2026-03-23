        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('featured-products');
            const products = window.ProductManager.getAll().slice(0, 4); 
            
            if (products.length === 0) {
                container.innerHTML = '<p>Nenhum produto encontrado.</p>';
                return;
            }

            products.forEach(product => {
                const isNew = product.rating >= 4.8;
                container.innerHTML += `
                    <div class="product-card">
                        ${isNew ? '<span class="product-badge">Novidade</span>' : ''}
                        
                        <div class="product-actions">
                            <button class="icon-btn" onclick="window.location.href='detalhes.html?id=${product.id}'" title="Ver Detalhes">
                                <i class='bx bx-show'></i>
                            </button>
                            <button class="icon-btn" title="Favoritar">
                                <i class='bx bx-heart'></i>
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

            // Hero Slider Logic
            const heroSlider = document.getElementById('hero-slider');
            const sliderDots = document.getElementById('slider-dots');
            
            if (heroSlider && sliderDots) {
                const allP = window.ProductManager.getAll();
                // Filter for "Novidades" (rating >= 4.8)
                const novidades = allP.filter(p => p.rating >= 4.8).slice(0, 5);
                
                if (novidades.length > 0) {
                    heroSlider.innerHTML = novidades.map((p, index) => `
                        <div class="slider-item ${index === 0 ? 'active' : ''}" data-id="${p.id}" onclick="window.location.href='detalhes.html?id=${p.id}'">
                            <img src="${p.image}" alt="${p.name}">
                        </div>
                    `).join('');
                    
                    sliderDots.innerHTML = novidades.map((_, index) => `
                        <span class="dot ${index === 0 ? 'active' : ''}" data-index="${index}"></span>
                    `).join('');
                    
                    let currentSlide = 0;
                    const items = heroSlider.querySelectorAll('.slider-item');
                    const dots = sliderDots.querySelectorAll('.dot');
                    
                    function showSlide(index) {
                        items.forEach(item => item.classList.remove('active'));
                        dots.forEach(dot => dot.classList.remove('active'));
                        
                        items[index].classList.add('active');
                        dots[index].classList.add('active');
                        currentSlide = index;
                    }
                    
                    // Auto-slide
                    let slideInterval = setInterval(() => {
                        let next = (currentSlide + 1) % items.length;
                        showSlide(next);
                    }, 5000);
                    
                    // Dot clicks
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

            // Lógica de Notificações
            const notifBtn = document.getElementById('notification-btn');
            const notifDropdown = document.getElementById('notification-dropdown');
            const notifBadge = document.getElementById('notification-badge');
            const notifList = document.getElementById('notification-list');
            const markReadBtn = document.getElementById('mark-read-btn');

            if (notifBtn) {
                const allP = window.ProductManager.getAll();
                const latest = allP.slice(-2).reverse(); 
                const promos = allP.filter(p => p.category && p.category.toLowerCase().includes('promo'));
                
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
                                <span class="notif-time">Testado hoje</span>
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
                                <span class="notif-time">Agora mesmo</span>
                            </div>
                        </a>
                    `;
                    count++;
                }

                if(count === 0) {
                    notifList.innerHTML = `<div style="padding: 2rem; text-align: center; color: var(--clr-text-light);">Tudo lido por aqui! 🎉</div>`;
                    notifBadge.style.display = 'none';
                } else {
                    notifList.innerHTML = notifsHTML;
                    notifBadge.textContent = count;
                    notifBadge.style.display = 'flex';
                }

                notifBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    notifDropdown.classList.toggle('active');
                });

                document.addEventListener('click', (e) => {
                    if(!notifBtn.contains(e.target) && !notifDropdown.contains(e.target)) {
                        notifDropdown.classList.remove('active');
                    }
                });

                markReadBtn.addEventListener('click', () => {
                    notifList.innerHTML = `<div style="padding: 2rem; text-align: center; color: var(--clr-text-light);">Tudo limpo! 🎉</div>`;
                    notifBadge.style.display = 'none';
                });
            }
        });
