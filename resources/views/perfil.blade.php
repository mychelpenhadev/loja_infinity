@extends('layouts.app')

@push('styles')
<style>
/* =========================================================
   PERFIL — INFINITY VARIEDADES
   Responsivo: PC (>1024px) | Tablet (768–1024px) | Mobile (<768px)
   ========================================================= */

/* Reset de overflow no nível da página */
html { overflow-x: hidden; }
body { overflow-x: hidden; }

:root {
    --pg: rgba(0, 56, 56, 0.45);
    --pb: rgba(0, 212, 212, 0.12);
    --pa: #00d4d4;
    /* Legado: usado nos cards gerados dinamicamente pelo perfil.js */
    --profile-border: rgba(0, 212, 212, 0.12);
    --profile-accent: #00d4d4;
}

/* ── Página ───────────────────────────────────────────── */
.pf-page {
    min-height: 80vh;
    padding: 3rem 1.5rem 4rem;
    box-sizing: border-box;
    width: 100%;
    overflow-x: hidden;
    background: radial-gradient(circle at 80% 10%, rgba(0,212,212,0.06), transparent 50%);
}

/* ── Grid principal ───────────────────────────────────── */
.pf-grid {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 2rem;
    max-width: 1160px;
    margin: 0 auto;
    align-items: start;
    width: 100%;
    box-sizing: border-box;
    min-width: 0;
}

/* ── Sidebar ──────────────────────────────────────────── */
.pf-sidebar {
    background: var(--pg);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid var(--pb);
    border-radius: 28px;
    padding: 2rem 1.25rem;
    position: sticky;
    top: 90px;
    box-shadow: 0 16px 40px rgba(0,0,0,0.25);
    box-sizing: border-box;
    width: 100%;
    min-width: 0;
}

/* Avatar hero */
.pf-hero {
    text-align: center;
    padding-bottom: 1.75rem;
    margin-bottom: 1.75rem;
    border-bottom: 1px solid var(--pb);
}
.pf-avatar-wrap {
    position: relative;
    width: 96px;
    height: 96px;
    margin: 0 auto 1rem;
}
.pf-avatar {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--pa);
    box-shadow: 0 8px 20px rgba(0,212,212,0.2);
}
.pf-badge {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 28px;
    height: 28px;
    background: var(--grad-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    color: #003838;
    border: 2px solid #001a1a;
}
.pf-name {
    font-size: 1.1rem;
    font-weight: 800;
    color: var(--clr-text);
    letter-spacing: -0.02em;
    margin-bottom: 0.3rem;
}
.pf-role {
    font-size: 0.68rem;
    color: var(--pa);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
}

/* Nav tabs — vertical no desktop */
.pf-nav {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
.pf-tab {
    display: flex;
    align-items: center;
    gap: 0.875rem;
    padding: 0.875rem 1.125rem;
    border-radius: 16px;
    color: var(--clr-text-light);
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    border: 1px solid transparent;
    transition: all 0.25s ease;
    white-space: nowrap;
    user-select: none;
}
.pf-tab i { font-size: 1.3rem; flex-shrink: 0; }
.pf-tab:hover { background: rgba(255,255,255,0.05); color: var(--clr-text); }
.pf-tab.active {
    background: var(--grad-primary);
    color: #003838;
    font-weight: 800;
    box-shadow: 0 8px 16px rgba(0,212,212,0.2);
}

/* Logout */
.pf-logout {
    width: 100%;
    margin-top: 2rem;
    padding: 0.95rem;
    border-radius: 16px;
    border: 1px solid rgba(239, 68, 68, 0.25);
    background: rgba(239, 68, 68, 0.06);
    color: #EF4444;
    font-weight: 800;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.625rem;
    cursor: pointer;
    transition: all 0.25s;
    box-sizing: border-box;
}
.pf-logout i { font-size: 1.2rem; }
.pf-logout:hover { background: rgba(239,68,68,0.12); }

/* ── Área de conteúdo ─────────────────────────────────── */
.pf-content {
    width: 100%;
    min-width: 0;
    max-width: 100%;
    box-sizing: border-box;
}

/* Painéis */
.pf-panel {
    display: none;
    background: var(--pg);
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
    border: 1px solid var(--pb);
    border-radius: 28px;
    padding: 2.5rem;
    box-shadow: 0 20px 40px rgba(0,0,0,0.18);
    animation: pfFade 0.4s ease-out;
    box-sizing: border-box;
    width: 100%;
}
.pf-panel.active { display: block; }

@keyframes pfFade {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: translateY(0); }
}

.pf-panel-head {
    margin-bottom: 2rem;
    padding-bottom: 1.25rem;
    border-bottom: 1px solid var(--pb);
}
.pf-panel-title {
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--clr-text);
    letter-spacing: -0.03em;
    font-family: var(--font-display);
}

