import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();


// Fonctionnalités avancées pour la navbar
document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.querySelector('.navbar');
    const dropdowns = document.querySelectorAll('.dropdown');
    
    // Effet de scroll pour la navbar
    let lastScrollTop = 0;
    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        if (scrollTop > 50) {
            navbar.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.15)';
            navbar.style.padding = '0.5rem 0';
        } else {
            navbar.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
            navbar.style.padding = '';
        }
        
        // Cacher la navbar au scroll vers le bas
        if (scrollTop > lastScrollTop && scrollTop > 100) {
            navbar.style.transform = 'translateY(-100%)';
        } else {
            navbar.style.transform = 'translateY(0)';
        }
        
        lastScrollTop = scrollTop;
    });
    
    // Améliorer les dropdowns
    dropdowns.forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        const menu = dropdown.querySelector('.dropdown-menu');
        
        toggle.addEventListener('mouseenter', function() {
            if (window.innerWidth > 991) {
                const dropdownInstance = bootstrap.Dropdown.getInstance(toggle);
                if (!dropdownInstance) {
                    new bootstrap.Dropdown(toggle).show();
                } else {
                    dropdownInstance.show();
                }
            }
        });
        
        dropdown.addEventListener('mouseleave', function() {
            if (window.innerWidth > 991) {
                setTimeout(() => {
                    const dropdownInstance = bootstrap.Dropdown.getInstance(toggle);
                    if (dropdownInstance) {
                        dropdownInstance.hide();
                    }
                }, 300);
            }
        });
    });
    
    // Mettre en évidence la page active
    highlightActivePage();
    
    // Gérer la recherche
    const searchForm = document.querySelector('form[role="search"]');
    if (searchForm) {
        const searchInput = searchForm.querySelector('input[name="search"]');
        searchInput.addEventListener('focus', function() {
            this.parentElement.style.boxShadow = '0 0 0 3px rgba(255, 193, 7, 0.25)';
        });
        
        searchInput.addEventListener('blur', function() {
            this.parentElement.style.boxShadow = '';
        });
        
        // Auto-complétion simple (à améliorer avec une API)
        searchInput.addEventListener('input', debounce(function() {
            if (this.value.length > 2) {
                // Pourrait faire une requête AJAX ici
                console.log('Recherche:', this.value);
            }
        }, 300));
    }
    
    // Fonction pour mettre en évidence la page active
    function highlightActivePage() {
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href === currentPath || 
                (href !== '/' && currentPath.includes(href.replace('/', ''))) ||
                (currentPath === '/' && href === '/')) {
                link.classList.add('active');
                
                // Mettre à jour le parent dropdown si nécessaire
                const dropdown = link.closest('.dropdown');
                if (dropdown) {
                    const dropdownToggle = dropdown.querySelector('.dropdown-toggle');
                    if (dropdownToggle) {
                        dropdownToggle.classList.add('active');
                    }
                }
            } else {
                link.classList.remove('active');
            }
        });
    }
    
    // Fonction debounce pour optimiser les événements
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // Afficher un message de bienvenue dans la console
    console.log('🌿 Navbar Bosten initialisée avec succès!');
});

// Notification pour les nouvelles fonctionnalités
document.addEventListener('alpine:init', () => {
    Alpine.store('navbar', {
        cartCount: 0,
        
        updateCartCount(count) {
            this.cartCount = count;
            
            // Mettre à jour l'affichage du badge
            const cartBadge = document.querySelector('.cart-badge, [title="Mon panier"] .badge');
            if (cartBadge) {
                if (count > 0) {
                    cartBadge.textContent = count;
                    cartBadge.style.display = 'inline-block';
                } else {
                    cartBadge.style.display = 'none';
                }
            }
        }
    });
});