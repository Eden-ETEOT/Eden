/**
 * Dashboard Script
 * Funcionalidades gerais do dashboard
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar funcionalidades
    initializeSearch();
    initializeUserAvatar();
    attachEventListeners();
});

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
        const firstLetter = userName.textContent.charAt(0).toUpperCase();
        userAvatar.textContent = firstLetter;
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
        window.location.href = './logout.php';
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

/* =========================================================
   FLUXO DE OCORRÊNCIAS (Adicionar / Editar / Excluir)
   ========================================================= */

function abrirModal(id) {
    document.getElementById(id).classList.add('active');
}

function fecharModal(id) {
    document.getElementById(id).classList.remove('active');
    const erro = document.querySelector('#' + id + ' .modal-error');
    if (erro) erro.textContent = '';
}

/**
 * Abre o modal em modo "Nova ocorrência", com o formulário limpo.
 */
function abrirModalNovo() {
    document.getElementById('modalOcorrenciaTitulo').textContent = 'Nova ocorrência';
    document.getElementById('ocorrenciaAcao').value = 'criar';
    document.getElementById('ocorrenciaId').value = '';
    document.getElementById('formOcorrencia').reset();
    abrirModal('modalOcorrencia');
}

/**
 * Abre o modal em modo "Editar", pré-preenchendo com os dados
 * guardados no atributo data-issue da linha (<tr>) clicada.
 */
function abrirModalEditar(botao) {
    const linha = botao.closest('tr');
    const dados = JSON.parse(linha.dataset.issue);

    document.getElementById('modalOcorrenciaTitulo').textContent = 'Editar ocorrência';
    document.getElementById('ocorrenciaAcao').value = 'editar';
    document.getElementById('ocorrenciaId').value = dados.id;
    document.getElementById('ocorrenciaTitulo').value = dados.titulo || '';
    document.getElementById('ocorrenciaDescricao').value = dados.descricao || '';
    document.getElementById('ocorrenciaCategoria').value = dados.categoria_id;
    document.getElementById('ocorrenciaPrioridade').value = dados.prioridade_id;
    document.getElementById('ocorrenciaStatus').value = dados.status;
    if (dados.morador_id) {
        document.getElementById('ocorrenciaMorador').value = dados.morador_id;
    }

    fecharTodosOsMenus();
    abrirModal('modalOcorrencia');
}

let idOcorrenciaParaExcluir = null;

function abrirModalExcluir(botao) {
    const linha = botao.closest('tr');
    const dados = JSON.parse(linha.dataset.issue);
    idOcorrenciaParaExcluir = dados.id;

    const nome = dados.titulo || dados.categoria_nome || 'esta ocorrência';
    document.getElementById('modalExcluirTexto').textContent =
        `Tem certeza que deseja excluir "${nome}"? Essa ação não pode ser desfeita.`;

    fecharTodosOsMenus();
    abrirModal('modalExcluir');
}

/**
 * Envia o formulário (criar ou editar, dependendo do campo "acao")
 * para o endpoint PHP via fetch, sem recarregar a página até o fim.
 */
async function salvarOcorrencia(event) {
    event.preventDefault();

    const form = document.getElementById('formOcorrencia');
    const erro = document.getElementById('ocorrenciaErro');
    const botaoSalvar = form.querySelector('.modal-submit-btn');

    erro.textContent = '';
    botaoSalvar.disabled = true;
    const textoOriginal = botaoSalvar.textContent;
    botaoSalvar.textContent = 'Salvando...';

    try {
        const resposta = await fetch('./actions/ocorrencias.php', {
            method: 'POST',
            body: new FormData(form)
        });
        const resultado = await resposta.json();

        if (!resultado.ok) {
            erro.textContent = resultado.erro || 'Não foi possível salvar a ocorrência.';
            return;
        }

        window.location.reload();
    } catch (e) {
        erro.textContent = 'Erro de conexão. Tente novamente.';
    } finally {
        botaoSalvar.disabled = false;
        botaoSalvar.textContent = textoOriginal;
    }
}

/**
 * Confirma a exclusão da ocorrência selecionada em abrirModalExcluir().
 */
async function confirmarExclusao() {
    if (!idOcorrenciaParaExcluir) return;

    const erro = document.getElementById('excluirErro');
    const botao = document.getElementById('btnConfirmarExclusao');

    erro.textContent = '';
    botao.disabled = true;
    const textoOriginal = botao.textContent;
    botao.textContent = 'Excluindo...';

    try {
        const dados = new FormData();
        dados.append('acao', 'deletar');
        dados.append('id', idOcorrenciaParaExcluir);

        const resposta = await fetch('./actions/ocorrencias.php', { method: 'POST', body: dados });
        const resultado = await resposta.json();

        if (!resultado.ok) {
            erro.textContent = resultado.erro || 'Não foi possível excluir a ocorrência.';
            return;
        }

        window.location.reload();
    } catch (e) {
        erro.textContent = 'Erro de conexão. Tente novamente.';
    } finally {
        botao.disabled = false;
        botao.textContent = textoOriginal;
    }
}

/* ---------- Dropdown "Ações" de cada linha da tabela ---------- */

function toggleActionsMenu(event, botao) {
    event.stopPropagation();
    const menu = botao.nextElementSibling;
    const jaEstavaAberto = menu.classList.contains('active');
    fecharTodosOsMenus();
    if (!jaEstavaAberto) menu.classList.add('active');
}

function fecharTodosOsMenus() {
    document.querySelectorAll('.actions-menu.active').forEach(menu => menu.classList.remove('active'));
}

// Fecha o menu de ações ao clicar fora dele
document.addEventListener('click', fecharTodosOsMenus);

// Fecha o modal ao clicar fora da caixa (na área escurecida)
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) overlay.classList.remove('active');
        });
    });
});

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