/* ── Bloco de avatar no painel ────────────────────────── */
.pf-pic-block {
    display: flex;
    align-items: center;
    gap: 2rem;
    padding: 1.75rem;
    background: rgba(255,255,255,0.02);
    border: 1px solid var(--pb);
    border-radius: 20px;
    margin-bottom: 2rem;
    box-sizing: border-box;
    width: 100%;
}
.pf-pic-preview {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--pa);
    box-shadow: 0 8px 24px rgba(0,212,212,0.18);
    flex-shrink: 0;
}
.pf-pic-info {
    flex: 1;
    min-width: 0;
}
.pf-pic-info h3 {
    font-size: 1rem;
    font-weight: 700;
    margin-bottom: 0.4rem;
    color: var(--clr-text);
}
.pf-pic-info p {
    font-size: 0.82rem;
    color: var(--clr-text-light);
    margin-bottom: 1rem;
    line-height: 1.5;
}
.pf-pic-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}
.pf-btn-cam {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.7rem 1.25rem;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.25s;
    border: none;
    background: var(--grad-primary);
    color: #003838;
}
.pf-btn-rm {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.7rem 1.25rem;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.25s;
    border: 1px solid rgba(239,68,68,0.25);
    background: rgba(239,68,68,0.08);
    color: #ef4444;
}

/* ── Formulário ───────────────────────────────────────── */
.pf-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}
.pf-field {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}
.pf-field label {
    font-size: 0.75rem;
    font-weight: 800;
    color: var(--clr-text-light);
    text-transform: uppercase;
    letter-spacing: 0.1em;
}
.pf-input {
    width: 100%;
    box-sizing: border-box;
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--pb);
    border-radius: 14px;
    padding: 1rem 1.25rem;
    color: #fff;
    font-size: 0.95rem;
    transition: all 0.25s;
    font-family: var(--font-body);
}
.pf-input:focus {
    border-color: var(--pa);
    background: rgba(0,212,212,0.05);
    outline: none;
    box-shadow: 0 0 0 3px rgba(0,212,212,0.1);
}

/* Alterar senha — bloco */
.pf-senha-block {
    grid-column: 1 / -1;
    margin-top: 0.5rem;
    padding: 2rem;
    background: rgba(0,0,0,0.2);
    border-radius: 22px;
    border: 1px solid var(--pb);
    box-sizing: border-box;
}
.pf-senha-block h3 {
    font-size: 1rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.6rem;
    color: var(--pa);
}
.pf-pass-wrap {
    position: relative;
}
.pf-pass-wrap .pf-input { padding-right: 3.25rem; }
.pf-pass-toggle {
    position: absolute;
    right: 1.1rem;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: var(--clr-text-light);
    font-size: 1.1rem;
    line-height: 1;
}

/* Segurança — badge */
.pf-ssl-badge {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #10B981;
    font-weight: 800;
    font-size: 0.875rem;
    padding: 1rem 1.25rem;
    background: rgba(16,185,129,0.06);
    border-radius: 14px;
    border: 1px solid rgba(16,185,129,0.15);
}

/* ── Botão submit ─────────────────────────────────────── */
.pf-submit-row {
    margin-top: 2.5rem;
    display: flex;
    justify-content: flex-end;
}
.pf-btn-submit {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1.1rem 3.5rem;
    border-radius: 18px;
    font-weight: 900;
    font-size: 1rem;
    background: var(--grad-primary);
    color: #003838;
    border: none;
    cursor: pointer;
    box-shadow: 0 12px 28px rgba(0,212,212,0.25);
    transition: all 0.25s;
    box-sizing: border-box;
}
.pf-btn-submit:hover { transform: translateY(-2px); box-shadow: 0 16px 32px rgba(0,212,212,0.35); }
.pf-btn-submit i { font-size: 1.3rem; }

