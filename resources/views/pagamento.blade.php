@extends('layouts.app')

@push('styles')
<style>
    :root {
        --checkout-glass: rgba(0, 56, 56, 0.4);
        --checkout-border: rgba(0, 212, 212, 0.1);
        --checkout-accent: #00d4d4;
    }

    .checkout-page {
        padding: 2rem 1.5rem 4rem; /* Reduced top padding */
        min-height: 85vh;
        background: radial-gradient(circle at bottom left, rgba(0, 212, 212, 0.05), transparent 600px);
    }

    .checkout-wrapper {
        max-width: 1100px;
        margin: 0 auto; /* Reset margin */
    }

    /* --- PROGRESS BAR --- */
    .checkout-steps {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 1.5rem; /* Reduced gap */
        margin-bottom: 2.5rem; /* Reduced margin-bottom */
        position: relative;
        padding-top: 0.5rem;
    }

    .step-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
        position: relative;
        z-index: 1;
    }

    .step-icon {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: var(--clr-surface);
        border: 2px solid var(--checkout-border);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: var(--clr-text-light);
        transition: all 0.4s;
    }

    .step-item.active .step-icon {
        background: var(--grad-primary);
        color: #003838;
        border-color: transparent;
        box-shadow: 0 0 20px rgba(0, 212, 212, 0.3);
    }

    .step-label {
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: var(--clr-text-light);
    }

    .step-item.active .step-label { color: var(--checkout-accent); }

    .steps-line {
        position: absolute;
        top: 22px;
        left: 50%;
        transform: translateX(-50%);
        width: 300px;
        height: 2px;
        background: var(--checkout-border);
        z-index: 0;
    }

    /* --- LAYOUT --- */
    .checkout-layout {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 3rem;
        align-items: start;
    }

    .glass-panel {
        background: var(--checkout-glass);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid var(--checkout-border);
        border-radius: 35px;
        padding: 1.5rem; /* Reduced padding */
        box-shadow: 0 25px 50px rgba(0,0,0,0.2);
    }

    .checkout-section-title {
        font-size: 1.5rem;
        font-weight: 800;
        font-family: var(--font-display);
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: #fff;
    }

    /* --- RETIRADA INFO --- */
    .retirada-box {
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid var(--checkout-border);
        border-radius: 25px;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }

    .info-row {
        display: flex;
        gap: 1.25rem;
        margin-bottom: 1.25rem;
    }

    .info-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: rgba(0, 212, 212, 0.08);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: var(--checkout-accent);
        flex-shrink: 0;
    }

    .info-content h4 { font-size: 1rem; font-weight: 700; margin-bottom: 0.25rem; color: #fff; }
    .info-content p { font-size: 0.9rem; color: var(--clr-text-light); line-height: 1.5; }

    .map-wrapper {
        border-radius: 25px;
        overflow: hidden;
        border: 1px solid var(--checkout-border);
        position: relative;
    }

    .map-wrapper iframe {
        width: 100%;
        height: 280px;
        filter: grayscale(1) invert(0.9) contrast(1.1);
        border: none;
    }

    .map-overlay {
        position: absolute;
        bottom: 1.5rem;
        left: 1.5rem;
        background: rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(10px);
        padding: 0.75rem 1.25rem;
        border-radius: 12px;
        border: 1px solid rgba(255,255,255,0.1);
        font-size: 0.8rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #fff;
    }

    /* --- SIDEBAR SUMMARY --- */
    .summary-card {
        background: var(--checkout-glass);
        backdrop-filter: blur(25px);
        -webkit-backdrop-filter: blur(25px);
        border: 1px solid var(--checkout-border);
        border-radius: 35px;
        padding: 1.5rem; /* Reduced padding */
        position: sticky;
        top: 100px;
        box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);
    }

    .receipt-header {
        text-align: center;
        margin-bottom: 1.25rem;
        padding-bottom: 1rem;
        border-bottom: 1px dashed var(--checkout-border);
    }

    .receipt-title {
        font-size: 0.75rem;
        font-weight: 850;
        text-transform: uppercase;
        letter-spacing: 0.25em;
        color: var(--checkout-accent);
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1.25rem;
        font-size: 0.95rem;
        color: var(--clr-text-light);
    }

    .summary-total-box {
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid var(--checkout-border);
    }

    .total-label { font-size: 0.85rem; font-weight: 700; color: #fff; text-transform: uppercase; margin-bottom: 0.5rem; }
    .total-value { font-size: 2.5rem; font-weight: 900; color: var(--checkout-accent); font-family: var(--font-display); letter-spacing: -0.05em; }

    /* --- RESPONSIVE --- */
    @media (max-width: 992px) {
        .checkout-layout { 
            display: flex;
            flex-direction: column-reverse;
            gap: 2rem;
        }
        .summary-card { position: static; margin-top: 0; width: 100%; }
        .glass-panel { width: 100%; }
    }

    @media (max-width: 600px) {
        .checkout-steps { gap: 0.75rem; }
        .steps-line { width: 120px; }
        .step-icon { width: 36px; height: 36px; font-size: 1rem; }
        .step-label { font-size: 0.65rem; }
        .glass-panel { padding: 1.25rem; border-radius: 24px; }
        .total-value { font-size: 2rem; }
    }
</style>
@endpush

@section('content')
<main class="checkout-page" id="checkout-container">
    <div class="checkout-wrapper">
        
        <!-- Progress Bar -->
        <div class="checkout-steps">
            <div class="steps-line"></div>
            <div class="step-item">
                <div class="step-icon"><i class='bx bx-cart'></i></div>
                <div class="step-label">Carrinho</div>
            </div>
            <div class="step-item active">
                <div class="step-icon"><i class='bx bx-credit-card-front'></i></div>
                <div class="step-label">Confirmação</div>
            </div>
            <div class="step-item">
                <div class="step-icon"><i class='bx bx-check-circle'></i></div>
                <div class="step-label">Finalizado</div>
            </div>
        </div>

        <div class="checkout-layout">
            
            <!-- LEFT AREA: METHOD & INFO -->
            <div class="glass-panel">
                <form id="payment-form">
                    <h2 class="checkout-section-title">
                        <i class='bx bx-store-alt' ></i> Método de Retirada
                    </h2>
                    
                    <div class="retirada-box">
                        <div class="info-row">
                            <div class="info-icon"><i class='bx bx-map-pin' ></i></div>
                            <div class="info-content">
                                <h4>Ponto de Coleta Principal</h4>
                                <p>Cajari, Maranhão - MA (Centro Especializado)</p>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-icon"><i class='bx bx-time-five' ></i></div>
                            <div class="info-content">
                                <h4>Horário de Funcionamento</h4>
                                <p>Segunda à Sábado - 08:00 às 18:00</p>
                            </div>
                        </div>
                        <div class="info-row" style="margin-bottom: 0;">
                            <div class="info-icon"><i class='bx bx-info-circle' ></i></div>
                            <div class="info-content">
                                <h4>Instruções</h4>
                                <p>Seu pedido será separado assim que você confirmar e enviar a lista para nossa equipe via WhatsApp.</p>
                            </div>
                        </div>
                    </div>

                    <div class="map-wrapper">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m17!1m12!1m3!1d3979.799736183494!2d-45.0119846!3d-3.3217251!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m2!1m1!2zM8KwMTknMTguMiJTIDQ1wrAwMCc0My4xIlc!5e0!3m2!1spt-BR!2sbr!4v1711200000000!5m2!1spt-BR!2sbr" allowfullscreen="" loading="lazy"></iframe>
                        <div class="map-overlay">
                            <i class='bx bxs-navigation'></i> Abrir no Google Maps
                        </div>
                    </div>

                    <div style="margin-top: 2.5rem; display: flex; align-items: center; gap: 1rem; padding: 1.5rem; background: rgba(0, 212, 212, 0.05); border-radius: 20px; border: 1px solid rgba(0, 212, 212, 0.1);">
                        <i class='bx bxl-whatsapp' style="font-size: 2rem; color: #25D366;"></i>
                        <div style="font-size: 0.85rem; color: var(--clr-text-light);">Nosso suporte está ativo para tirar qualquer dúvida antes da sua visita.</div>
                    </div>
                </form>
            </div>

            <!-- RIGHT AREA: SUMMARY & CONFIRM -->
            <div class="summary-card" id="checkout-summary">
                <div style="text-align: center; padding: 3rem;">
                    <i class='bx bx-loader-alt bx-spin' style="font-size: 2.5rem; color: var(--checkout-accent); margin-bottom: 1rem;"></i>
                    <p style="color: var(--clr-text-light); font-weight: 600;">Processando valores...</p>
                </div>
            </div>

        </div>
    </div>
</main>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/pages/pagamento.js?v=11') }}"></script>
@endpush
