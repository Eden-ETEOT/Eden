/**
 * Dashboard Script
 * Funcionalidades gerais do dashboard
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar funcionalidades
    initializeSearch();
    initializeUserAvatar();
    attachEventListeners();
    showUrlNotification();
});

/**
 * Exibir notificação com base nos parâmetros da URL (ex: ?tipo=success&msg=...)
 * Usado ao chegar na dashboard vindo de um cadastro/redirecionamento.
 */
function showUrlNotification() {
    const params = new URLSearchParams(window.location.search);
    const type = params.get('tipo') || 'info';
    const message = params.get('msg');
    if (message) {
        showNotification(decodeURIComponent(message), type, 3000);
    }
}

/**
 * Inicializar busca
 */
function initializeSearch() {
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            // Implementar lógica de busca aqui
            console.log('Buscando:', query);
        });
    }
}

/**
 * Inicializar avatar do usuário
 */
function initializeUserAvatar() {
    const userAvatar = document.getElementById('userAvatar');
    const userName = document.getElementById('userName');
    
    if (userName && userAvatar) {
        // Só exibe a inicial quando o avatar não tem foto de perfil
        const hasPhoto = userAvatar.style.backgroundImage && userAvatar.style.backgroundImage !== 'none';
        if (!hasPhoto) {
            const firstLetter = userName.textContent.charAt(0).toUpperCase();
            userAvatar.textContent = firstLetter;
        }
    }
}

/**
 * Anexar event listeners globais
 */
function attachEventListeners() {
    // Notificações
    const notificationIcon = document.querySelector('.notification-icon');
    if (notificationIcon) {
        notificationIcon.addEventListener('click', function() {
            console.log('Abrindo notificações');
        });
    }

    // Perfil do usuário
    const userProfile = document.querySelector('.user-profile');
    if (userProfile) {
        userProfile.addEventListener('click', function() {
            console.log('Abrindo menu do usuário');
        });
    }

    // Ações das linhas da tabela
    const actionDropdowns = document.querySelectorAll('.actions-dropdown');
    actionDropdowns.forEach(dropdown => {
        dropdown.addEventListener('click', function(e) {
            e.stopPropagation();
            console.log('Abrindo ações para:', this.closest('tr'));
        });
    });
}

/**
 * Toggle de submenu da sidebar
 */
function toggleSubmenu(id) {
    const submenu = document.getElementById('submenu-' + id);
    if (submenu) {
        submenu.classList.toggle('active');
        
        // Animar a seta
        const parentLink = submenu.previousElementSibling;
        const arrow = parentLink.querySelector('svg:last-child');
        if (arrow) {
            arrow.style.transform = submenu.classList.contains('active') ? 'rotate(180deg)' : 'rotate(0deg)';
        }
    }
}

/**
 * Formatar número como moeda
 */
function formatCurrency(value) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value);
}

/**
 * Formatar data
 */
function formatDate(date) {
    return new Intl.DateTimeFormat('pt-BR').format(new Date(date));
}

/**
 * Validar campo de entrada
 */
function validateInput(input, type = 'text') {
    const value = input.value.trim();
    
    switch(type) {
        case 'email':
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(value);
        case 'phone':
            const phoneRegex = /^[\d\s\-\(\)]{10,}$/;
            return phoneRegex.test(value);
        case 'number':
            return !isNaN(value) && value !== '';
        default:
            return value.length > 0;
    }
}

/**
 * Mostrar notificação
 */
function showNotification(message, type = 'info', duration = 3000) {
    // Criar elemento de notificação
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 16px 24px;
        background-color: ${getNotificationColor(type)};
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 9999;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, duration);
}

/**
 * Obter cor da notificação
 */
function getNotificationColor(type) {
    const colors = {
        'success': '#4D8F14',
        'error': '#C64539',
        'warning': '#FFBF00',
        'info': '#4C90B2'
    };
    return colors[type] || colors['info'];
}

/**
 * Animar número (contador)
 */
function animateCounter(element, targetValue, duration = 1000) {
    const startValue = 0;
    const increment = targetValue / (duration / 16);
    let currentValue = startValue;
    
    const timer = setInterval(() => {
        currentValue += increment;
        if (currentValue >= targetValue) {
            element.textContent = targetValue;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(currentValue);
        }
    }, 16);
}

/**
 * Logout do usuário
 */
function logout() {
    if (confirm('Tem certeza que deseja sair?')) {
        window.location.href = './config/logout.php';
    }
}

/**
 * Definir modo escuro/claro
 */
function toggleTheme() {
    const html = document.documentElement;
    const isDark = html.getAttribute('data-theme') === 'dark';
    html.setAttribute('data-theme', isDark ? 'light' : 'dark');
    localStorage.setItem('theme', isDark ? 'light' : 'dark');
}

/**
 * Carregar tema salvo
 */
function loadSavedTheme() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
}

// Carregar tema salvo ao iniciar
loadSavedTheme();

// Adições de CSS para animações
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }
`;
document.head.appendChild(style);
