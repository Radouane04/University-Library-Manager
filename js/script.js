document.addEventListener('DOMContentLoaded', function() {
    // Menu responsive
    const menuToggle = document.createElement('div');
    menuToggle.className = 'menu-toggle';
    menuToggle.innerHTML = '<i class="fas fa-bars"></i> Menu';
    document.querySelector('header .header-container').appendChild(menuToggle);
    
    const nav = document.querySelector('nav');
    
    menuToggle.addEventListener('click', function() {
        nav.style.display = nav.style.display === 'block' ? 'none' : 'block';
    });
    
    // Gestion de la taille de l'écran
    function handleResize() {
        if (window.innerWidth > 768) {
            nav.style.display = 'block';
        } else {
            nav.style.display = 'none';
        }
    }
    
    window.addEventListener('resize', handleResize);
    handleResize();
    
    // Confirmation pour les actions critiques
    document.querySelectorAll('a[onclick], button[type="submit"]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (this.getAttribute('data-confirm')) {
                if (!confirm(this.getAttribute('data-confirm'))) {
                    e.preventDefault();
                }
            }
        });
    });
    
    // Gestion des formulaires AJAX
    document.querySelectorAll('form[data-ajax]').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            try {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> En cours...';
                
                const response = await fetch(this.action, {
                    method: this.method,
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert(data.message, 'success');
                    if (data.redirect) {
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1500);
                    } else if (data.reload) {
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    }
                } else {
                    showAlert(data.message, 'danger');
                }
            } catch (error) {
                showAlert('Une erreur est survenue: ' + error.message, 'danger');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    });
    
    // Fonction pour afficher les alertes
    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.textContent = message;
        
        const container = document.querySelector('.container') || document.querySelector('main');
        container.prepend(alertDiv);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
    
    // Initialisation des tooltips
    if (window.tippy) {
        tippy('[data-tippy-content]', {
            arrow: true,
            animation: 'fade'
        });
    }
    
    // Gestion des dates
    if (window.flatpickr) {
        flatpickr('.datepicker', {
            dateFormat: 'Y-m-d',
            allowInput: true
        });
    }
});

// Fonction pour charger des données via AJAX
async function fetchData(url, options = {}) {
    try {
        const response = await fetch(url, options);
        if (!response.ok) throw new Error('Network response was not ok');
        return await response.json();
    } catch (error) {
        console.error('Error fetching data:', error);
        return null;
    }
}