@extends('layouts.app')

@push('styles')
<style>
    :root {
        --glass-bg: rgba(0, 56, 56, 0.4);
        --glass-border: rgba(0, 212, 212, 0.1);
    }

    .cart-layout {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 2.5rem;
        margin-top: 3rem;
        align-items: start;
    }

    /* --- PREMIUM ITEM CONTAINER --- */
    .cart-items-container {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .cart-item {
        background: var(--glass-bg);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border: 1px solid var(--glass-border);
        border-radius: 24px;
        padding: 1.5rem;
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 2rem;
        align-items: center;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .cart-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: var(--grad-primary);
        opacity: 0;
        transition: opacity 0.3s;
    }

    .cart-item:hover {
        transform: translateY(-5px) scale(1.01);
        border-color: rgba(0, 212, 212, 0.3);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
    }

    .cart-item:hover::before {
        opacity: 1;
    }

    .cart-item-img-wrapper {
        width: 120px;
        height: 120px;
        border-radius: 18px;
        overflow: hidden;
        background: #fff;
        padding: 8px;
        border: 1px solid var(--glass-border);
        box-shadow: var(--shadow-sm);
    }

    .cart-item-img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        transition: transform 0.5s ease;
    }

    .cart-item:hover .cart-item-img {
        transform: scale(1.1);
    }

    .cart-item-info {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .cart-item-category {
        font-size: 0.65rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.15em;
        color: #ff2d55; /* Vibrant Neon Pink */
        background: rgba(255, 45, 85, 0.1);
        padding: 5px 12px;
        border-radius: 50px;
        border: 1px solid rgba(255, 45, 85, 0.3);
        display: inline-flex;
        align-items: center;
        width: fit-content;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        margin-bottom: 0.25rem;
    }

    .cart-item-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--clr-text);
        font-family: var(--font-display);
        line-height: 1.3;
    }

    .cart-item-title a { color: inherit; text-decoration: none; transition: color 0.3s; }
    .cart-item-title a:hover { color: var(--clr-primary); }

    .qty-controls-premium {
        display: flex;
        align-items: center;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--glass-border);
        border-radius: 12px;
        padding: 4px;
        width: fit-content;
        margin-top: 0.75rem;
    }

    .qty-btn-premium {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        border: none;
        color: var(--clr-text);
        cursor: pointer;
        border-radius: 8px;
        transition: all 0.2s;
        font-size: 1.1rem;
    }

    .qty-btn-premium:hover { background: rgba(0, 212, 212, 0.15); color: var(--clr-primary); }
    
    .qty-input-premium {
        width: 40px;
        text-align: center;
        border: none;
        background: transparent;
        font-weight: 800;
        color: var(--clr-text);
        font-size: 1rem;
    }

    .cart-item-right {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        justify-content: space-between;
        height: 100%;
        gap: 1.5rem;
    }

    .cart-item-price {
        font-size: 1.5rem;
        font-weight: 900;
        color: #002222; /* High-Contrast Dark Cyan */
        font-family: var(--font-display);
        letter-spacing: -0.03em;
        background: var(--grad-primary);
        padding: 4px 12px;
        border-radius: 12px;
    }

    .btn-remove-premium {
        background: rgba(239, 68, 68, 0.08);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.2);
        padding: 6px 16px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .btn-remove-premium:hover {
        background: #ef4444;
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3);
    }

    /* --- SUMMARY PANEL --- */
    .order-summary-premium {
        background: var(--glass-bg);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border);
        border-radius: 32px;
        padding: 2.5rem;
        position: sticky;
        top: 120px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }

    .summary-title {
        font-size: 1rem;
        font-weight: 800;
        margin-bottom: 2.5rem;
        font-family: var(--font-display);
        color: #fff;
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 0.15em;
        background: rgba(0, 0, 0, 0.4);
        padding: 12px 20px;
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.05);
        box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.2);
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1.25rem;
        font-size: 1rem;
        color: var(--clr-text-light);
    }

    .summary-total {
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid var(--glass-border);
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
    }

    .summary-total span:first-child { font-weight: 600; color: var(--clr-text-light); }
    .total-amount {
        font-size: 2.25rem;
        font-weight: 900;
        color: #002222; /* High-Contrast Dark Cyan */
        font-family: var(--font-display);
        letter-spacing: -0.05em;
        line-height: 1;
        background: var(--grad-primary);
        padding: 5px 15px;
        border-radius: 14px;
        box-shadow: 0 10px 20px rgba(0, 212, 212, 0.2);
    }

    .btn-checkout-premium {
        width: 100%;
        margin-top: 2.5rem;
        padding: 1.25rem;
        background: var(--grad-primary);
        color: #003838;
        border: none;
        border-radius: 18px;
        font-size: 1.15rem;
        font-weight: 800;
        font-family: var(--font-display);
        cursor: pointer;
        transition: all 0.4s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        box-shadow: 0 10px 20px rgba(0, 212, 212, 0.2);
    }

    .btn-checkout-premium:hover:not(:disabled) {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(0, 212, 212, 0.4);
        filter: brightness(1.1);
    }

    .btn-checkout-premium:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* --- EMPTY STATE --- */
    .empty-cart-premium {
        text-align: center;
        padding: 6rem 2rem;
        background: var(--glass-bg);
        border-radius: 40px;
        border: 1px dashed var(--glass-border);
    }

    .empty-cart-icon {
        font-size: 6rem;
        background: var(--grad-primary);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 2rem;
        display: inline-block;
        filter: drop-shadow(0 10px 15px rgba(0, 212, 212, 0.2));
    }

    /* --- RESPONSIVE --- */
    @media (max-width: 1100px) {
        .cart-layout { grid-template-columns: 1fr; }
        .order-summary-premium { position: relative; top: 0; }
    }

    @media (max-width: 768px) {
        .cart-item {
            grid-template-columns: 90px 1fr;
            gap: 1rem;
            padding: 1rem;
            border-radius: 20px;
        }
        .cart-item-img-wrapper {
            width: 90px;
            height: 90px;
            padding: 5px;
        }
        .cart-item-title {
            font-size: 1rem;
        }
        .cart-item-right {
            grid-column: 1 / -1;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding-top: 0.75rem;
            border-top: 1px solid var(--glass-border);
            margin-top: 0.5rem;
        }
        .order-summary-premium {
            padding: 1.5rem;
            border-radius: 24px;
        }
        .total-amount {
            font-size: 1.75rem;
        }
    }
</style>
@endpush

@section('content')
<main class="section">
    <div class="container">
        <h1 class="section-title">Meu Carrinho</h1>
        <div id="cart-content">
            <div style="text-align: center; padding: 4rem; color: var(--clr-primary);">
                <i class='bx bx-loader-alt bx-spin' style="font-size: 3rem; margin-bottom: 1rem;"></i>
                <p>Carregando seu carrinho...</p>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/pages/carrinho.js') }}"></script>
@endpush
