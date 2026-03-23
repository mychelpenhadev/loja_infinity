document.addEventListener('DOMContentLoaded', async () => {
    // Basic Auth Check
    try {
        const response = await fetch('api/auth.php?action=check');
        const data = await response.json();
        
        if (!data.loggedIn || data.role !== 'admin') {
            window.location.href = 'index.html';
            return;
        }
    } catch(err) {
        window.location.href = 'index.html';
        return;
    }

    // Aguardar o ConfigManager carregar os dados do banco
    // No db.js ele inicia automaticamente, mas vamos garantir aqui
    if (window.ConfigManager && window.ConfigManager.init) {
        await window.ConfigManager.init();
    }

    loadSettings();
    setupForm();
});

function loadSettings() {
    const whatsappInput = document.getElementById('config-whatsapp');
    const brandsCosturaInput = document.getElementById('config-brands-costura');
    const brandsCanetasInput = document.getElementById('config-brands-canetas');

    if (!whatsappInput) return;

    const savedWhatsapp = window.ConfigManager.get('whatsappNumber');
    const savedBrandsCostura = window.ConfigManager.get('brandsCostura');
    const savedBrandsCanetas = window.ConfigManager.get('brandsCanetas');
    
    if (savedWhatsapp) whatsappInput.value = savedWhatsapp;
    if (savedBrandsCostura) brandsCosturaInput.value = savedBrandsCostura;
    if (savedBrandsCanetas) brandsCanetasInput.value = savedBrandsCanetas;
}

function setupForm() {
    const form = document.getElementById('configForm');
    if (!form) return;
    
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const whatsappVal = document.getElementById('config-whatsapp').value.trim();
        const brandsCosturaVal = document.getElementById('config-brands-costura').value.trim();
        const brandsCanetasVal = document.getElementById('config-brands-canetas').value.trim();
        
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> Salvando...";

        await window.ConfigManager.set('whatsappNumber', whatsappVal);
        await window.ConfigManager.set('brandsCostura', brandsCosturaVal);
        await window.ConfigManager.set('brandsCanetas', brandsCanetasVal);
        
        submitBtn.disabled = false;
        submitBtn.innerText = "Salvar Configurações";
        
        window.showToast("Configurações salvas com sucesso!", "success");
    });
}