/* ── Cards de pedido ──────────────────────────────────── */
.pf-order-card {
    background: rgba(255,255,255,0.02);
    border: 1px solid var(--pb);
    border-radius: 18px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    transition: background 0.25s;
    box-sizing: border-box;
}
.pf-order-card:hover { background: rgba(255,255,255,0.04); }

.pf-status {
    padding: 5px 12px;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    background: rgba(0,0,0,0.4);
    white-space: nowrap;
    flex-shrink: 0;
}
.pf-status.delivered { color: #00d4d4; border: 1px solid rgba(0,212,212,0.35); }
.pf-status.pending   { color: #a855f7; border: 1px solid rgba(168,85,247,0.35); }

/* ── Promos ───────────────────────────────────────────── */
.pf-promos-head {
    margin-top: 3.5rem;
    padding-top: 2rem;
    border-top: 1px solid var(--pb);
}
.pf-promos-head h3 {
    font-size: 1.2rem;
    font-weight: 800;
    color: var(--pa);
    display: flex;
    align-items: center;
    gap: 0.6rem;
    margin-bottom: 1.5rem;
}
.pf-promo-scroll {
    display: flex;
    gap: 1rem;
    overflow-x: auto;
    padding-bottom: 0.75rem;
    scrollbar-width: thin;
    scrollbar-color: var(--pa) transparent;
}
.pf-promo-card {
    min-width: 150px;
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--pb);
    border-radius: 18px;
    padding: 1rem;
    text-align: center;
    text-decoration: none;
    transition: all 0.25s;
    flex-shrink: 0;
}
.pf-promo-card:hover { border-color: var(--pa); transform: translateY(-4px); }
.pf-promo-card img { width: 100%; height: 100px; object-fit: contain; margin-bottom: 0.75rem; }
.pf-promo-card h4 { font-size: 0.82rem; color: #fff; margin-bottom: 0.375rem; }
.pf-promo-card span { font-size: 0.95rem; font-weight: 800; color: var(--pa); }

/* ── Suporte ──────────────────────────────────────────── */
.pf-suporte-wrap { text-align: center; max-width: 520px; margin: 2rem auto 0; }
.pf-wa-icon {
    width: 90px;
    height: 90px;
    background: rgba(37,211,102,0.1);
    border-radius: 26px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 2rem;
}
.pf-wa-icon i { font-size: 4rem; color: #25D366; filter: drop-shadow(0 0 12px rgba(37,211,102,0.3)); }
.pf-suporte-wrap h2 { font-size: 1.4rem; margin-bottom: 0.875rem; }
.pf-suporte-wrap p { font-size: 0.95rem; color: var(--clr-text-light); line-height: 1.75; margin-bottom: 2rem; }

.pf-wa-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.875rem;
    padding: 1.25rem;
    background: #25D366;
    color: #fff;
    border-radius: 20px;
    text-decoration: none;
    font-weight: 900;
    font-size: 1.1rem;
    box-shadow: 0 12px 28px rgba(37,211,102,0.28);
    transition: all 0.3s;
}
.pf-wa-btn:hover { transform: translateY(-2px); }
.pf-wa-btn i { font-size: 1.6rem; }

.pf-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
    margin-top: 2.5rem;
}
.pf-info-card {
    background: rgba(255,255,255,0.02);
    border: 1px solid var(--pb);
    border-radius: 18px;
    padding: 1.25rem;
    text-align: left;
}
.pf-info-card i { font-size: 1.6rem; color: var(--pa); display: block; margin-bottom: 0.625rem; }
.pf-info-card h4 { font-size: 0.9rem; margin-bottom: 0.3rem; color: var(--clr-text); }
.pf-info-card p { font-size: 0.8rem; color: var(--clr-text-light); }

/* =========================================================
   TABLET  (768px – 1024px)
   ========================================================= */
@media (max-width: 1024px) {
    .pf-grid {
        grid-template-columns: 1fr;
        gap: 1.25rem;
    }
    .pf-sidebar {
        position: static;
        padding: 1.5rem 1.25rem;
        border-radius: 24px;
    }
    /* Hero em linha no tablet */
    .pf-hero {
        display: flex;
        align-items: center;
        gap: 1rem;
        text-align: left;
        padding-bottom: 1.25rem;
        margin-bottom: 1.25rem;
    }
    .pf-avatar-wrap { width: 64px; height: 64px; margin: 0; flex-shrink: 0; }
    .pf-badge { width: 22px; height: 22px; font-size: 0.7rem; }
    .pf-name { font-size: 1rem; }

    /* Tabs em linha horizontal com scroll */
    .pf-nav {
        flex-direction: row;
        overflow-x: auto;
        gap: 0.5rem;
        padding-bottom: 2px;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }
    .pf-nav::-webkit-scrollbar { display: none; }
    .pf-tab {
        flex-shrink: 0;
        padding: 0.7rem 1.1rem;
        border-radius: 14px;
        font-size: 0.875rem;
        gap: 0.5rem;
    }
    .pf-tab i { font-size: 1.15rem; }
    .pf-tab:hover { transform: none; }

    .pf-logout { margin-top: 1.25rem; padding: 0.825rem; border-radius: 14px; }
    .pf-panel { padding: 2rem; }
    .pf-panel-title { font-size: 1.4rem; }
    .pf-form-grid { grid-template-columns: 1fr 1fr; }
}

/* =========================================================
   MOBILE  (< 768px)
   ========================================================= */
@media (max-width: 767px) {
    .pf-page { padding: 0.75rem 0.875rem 6.5rem; width: 100%; box-sizing: border-box; }

    .pf-grid { gap: 0.875rem; width: 100%; box-sizing: border-box; }

    /* Sidebar mobile */
    .pf-sidebar {
        padding: 1rem;
        border-radius: 18px;
        width: 100%;
        box-sizing: border-box;
    }

    /* Hero linha compacta */
    .pf-hero {
        display: flex;
        align-items: center;
        gap: 0.875rem;
        text-align: left;
        padding-bottom: 1rem;
        margin-bottom: 1rem;
    }
    .pf-avatar-wrap { width: 48px; height: 48px; margin: 0; flex-shrink: 0; }
    .pf-badge { width: 18px; height: 18px; font-size: 0.6rem; border-width: 1.5px; }

    /* Wrapper de texto para limitar largura */
    .pf-hero-text { flex: 1; min-width: 0; overflow: hidden; }
    .pf-name { font-size: 0.9rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .pf-role { font-size: 0.58rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    /* Tabs */
    .pf-nav {
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.5rem;
    }
    .pf-tab {
        flex: 1 1 calc(50% - 0.5rem);
        justify-content: center;
        padding: 0.65rem 0.5rem;
        border-radius: 12px;
        font-size: 0.8rem;
        gap: 0.375rem;
    }
    .pf-tab i { font-size: 1rem; }
    .pf-tab:hover { transform: none; }
    .pf-logout { margin-top: 1rem; padding: 0.75rem; border-radius: 12px; font-size: 0.875rem; }

    /* Painel */
    .pf-panel { padding: 1.25rem 1rem; border-radius: 20px; }
    .pf-panel-head { margin-bottom: 1.25rem; padding-bottom: 0.875rem; }
    .pf-panel-title { font-size: 1.15rem; }

    /* Bloco avatar no conteúdo — empilhado */
    .pf-pic-block {
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 1rem;
        padding: 1.25rem;
    }
    .pf-pic-preview { width: 80px; height: 80px; }
    .pf-pic-info { width: 100%; }
    .pf-pic-info p { font-size: 0.78rem; }
    .pf-pic-actions { justify-content: center; }
    .pf-btn-cam, .pf-btn-rm { width: 100%; justify-content: center; padding: 0.75rem; }

    /* Formulários — 1 coluna */
    .pf-form-grid { grid-template-columns: 1fr; gap: 1rem; }
    .pf-senha-block { padding: 1.25rem; }
    .pf-senha-block h3 { font-size: 0.9rem; }

    /* Submit */
    .pf-submit-row { justify-content: stretch; }
    .pf-btn-submit { width: 100%; justify-content: center; padding: 1rem; border-radius: 14px; }

    /* Pedidos */
    .pf-order-card {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.625rem;
        padding: 1rem;
    }

    /* Suporte */
    .pf-suporte-wrap { margin: 1rem auto 0; }
    .pf-wa-icon { width: 72px; height: 72px; border-radius: 20px; }
    .pf-wa-icon i { font-size: 3rem; }
    .pf-suporte-wrap h2 { font-size: 1.15rem; }
    .pf-suporte-wrap p { font-size: 0.875rem; }
    .pf-wa-btn { font-size: 1rem; padding: 1rem; }
    .pf-info-grid { grid-template-columns: 1fr; }
}

/* =========================================================
   EXTRA SMALL  (< 400px)
   ========================================================= */
@media (max-width: 399px) {
    .pf-page { padding: 0.5rem 0.625rem 2.5rem; }
    .pf-tab { padding: 0.5rem 0.75rem; font-size: 0.75rem; }
    .pf-panel { padding: 1rem 0.875rem; border-radius: 16px; }
    .pf-panel-title { font-size: 1.05rem; }
}
</style>
@endpush

@section('content')
<main class="pf-page">
    <div class="pf-grid">

        {{-- ── SIDEBAR ─────────────────────────────────────── --}}
        <aside class="pf-sidebar">

            {{-- Avatar hero --}}
            <div class="pf-hero">
                <div class="pf-avatar-wrap">
                    <img id="prof-pic-preview"
                         src="https://ui-avatars.com/api/?name=User&background=random"
                         alt="Avatar"
                         class="pf-avatar">
                    <div class="pf-badge"><i class='bx bxs-check-shield'></i></div>
                </div>
                {{-- Wrapper de texto (mobile: flex item que nao estoura) --}}
                <div class="pf-hero-text">
                    <div class="pf-name" id="prof-welcome-name">Carregando…</div>
                    <div class="pf-role">Membro Infinity Elite</div>
                </div>
            </div>

            {{-- Tabs --}}
            <nav class="pf-nav">
                <div class="pf-tab active" data-tab="dados">
                    <i class='bx bx-user-circle'></i> Meus Dados
                </div>
                <div class="pf-tab" data-tab="pedidos">
                    <i class='bx bx-shopping-bag'></i> Pedidos
                </div>
                <div class="pf-tab" data-tab="seguranca">
                    <i class='bx bx-lock-open-alt'></i> Segurança
                </div>
                <div class="pf-tab" data-tab="suporte">
                    <i class='bx bx-help-circle'></i> Suporte
                </div>
            </nav>

            {{-- Logout --}}
            <button id="btn-logout" class="pf-logout">
                <i class='bx bx-log-out-circle'></i> Sair da Conta
            </button>
        </aside>

        {{-- ── CONTEÚDO ─────────────────────────────────────── --}}
        <div class="pf-content">

            {{-- TAB: MEUS DADOS --}}
            <section class="pf-panel active" id="tab-dados">
                <div class="pf-panel-head">
                    <h1 class="pf-panel-title">Informações Pessoais</h1>
                </div>

                <form id="profile-form">
                    {{-- Bloco de foto --}}
                    <div class="pf-pic-block">
                        <img id="prof-pic-preview-large"
                             src="https://ui-avatars.com/api/?name=User&background=random"
                             class="pf-pic-preview"
                             alt="Foto de Perfil">
                        <div class="pf-pic-info">
                            <h3>Foto de Perfil</h3>
                            <p>Personalize sua conta carregando uma foto. Formatos aceitos: JPG, PNG ou WebP.</p>
                            <div class="pf-pic-actions">
                                <input type="file" id="prof-pic-input" accept="image/*" style="display:none;">
                                <button type="button" id="btn-edit-pic" class="pf-btn-cam">
                                    <i class='bx bx-camera'></i> Alterar Foto
                                </button>
                                <button type="button" id="btn-delete-pic" class="pf-btn-rm">
                                    <i class='bx bx-trash'></i> Remover
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Nome --}}
                    <div class="pf-field">
                        <label for="prof-nome">Nome Completo</label>
                        <input type="text" id="prof-nome" class="pf-input" required>
                    </div>

                    {{-- Submit --}}
                    <div class="pf-submit-row">
                        <button type="submit" class="pf-btn-submit">
                            Salvar Alterações <i class='bx bx-check-circle'></i>
                        </button>
                    </div>
                </form>

                {{-- Promos --}}
                <div class="pf-promos-head">
                    <h3><i class='bx bxs-star' style="color:#FFD700;"></i> Seleção Infinity para Você</h3>
                    <div class="pf-promo-scroll" id="profile-promos-list"></div>
                </div>
            </section>

            {{-- TAB: PEDIDOS --}}
            <section class="pf-panel" id="tab-pedidos">
                <div class="pf-panel-head">
                    <h1 class="pf-panel-title">Histórico de Pedidos</h1>
                </div>
                <div id="profile-orders-list">
                    {{-- Preenchido dinamicamente por perfil.js --}}
                </div>
            </section>

            {{-- TAB: SEGURANÇA --}}
            <section class="pf-panel" id="tab-seguranca">
                <div class="pf-panel-head">
                    <h1 class="pf-panel-title">Segurança da Conta</h1>
                </div>
                <form id="security-form">
                    <div class="pf-form-grid">

                        <div class="pf-field">
                            <label for="sec-email">Gmail / E-mail</label>
                            <input type="email" id="sec-email" class="pf-input">
                        </div>

                        <div class="pf-field">
                            <label for="sec-cpf">CPF Cadastrado</label>
                            <input type="text" id="sec-cpf" class="pf-input" placeholder="000.000.000-00">
                        </div>

                        <div class="pf-field">
                            <label for="sec-telefone">WhatsApp / Telefone</label>
                            <input type="text" id="sec-telefone" class="pf-input">
                        </div>

                        <div class="pf-field">
                            <label>Nível de Segurança</label>
                            <div class="pf-ssl-badge">
                                <i class='bx bxs-lock-alt'></i> Proteção SSL Ativa
                            </div>
                        </div>

                        {{-- Bloco senha --}}
                        <div class="pf-senha-block">
                            <h3><i class='bx bx-key'></i> Alterar Senha de Acesso</h3>
                            <div class="pf-form-grid">
                                <div class="pf-field">
                                    <label for="sec-senha-atual">Senha Atual</label>
                                    <div class="pf-pass-wrap">
                                        <input type="password" id="sec-senha-atual" class="pf-input" placeholder="••••••••">
                                        <i class='bx bx-show pf-pass-toggle' onclick="togglePassword('sec-senha-atual', this)"></i>
                                    </div>
                                </div>
                                <div class="pf-field">
                                    <label for="sec-nova-senha">Nova Senha</label>
                                    <div class="pf-pass-wrap">
                                        <input type="password" id="sec-nova-senha" class="pf-input" placeholder="••••••••">
                                        <i class='bx bx-show pf-pass-toggle' onclick="togglePassword('sec-nova-senha', this)"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pf-submit-row">
                        <button type="submit" class="pf-btn-submit">
                            Atualizar Segurança <i class='bx bx-shield-check'></i>
                        </button>
                    </div>
                </form>
            </section>

            {{-- TAB: SUPORTE --}}
            <section class="pf-panel" id="tab-suporte">
                <div class="pf-panel-head">
                    <h1 class="pf-panel-title">Suporte ao Cliente</h1>
                </div>
                <div class="pf-suporte-wrap">
                    <div class="pf-wa-icon">
                        <i class='bx bxl-whatsapp'></i>
                    </div>
                    <h2>Central de Ajuda Via WhatsApp</h2>
                    <p>Precisa de ajuda com uma entrega, troca ou dúvida sobre produto? Fale diretamente com nosso time Elite pelo WhatsApp em tempo real.</p>

                    <a href="#" id="profile-wa-btn" target="_blank" class="pf-wa-btn">
                        Iniciar Conversa Agora <i class='bx bx-right-arrow-alt'></i>
                    </a>

                    <div class="pf-info-grid">
                        <div class="pf-info-card">
                            <i class='bx bx-time-five'></i>
                            <h4>Horário</h4>
                            <p>Seg – Sex: 08h às 18h</p>
                        </div>
                        <div class="pf-info-card">
                            <i class='bx bx-envelope'></i>
                            <h4>E-mail</h4>
                            <p>sac@infinityvariedades.com</p>
                        </div>
                    </div>
                </div>
            </section>

        </div>{{-- /pf-content --}}
    </div>{{-- /pf-grid --}}
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const tabs   = document.querySelectorAll('.pf-tab');
    const panels = document.querySelectorAll('.pf-panel');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.tab;
            tabs.forEach(t => t.classList.remove('active'));
            panels.forEach(p => {
                p.classList.remove('active');
                if (p.id === `tab-${target}`) p.classList.add('active');
            });
            tab.classList.add('active');
        });
    });
});
</script>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/pages/perfil.js') }}"></script>
@endpush
