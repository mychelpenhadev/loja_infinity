@extends('layouts.app')

@section('content')
<section class="hero" style="padding: 1rem 0; min-height: auto; display: flex; align-items: center; position: relative;">
    <!-- Animated Background Glows -->
    <div style="position: absolute; top: -10%; right: -5%; width: 50vw; height: 50vw; background: radial-gradient(circle, rgba(var(--clr-primary-rgb), 0.15) 0%, transparent 70%); filter: blur(60px); z-index: -1;"></div>
    <div style="position: absolute; bottom: -10%; left: -5%; width: 40vw; height: 40vw; background: radial-gradient(circle, rgba(var(--clr-secondary-rgb), 0.15) 0%, transparent 70%); filter: blur(60px); z-index: -1;"></div>

    <div class="container">
        <style>
            .hero-banner-row {
                display: grid;
                grid-template-columns: 1.8fr 1fr;
                gap: 2rem;
                align-items: flex-start;
            }
            @media (max-width: 992px) {
                .hero-banner-row {
                    grid-template-columns: 1fr;
                    gap: 1.5rem;
                }
                .hero-banner-slider-wrap {
                    height: 380px !important;
                }
                .hero-image {
                    height: auto !important;
                    min-height: 480px;
                }
            }
            @media (max-width: 576px) {
                .hero-banner-slider-wrap {
                    height: 280px !important;
                }
                .hero-image {
                    height: auto !important;
                    min-height: 420px;
                    padding: 1.2rem !important;
                }
                .hero { padding: 0.5rem 0 !important; }
            }
        </style>
        <div class="hero-banner-row">
            <!-- Main Discovery Slider -->
            <div class="hero-banner-slider-wrap glass-panel" style="border-radius: var(--radius-lg); overflow: hidden; height: 440px; position: relative; border: 1px solid rgba(255,255,255,0.1);">
                
                    <style>
                    .floating-polaroid {
                        position: absolute; z-index: 10;
                        background: #ffffff; padding: 6px; border-radius: 2px;
                        box-shadow: 0 15px 35px rgba(0,0,0,0.15), 0 5px 15px rgba(0,0,0,0.1);
                        text-decoration: none; 
                        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                        display: block; width: 145px;
                    }
                    .floating-polaroid:hover {
                        transform: rotate(0deg) scale(1.05) translateY(-5px) !important;
                        z-index: 20 !important;
                    }

                    .style-novo .layout-antigo { display: none !important; }
                    .style-novo .layout-polaroid { display: block !important; }
                    .style-antigo .layout-polaroid { display: none !important; }
                    .style-antigo .layout-antigo { display: flex !important; }
                    
                    .layout-antigo:hover {
                        transform: scale(1.05) translateY(-5px) !important;
                        background: rgba(15, 23, 42, 0.95) !important;
                        box-shadow: 0 15px 45px rgba(0,0,0,0.6) !important;
                        z-index: 30 !important;
                    }
                    </style>

                    <!-- Javascript will inject floating promos here -->
                    <div id="dynamic-floating-promos" style="position: absolute; inset: 0; pointer-events: none; z-index: 10;">
                        <!-- The <a> tags will have pointer-events: auto -->
                    </div>

                <div class="hero-banner-slider" id="hero-banner-slider" style="height: 100%;">
                    @if(isset($banners) && count($banners) > 0)
                        @foreach($banners as $index => $b)
                            @php
                                $tag = !empty($b['link']) ? 'a' : 'div';
                                $href = !empty($b['link']) ? 'href="' . $b['link'] . '"' : '';
                            @endphp
                            <{{ $tag }} {{ $href }} class="hb-slide {{ $index === 0 ? 'active' : '' }}">
                                @php
                                    $bannerUrl = $b['url'];
                                    // If URL is absolute but points to another domain (like localhost), try to fix it
                                    if (str_contains($bannerUrl, 'uploads/banners/')) {
                                        $bannerUrl = asset('uploads/banners/' . basename($bannerUrl));
                                    }
                                @endphp
                                <img src="{{ $bannerUrl }}" alt="Banner {{ $index + 1 }}" 
                                     {{ $index === 0 ? 'loading="eager" fetchpriority="high"' : 'loading="lazy"' }}>
                            </{{ $tag }}>
                        @endforeach
                    @else
                        <div class="hero-banner-placeholder" id="hero-banner-placeholder" style="height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; background: linear-gradient(135deg, #0a1628 0%, #001a1a 100%);">
                            <img src="{{ asset('assets/img/logoPNG.png') }}" alt="Infinity Variedades" style="width: 70%; max-width: 380px; filter: brightness(0) invert(1) drop-shadow(0 0 30px rgba(0,212,212,0.4)); animation: logoPulse 3s ease-in-out infinite;">
                            <p style="color: var(--clr-text-light); margin-top: 1.5rem; font-size: 0.95rem; opacity: 0.7;">Explore o que há de mais inovador em papelaria e criatividade.</p>
                        </div>
                    @endif
                </div>
                <button class="hb-arrow hb-arrow-left" id="hb-prev" aria-label="Anterior" style="background: rgba(255,255,255,0.8); backdrop-filter: blur(4px);"><i class='bx bx-chevron-left'></i></button>
                <button class="hb-arrow hb-arrow-right" id="hb-next" aria-label="Próximo" style="background: rgba(255,255,255,0.8); backdrop-filter: blur(4px);"><i class='bx bx-chevron-right'></i></button>
            </div>

            <!-- Side Product Preview -->
            <style>
                @media (max-width: 768px) {
                    .hero-image { padding: 1rem !important; }
                    .hero-slider { height: 350px !important; margin-bottom: 0 !important; }
                    .slider-item { gap: 0.8rem !important; }
                    .sidebar-image-box { height: 160px !important; }
                    .sidebar-caption { gap: 0.25rem !important; }
                    .sidebar-title { font-size: 0.9rem !important; height: 2.6em !important; }
                    .sidebar-bottom-btn { padding-top: 0.75rem !important; }
                }
            </style>

            <div class="hero-image glass-panel bg-dark-premium" style="border-radius: var(--radius-lg); overflow: hidden; display: flex; flex-direction: column; position: relative;">
                
                <!-- Header -->
                <div style="display: flex; align-items: center; gap: 0.6rem; padding: 1rem 1.25rem 0.75rem; position: relative; z-index: 2;">
                    <h3 style="font-family: var(--font-display); font-size: 1.1rem; margin: 0; color: #e0fafa; font-weight: 700;">Lançamentos</h3>
                    <span style="font-size: 0.6rem; background: var(--grad-primary); color: #003838; padding: 3px 8px; border-radius: var(--radius-full); font-weight: 800; white-space: nowrap; letter-spacing: 0.05em;">NEW</span>
                </div>
                
                <!-- Full-image Slider -->
                <div class="hero-slider" id="hero-slider" style="flex: 1; box-shadow: none; border: none; background: transparent;">
                    @forelse($sliderProducts as $index => $p)
                        <div class="slider-item {{ $index === 0 ? 'active' : '' }}" data-id="{{ $p->id }}"
                             onclick="window.location.href='{{ url('/detalhes/' . $p->id) }}'">

                            
                            <!-- Full BG Image -->
                            <img src="{{ $p->image }}" alt="{{ $p->name }}"
                                 style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: contain; background: #fff;">
                            
                            <!-- Gradient Overlay -->
                            <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.88) 0%, rgba(0,0,0,0.3) 50%, transparent 100%);"></div>
                            
                            <!-- Info Overlay at Bottom -->
                            <div style="position: absolute; bottom: 0; left: 0; right: 0; padding: 1rem 1.1rem; display: flex; align-items: flex-end; justify-content: space-between; gap: 0.75rem;">
                                <div style="flex: 1; min-width: 0;">
                                    <p style="margin: 0 0 4px; font-size: 0.85rem; font-weight: 600; color: rgba(255,255,255,0.9); line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">{{ $p->name }}</p>
                                    <div style="display: flex; align-items: baseline; gap: 3px;">
                                        <span style="color: var(--clr-primary); font-size: 0.8rem; font-weight: 800;">R$</span>
                                        <span style="color: var(--clr-primary); font-size: 1.35rem; font-weight: 900; letter-spacing: -0.5px; text-shadow: 0 0 12px rgba(0,212,212,0.4);">{{ number_format($p->price, 2, ',', '.') }}</span>
                                    </div>
                                </div>
                                <!-- Cart Icon Button -->
                                <button onclick="event.stopPropagation(); window.handleAddToCart('{{ $p->id }}')"
                                        style="width: 44px; height: 44px; border-radius: 50%; background: var(--grad-primary); color: #003838; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; box-shadow: 0 4px 15px rgba(0,212,212,0.4); transition: transform 0.2s ease;"
                                        onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                                    <i class='bx bx-cart-add'></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="slider-loading"><i class='bx bx-loader-alt bx-spin'></i></div>
                    @endforelse
                </div>


            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-header" style="text-align: center; margin-bottom: 1rem;">
            <p class="section-subtitle" style="font-size: 1.1rem; color: var(--clr-text-light); opacity: 0.9;">Explore categorias selecionadas com calma e carinho.</p>
        </div>

        <div class="home-category-grid">
            @php
                $cats = [
                    ['id' => 'canetas', 'label' => 'Canetas', 'icon' => 'bx-pen', 'color' => '#3b82f6'],
                    ['id' => 'cadernos', 'label' => 'Cadernos', 'icon' => 'bx-book-bookmark', 'color' => '#10b981'],
                    ['id' => 'adesivos', 'label' => 'Adesivos', 'icon' => 'bx-sticker', 'color' => '#f59e0b'],
                    ['id' => 'mochilas', 'label' => 'Mochilas', 'icon' => 'bx-briefcase', 'color' => '#ef4444'],
                    ['id' => 'office', 'label' => 'Escritório', 'icon' => 'bx-buildings', 'color' => '#8b5cf6'],
                    ['id' => 'ofertas', 'label' => 'Ofertas', 'icon' => 'bx-purchase-tag', 'color' => '#ec4899']
                ];
            @endphp
            @foreach($cats as $c)
            <a href="{{ url('/produtos?cat=' . $c['id']) }}" class="home-category-card glass-panel">
                <div class="home-category-icon" style="background: {{ $c['color'] }}15; color: {{ $c['color'] }}; box-shadow: 0 8px 16px {{ $c['color'] }}10;">
                    <i class='bx {{ $c['icon'] }}'></i>
                </div>
                <span class="home-category-label">{{ $c['label'] }}</span>
            </a>
            @endforeach
        </div>
        
        <div class="product-grid" id="featured-products">
            @forelse($featuredProducts->take(12) as $index => $product)
                @php
                    $showDiscount = $product->original_price && $product->original_price > $product->price;
                    $soldCount = $product->sold_quantity ?? 0;
                @endphp
                <a href="{{ url('/detalhes/' . $product->id) }}" class="ali-style-card" id="prod-{{ $product->id }}" data-index="{{ $index }}">
                    <!-- Promo Header Banner -->
                    @if($product->category === 'promocoes')
                        <div class="card-promo-header" style="background: var(--grad-primary); padding: 5px 12px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(0,0,0,0.05);">
                            <span style="font-weight: 800; font-size: 0.85rem; color: #003838;">Promoção</span>
                            <i class='bx bx-basket' style="color: #003838; font-size: 1.2rem; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));"></i>
                        </div>
                    @elseif($product->category === 'novidades')
                        <div class="card-promo-header" style="background: var(--grad-blue); padding: 5px 12px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(0,0,0,0.05);">
                            <span style="font-weight: 800; font-size: 0.85rem; color: #fff;">Novidades</span>
                            <i class='bx bx-star' style="color: #fff; font-size: 1.2rem; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));"></i>
                        </div>
                    @endif

                    <div class="ali-img-box">
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" {{ $index < 4 ? 'loading=eager' : 'loading=lazy' }}>
                    </div>
                    <div class="ali-card-content">
                        <div class="ali-prod-title">{{ $product->name }}</div>
                        
                        <!-- New Image-Matched Price Layout -->
                        <div style="display: flex; align-items: flex-start; gap: 8px; margin-top: 8px;">
                            <div style="color: var(--clr-accent); font-size: 1.7rem; font-weight: 900; line-height: 1; letter-spacing: -1px;">
                                <span style="font-size: 1.1rem; font-weight: 800; margin-right: 1px;">R$</span>{{ number_format($product->price, 2, ',', '.') }}
                            </div>
                            
                            <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 5px; padding-top: 1px;">
                                @if($product->discount_percent && $product->discount_percent > 0)
                                    <span style="background: rgba(239, 68, 68, 0.08); color: var(--clr-accent); font-size: 0.65rem; font-weight: 800; padding: 3px 6px; border-radius: 4px; line-height: 1; white-space: nowrap;">-{{ $product->discount_percent }}% OFF</span>
                                @endif
                                @if($showDiscount)
                                    <span style="color: #9ca3af; text-decoration: line-through; font-size: 0.75rem; font-weight: 600; line-height: 1;">R$ {{ number_format($product->original_price, 2, ',', '.') }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Add to cart and standard info -->
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                                <div style="display: flex; align-items: center; gap: 4px; color: {{ ($product->stock_quantity <= 5 && $product->stock_quantity > 0) ? '#ef4444' : 'var(--clr-text-light)' }}; font-size: 0.75rem;">
                                    @if($product->stock_quantity <= 5 && $product->stock_quantity > 0)
                                        <i class='bx bxs-hot' style="font-size: 10px;"></i>
                                        <span style="font-weight: 700;">Últimas {{ $product->stock_quantity }} unidades!</span>
                                    @else
                                        <i class='bx bx-check-double' style="font-size: 10px; color: #10b981;"></i>
                                        <span>{{ number_format($soldCount, 0, ',', '.') }}+ vendidos</span>
                                    @endif
                                </div>
                            <button class="ali-float-cart" style="margin-left: 0; width: 34px; height: 34px; transform: translateY(-5px);" onclick="event.preventDefault(); window.handleAddToCart('{{ $product->id }}')">
                                <i class='bx bx-cart-add'></i>
                            </button>
                        </div>
                        <button class="ali-buy-btn" style="margin-top: auto;" onclick="event.preventDefault(); window.handleBuyNow('{{ $product->id }}')">
                            Comprar Agora
                        </button>
                    </div>
                </a>
            @empty
                <div style="grid-column: 1/-1; text-align: center; padding: 4rem 0; opacity: 0.5;">
                    <i class='bx bx-package' style="font-size: 3rem; margin-bottom: 1rem;"></i>
                    <p>Nenhum produto em destaque encontrado no momento.</p>
                </div>
            @endforelse
        </div>
        
        @if ($featuredProducts->count() >= 12)
        <div style="text-align: center; margin-top: 1.5rem;">
            <button id="load-more-btn" class="glass-panel" onclick="window.location.href='{{ url('/produtos') }}'" style="background: var(--clr-bg); color: var(--clr-text); padding: 0.75rem 2.5rem; border: 1px solid var(--clr-border); border-radius: var(--radius-full); font-weight: 700; font-family: var(--font-display); cursor: pointer; transition: var(--transition); display: inline-flex; align-items: center; gap: 0.5rem;">
                Explorar Mais Produtos <i class='bx bx-chevron-down' style="font-size: 1.25rem;"></i>
            </button>
        </div>
        @endif
    </div>
</section>

<!-- Extra discovery section: Mais Desejados -->
<section class="section bg-dark-premium" style="padding: 2.5rem 0;">
    <div class="container">
        <div class="section-header discover-header">
            <div class="discover-header-text">
                <h2 class="discover-title">Mais Desejados</h2>
                <p class="discover-subtitle">Os favoritos da comunidade Infinity neste mês</p>
            </div>
            <a href="{{ url('/produtos') }}" class="discover-link">Ver Coleção <i class='bx bx-right-arrow-alt'></i></a>
        </div>

        <div class="product-grid">
            @foreach($featuredProducts->slice(12, 8) as $product)
                @php
                    $showDiscountSide = $product->original_price && $product->original_price > $product->price;
                    $soldCountSide = $product->sold_quantity ?? 0;
                @endphp
                <a href="{{ url('/detalhes/' . $product->id) }}" class="ali-style-card">
                    <!-- Promo Header Banner -->
                    @if($product->category === 'promocoes')
                        <div class="card-promo-header" style="background: var(--grad-primary); padding: 5px 12px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(0,0,0,0.05);">
                            <span style="font-weight: 800; font-size: 0.85rem; color: #003838;">Promoção</span>
                            <i class='bx bx-basket' style="color: #003838; font-size: 1.2rem; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));"></i>
                        </div>
                    @elseif($product->category === 'novidades')
                        <div class="card-promo-header" style="background: var(--grad-blue); padding: 5px 12px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(0,0,0,0.05);">
                            <span style="font-weight: 800; font-size: 0.85rem; color: #fff;">Novidades</span>
                            <i class='bx bx-star' style="color: #fff; font-size: 1.2rem; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));"></i>
                        </div>
                    @endif

                    <div class="ali-img-box">
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy">
                    </div>
                    <div class="ali-card-content">
                        <div class="ali-prod-title">{{ $product->name }}</div>
                        
                        <!-- New Image-Matched Price Layout -->
                        <div style="display: flex; align-items: flex-start; gap: 8px; margin-top: 8px;">
                            <div style="color: var(--clr-accent); font-size: 1.7rem; font-weight: 900; line-height: 1; letter-spacing: -1px;">
                                <span style="font-size: 1.1rem; font-weight: 800; margin-right: 1px;">R$</span>{{ number_format($product->price, 2, ',', '.') }}
                            </div>
                            
                            <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 5px; padding-top: 1px;">
                                @if($product->discount_percent && $product->discount_percent > 0)
                                    <span style="background: rgba(239, 68, 68, 0.08); color: var(--clr-accent); font-size: 0.65rem; font-weight: 800; padding: 3px 6px; border-radius: 4px; line-height: 1; white-space: nowrap;">-{{ $product->discount_percent }}% OFF</span>
                                @endif
                                @if($showDiscountSide)
                                    <span style="color: #9ca3af; text-decoration: line-through; font-size: 0.75rem; font-weight: 600; line-height: 1;">R$ {{ number_format($product->original_price, 2, ',', '.') }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Add to cart and standard info -->
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                            <div class="ali-rating-sold" style="margin: 0;">
                                <div style="display: flex; color: #ffd700;">
                                    <i class='bx bxs-star' style="font-size: 10px;"></i>
                                    <span style="font-weight: 700; margin-left: 2px; color: var(--clr-text);">{{ number_format($product->rating, 1) }}</span>
                                </div>
                                <span>| {{ number_format($soldCountSide, 0, ',', '.') }}+ vendidos</span>
                            </div>
                            <button class="ali-float-cart" style="margin-left: 0; width: 34px; height: 34px; transform: translateY(-5px);" onclick="event.preventDefault(); window.handleAddToCart('{{ $product->id }}')">
                                <i class='bx bx-cart-add'></i>
                            </button>
                        </div>
                        <button class="ali-buy-btn" style="margin-top: auto; background: var(--grad-blue); color: #fff;" onclick="event.preventDefault(); window.handleBuyNow('{{ $product->id }}')">
                            Ver Detalhes
                        </button>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>


@endsection

@push('scripts')
<script src="{{ asset('assets/js/pages/index.js?v=' . time()) }}"></script>
@endpush
