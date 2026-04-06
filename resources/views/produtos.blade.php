@extends('layouts.app')

@push('styles')
<style>
    .filter-header {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
        gap: 1.5rem;
        margin-bottom: 2rem;
        padding: 2rem;
        background: var(--clr-surface);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-lg);
        border: 1px solid var(--clr-border);
    }
    @media (max-width: 768px) {
        .filter-header {
            padding: 1.25rem;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
    }
    .search-wrapper {
        display: flex;
        width: 100%;
        max-width: 900px;
        gap: 1rem;
        align-items: center;
    }
    .topic-select {
        background: var(--clr-bg) !important;
        color: var(--clr-text) !important;
        border: 1px solid var(--clr-border);
        border-radius: var(--radius-md);
        padding: 0.8rem 1.2rem;
        font-family: var(--font-display);
        font-weight: 600;
        cursor: pointer;
        min-width: 220px;
        outline: none;
        transition: var(--transition);
        box-shadow: var(--shadow-sm);
    }
    .topic-select:focus {
        border-color: var(--clr-accent);
        box-shadow: 0 0 0 4px rgba(var(--clr-accent-rgb), 0.1);
    }
    .search-input-container {
        display: flex;
        align-items: center;
        flex-grow: 1;
        background: var(--clr-bg);
        border-radius: var(--radius-md);
        padding: 0 1.2rem;
        border: 1px solid var(--clr-border);
        transition: var(--transition);
        box-shadow: var(--shadow-sm);
    }
    .search-input-container:focus-within {
        border-color: var(--clr-accent);
        background: var(--clr-surface);
        box-shadow: 0 0 0 4px rgba(var(--clr-accent-rgb), 0.1);
    }
    .search-input-container input {
        border: none;
        background: transparent;
        padding: 0.8rem;
        width: 100%;
        color: var(--clr-text);
        outline: none;
        font-size: 1rem;
        font-family: var(--font-body);
    }
    .no-results {
        text-align: center;
        padding: 5rem 0;
        grid-column: 1 / -1;
    }
    @media (max-width: 768px) {
        .search-wrapper {
            flex-direction: column;
        }
        .topic-select {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="container" style="padding: 2rem 1rem;">
    <h1 class="section-title" style="margin-bottom: 2rem;">Nossa Coleção</h1>
    <div class="filter-header">
        <div class="search-wrapper">
            <select id="topic-filter" class="topic-select">
                <option value="all">📚 Todos os Produtos</option>
                <option value="promocoes">🏷️ Promoções</option>
                <option value="novidades">✨ Novidades</option>
                <option value="criancas">🧸 Crianças</option>
                <option value="mochilas">🎒 Mochilas</option>
                <option value="materiais">✏️ Materiais Escolares</option>
                <option value="costura">🧵 Costura & Bordados</option>
                <optgroup label="🧵 Subcategorias Costura">
                    <option value="agulhas">Agulhas</option>
                    <option value="armarinhos">Armarinhos</option>
                    <option value="botoes">Botões e Zíper</option>
                    <option value="barbantes">Barbantes</option>
                    <option value="bordados">Bordados e Viés</option>
                    <option value="cama">Cama, Mesa e Banho</option>
                    <option value="croche">Crochê e Tricô</option>
                    <option value="fitas">Fitas e Laços</option>
                    <option value="las">Lãs e Fios</option>
                    <option value="linhas">Linhas Costura</option>
                    <option value="embalagens">Embalagens</option>
                </optgroup>
            </select>
            
            <div class="search-input-container" style="display: none !important;">
                <i class='bx bx-search' style="color: var(--clr-accent);"></i>
                <input type="text" id="search-input" placeholder="O que você está procurando hoje?">
            </div>

            <select id="brand-filter" class="topic-select" style="display: none; min-width: 140px;">
                <option value="all">Todas as Marcas</option>
            </select>
        </div>
        <div class="category-chips-wrapper" id="category-chips"></div>
    </div>
    <div class="product-grid" id="catalog-grid">
        <div style="grid-column: 1/-1; text-align:center; padding: 4rem; color: var(--clr-primary);">
            <i class='bx bx-loader-alt bx-spin' style="font-size: 3rem; margin-bottom: 1rem;"></i>
            <p>Carregando nossa coleção especial...</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/pages/produtos.js?v=' . time()) }}"></script>
@endpush
