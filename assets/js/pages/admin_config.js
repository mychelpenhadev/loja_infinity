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

    loadSettings();
    setupForm();
});

function loadSettings() {
    const whatsappInput = document.getElementById('config-whatsapp');
    const brandsCosturaInput = document.getElementById('config-brands-costura');
    const brandsCanetasInput = document.getElementById('config-brands-canetas');

    const savedWhatsapp = window.ConfigManager.get('whatsappNumber');
    const savedBrandsCostura = window.ConfigManager.get('brandsCostura');
    const savedBrandsCanetas = window.ConfigManager.get('brandsCanetas');
    
    if (savedWhatsapp) whatsappInput.value = savedWhatsapp;
    if (savedBrandsCostura) brandsCosturaInput.value = savedBrandsCostura;
    if (savedBrandsCanetas) brandsCanetasInput.value = savedBrandsCanetas;
}

function setupForm() {
    const form = document.getElementById('configForm');
    
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        
        const whatsappVal = document.getElementById('config-whatsapp').value.trim();
        const brandsCosturaVal = document.getElementById('config-brands-costura').value.trim();
        const brandsCanetasVal = document.getElementById('config-brands-canetas').value.trim();
        
        window.ConfigManager.set('whatsappNumber', whatsappVal);
        window.ConfigManager.set('brandsCostura', brandsCosturaVal);
        window.ConfigManager.set('brandsCanetas', brandsCanetasVal);
        
        window.showToast("Configurações salvas com sucesso!", "success");
    });
}
