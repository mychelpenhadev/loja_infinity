@extends('layouts.app')

@push('styles')
<style>
    .details-grid {
        display: grid;
        grid-template-columns: 1.2fr 1fr;
        gap: 4rem;
        margin-top: 1rem;
        align-items: start;
    }
    .product-gallery {
        background-color: var(--clr-surface);
        border-radius: var(--radius-lg);
        padding: 3rem;
        box-shadow: var(--shadow-xl);
        border: 1px solid var(--clr-border);
        display: flex;
        align-items: center;
        justify-content: center;
        position: sticky;
        top: 120px;
        transition: var(--transition);
    }
    .product-gallery:hover {
        transform: scale(1.02);
        box-shadow: var(--shadow-2xl);
    }
    .product-gallery img {
        max-width: 100%;
        max-height: 500px;
        object-fit: contain;
        border-radius: var(--radius-md);
        filter: drop-shadow(0 20px 40px rgba(0,0,0,0.1));
    }
    .product-meta {
        margin-bottom: 2rem;
    }
    .category-tag {
        display: inline-block;
        background: var(--grad-blue);
        color: #fff;
        padding: 0.35rem 1rem;
        border-radius: var(--radius-full);
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 1.5rem;
        letter-spacing: 1px;
    }
    .product-title-large {
        font-size: 3rem;
        font-family: var(--font-display);
        margin-bottom: 1rem;
        line-height: 1.1;
        color: var(--clr-text);
    }
    .price-container {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        margin: 2rem 0;
        padding: 1.5rem;
        background: var(--clr-bg-alt);
        border-radius: var(--radius-lg);
        border: 1px solid var(--clr-border);
    }
    .price-large {
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--clr-accent);
        font-family: var(--font-display);
    }
    .price-off-tag {
        background: var(--clr-primary);
        color: #000;
        padding: 4px 12px;
        border-radius: var(--radius-full);
        font-weight: 700;
        font-size: 0.9rem;
    }
    .desc-text {
        color: var(--clr-text-light);
        font-size: 1.1rem;
        line-height: 1.8;
        margin-bottom: 2.5rem;
    }
    .cart-actions {
        display: flex;
        gap: 1.5rem;
        margin-top: 2.5rem;
        align-items: center;
    }
    .qty-controls {
        display: flex;
        align-items: center;
        background-color: var(--clr-surface);
        border: 1px solid var(--clr-border);
        border-radius: var(--radius-full);
        padding: 5px;
        box-shadow: var(--shadow-sm);
    }
    .qty-btn {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--clr-text);
        transition: var(--transition);
        border-radius: 50%;
        background: var(--clr-bg-alt);
    }
    .qty-btn:hover {
        background-color: var(--clr-primary);
        color: #000;
    }
    .qty-input {
        width: 50px;
        text-align: center;
        border: none;
        background: transparent;
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--clr-text);
    }
    .btn-buy-now {
        background: var(--grad-blue);
        color: #fff;
        border-radius: var(--radius-full);
        padding: 1rem 2.5rem;
        font-weight: 700;
        font-size: 1.1rem;
        box-shadow: var(--shadow-md);
        transition: var(--transition);
        flex: 2;
    }
    .btn-buy-now:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-lg);
        filter: brightness(1.1);
    }
    .btn-add-cart-outline {
        background: transparent;
        color: var(--clr-accent);
        border: 2px solid var(--clr-accent);
        border-radius: var(--radius-full);
        padding: 1rem 1.5rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: var(--transition);
    }
    .btn-add-cart-outline:hover {
        background: var(--clr-accent);
        color: #fff;
    }
    .trust-badges {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-top: 3rem;
        padding-top: 2rem;
        border-top: 1px solid var(--clr-border);
    }
    .badge-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.85rem;
        color: var(--clr-text-light);
    }
    .badge-item i {
        font-size: 1.5rem;
        color: var(--clr-primary);
    }
    @media (max-width: 992px) {
        .details-grid {
            grid-template-columns: 1fr;
            gap: 2rem;
        }
        .product-gallery {
            position: relative;
            top: 0;
            padding: 1.5rem;
        }
        .product-title-large {
            font-size: 1.85rem;
        }
        .cart-actions {
            flex-direction: column;
            align-items: stretch;
            gap: 1rem;
        }
        .qty-controls {
            width: 100%;
            justify-content: center;
        }
        .qty-input { flex: 1; }
        .btn-buy-now, .btn-add-cart-outline {
            width: 100%;
            padding: 1.15rem;
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
<main class="section">
    <div class="container">
        <a href="{{ url()->previous() == url()->current() ? url('/') : url()->previous() }}" id="back-link" style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--clr-text-light); margin-bottom: 2rem; transition: var(--transition);">
            <i class='bx bx-arrow-back'></i> <span id="back-text">Voltar</span>
        </a>

        <div class="details-grid">
            <div class="product-gallery">
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" id="main-product-image">
            </div>
            
            <div class="product-info-detail">
                <div class="product-meta">
                    @if($product->category)
                        <span class="category-tag">{{ $product->category }}</span>
                    @endif
                    <h1 class="product-title-large">{{ $product->name }}</h1>
                    
                    <div class="product-rating" style="display: flex; align-items: center; gap: 0.5rem; color: #F59E0B; margin-bottom: 0.5rem;">
                        <div style="display: flex;">
                            @for($i=1; $i<=5; $i++)
                                @if($i <= floor($product->rating))
                                    <i class='bx bxs-star'></i>
                                @elseif($i == floor($product->rating) + 1 && ($product->rating - floor($product->rating)) >= 0.5)
                                    <i class='bx bxs-star-half'></i>
                                @else
                                    <i class='bx bx-star'></i>
                                @endif
                            @endfor
                        </div>
                        <span style="color: var(--clr-text-light); font-size: 0.9rem;">({{ ($product->id % 50) + 10 }} avaliações)</span>
                    </div>

                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; font-size: 0.9rem; font-weight: 600;">
                        <span style="display: flex; align-items: center; gap: 4px; color: var(--clr-text-light);">
                            <i class='bx bx-rocket' style="color: var(--clr-accent);"></i> {{ $product->sold_quantity ?? 0 }} vendidos
                        </span>
                        <span style="display: flex; align-items: center; gap: 4px; color: {{ ($product->stock_quantity ?? 0) <= 5 ? '#ef4444' : '#10b981' }};">
                            <i class='bx bx-archive'></i> 
                            @if(($product->stock_quantity ?? 0) <= 0)
                                Esgotado
                            @elseif(($product->stock_quantity ?? 0) <= 5)
                                Últimas {{ $product->stock_quantity }} unidades!
                            @else
                                {{ $product->stock_quantity }} em estoque
                            @endif
                        </span>
                    </div>
                </div>

                <div class="price-container">
                    <div>
                        <span class="price-large">R$ {{ number_format($product->price, 2, ',', '.') }}</span>
                        @if($product->original_price && $product->original_price > $product->price)
                            <div style="color: var(--clr-text-light); text-decoration: line-through; font-size: 1rem; margin-top: 5px;">R$ {{ number_format($product->original_price, 2, ',', '.') }}</div>
                        @endif
                    </div>
                    @if($product->discount_percent && $product->discount_percent > 0)
                        <span class="price-off-tag">-{{ $product->discount_percent }}% OFF</span>
                    @endif
                </div>

                <div class="desc-text">
                    {{ $product->description ?? 'Um produto exclusivo selecionado pela Infinity Variedades para trazer mais cor e organização ao seu dia a dia. Qualidade premium garantida.' }}
                </div>

                <div class="cart-actions">
                    <div class="qty-controls">
                        <button class="qty-btn" id="qty-minus" aria-label="Diminuir"><i class='bx bx-minus'></i></button>
                        <input type="number" class="qty-input" id="qty-input" value="1" min="1" readonly>
                        <button class="qty-btn" id="qty-plus" aria-label="Aumentar"><i class='bx bx-plus'></i></button>
                    </div>
                    
                    <button class="btn-buy-now" onclick="handleBuyNowFromDetails({{ $product->id }})">
                        Comprar Agora
                    </button>

                    <button class="btn-add-cart-outline" onclick="handleAddToCartFromDetails({{ $product->id }})" title="Adicionar ao Carrinho">
                        <i class='bx bx-cart-add' style="font-size: 1.5rem;"></i>
                    </button>
                </div>

                <div class="trust-badges">
                    <div class="badge-item">
                        <i class='bx bx-shield-quarter'></i>
                        <div>
                            <strong style="display: block; color: var(--clr-text);">Garantia Infinity</strong>
                            <span>Compra 100% Segura</span>
                        </div>
                    </div>
                    <div class="badge-item">
                        <i class='bx bx-package'></i>
                        <div>
                            <strong style="display: block; color: var(--clr-text);">Envio Rápido</strong>
                            <span>Para todo o Brasil</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($product->video)
            <div class="video-container">
                <h3 style="margin-bottom: 1.5rem;">Vídeo do Produto</h3>
                <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: var(--radius-lg); box-shadow: var(--shadow-md);">
                    <iframe style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none;" src="{{ $product->video }}" allowfullscreen></iframe>
                </div>
            </div>
        @endif
    </div>
</main>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const minusBtn = document.getElementById('qty-minus');
        const plusBtn = document.getElementById('qty-plus');
        const qtyInput = document.getElementById('qty-input');

        if (minusBtn && plusBtn && qtyInput) {
            minusBtn.addEventListener('click', () => {
                let current = parseInt(qtyInput.value) || 1;
                if (current > 1) qtyInput.value = current - 1;
            });
            plusBtn.addEventListener('click', () => {
                let current = parseInt(qtyInput.value) || 1;
                qtyInput.value = current + 1;
            });
        }
    });

    window.handleAddToCartFromDetails = function(id) {
        const qty = parseInt(document.getElementById('qty-input').value) || 1;
        if(window.CartManager) {
            window.CartManager.add(id.toString(), qty);
            window.showToast('Produto adicionado ao carrinho!', 'success');
        } else {
            console.warn('CartManager não carregado.');
        }
    };

    window.handleBuyNowFromDetails = function(id) {
        const qty = parseInt(document.getElementById('qty-input').value) || 1;
        if(window.CartManager) {
            window.CartManager.add(id.toString(), qty);
            window.location.href = "{{ url('/carrinho') }}";
        }
    };
</script>
@endpush
