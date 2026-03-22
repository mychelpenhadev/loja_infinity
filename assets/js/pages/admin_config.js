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
    const pixInput = document.getElementById('config-pix');
    const savedPix = window.ConfigManager.get('pixKey');
    
    if (savedPix) {
        pixInput.value = savedPix;
    }
}

function setupForm() {
    const form = document.getElementById('configForm');
    
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        
        const pixVal = document.getElementById('config-pix').value.trim();
        
        window.ConfigManager.set('pixKey', pixVal);
        
        window.showToast("Configurações salvas com sucesso!", "success");
    });
}
