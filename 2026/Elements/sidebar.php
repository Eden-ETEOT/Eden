<?php
// Sidebar Component
// Este arquivo é importado nas páginas principais do dashboard
?>

<aside class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">é</div>
        <div class="sidebar-account">
            <span class="sidebar-account-name">Administração</span>
            <span class="sidebar-account-type">Plano Avançado</span>
        </div>
    </div>

    <nav>
        <ul class="sidebar-menu">
            <!-- Dashboard -->
            <li class="sidebar-menu-item">
                <a href="./dashboard.php" class="sidebar-menu-link active">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    Dashboard
                </a>
            </li>

            <!-- Gerenciamento Administrativo -->
            <li class="sidebar-menu-item">
                <a href="#" class="sidebar-menu-link disabled" onclick="toggleSubmenu('admin'); return false;">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="1"></circle>
                        <circle cx="19" cy="12" r="1"></circle>
                        <circle cx="5" cy="12" r="1"></circle>
                    </svg>
                    Gerenciamento
                    <svg style="width: 12px; height: 12px; margin-left: auto;" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 4 6 8 9 4"></polyline>
                    </svg>
                </a>
                <ul id="submenu-admin" class="sidebar-submenu">
                    <li class="sidebar-submenu-item"><a href="#" class="sidebar-submenu-link">Unidades</a></li>
                    <li class="sidebar-submenu-item"><a href="#" class="sidebar-submenu-link">Serviços</a></li>
                    <li class="sidebar-submenu-item"><a href="#" class="sidebar-submenu-link">Contratos</a></li>
                    <li class="sidebar-submenu-item"><a href="#" class="sidebar-submenu-link">Manutenções</a></li>
                    <li class="sidebar-submenu-item"><a href="#" class="sidebar-submenu-link">Gestão de Usuários</a></li>
                </ul>
            </li>

            <!-- Portaria -->
            <li class="sidebar-menu-item">
                <a href="#" class="sidebar-menu-link disabled" onclick="toggleSubmenu('portaria'); return false;">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                    Portaria
                    <svg style="width: 12px; height: 12px; margin-left: auto;" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 4 6 8 9 4"></polyline>
                    </svg>
                </a>
                <ul id="submenu-portaria" class="sidebar-submenu">
                    <li class="sidebar-submenu-item"><a href="#" class="sidebar-submenu-link">Visitantes</a></li>
                    <li class="sidebar-submenu-item"><a href="#" class="sidebar-submenu-link">Veículos</a></li>
                    <li class="sidebar-submenu-item"><a href="#" class="sidebar-submenu-link">Encomendas</a></li>
                    <li class="sidebar-submenu-item"><a href="#" class="sidebar-submenu-link">Câmeras</a></li>
                </ul>
            </li>

            <!-- Comunicação -->
            <li class="sidebar-menu-item">
                <a href="#" class="sidebar-menu-link" onclick="toggleSubmenu('comunicacao'); return false;">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    Comunicação
                    <svg style="width: 12px; height: 12px; margin-left: auto;" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 4 6 8 9 4"></polyline>
                    </svg>
                </a>
                <ul id="submenu-comunicacao" class="sidebar-submenu">
                    <li class="sidebar-submenu-item"><a href="#" class="sidebar-submenu-link">Chat</a></li>
                    <li class="sidebar-submenu-item"><a href="#" class="sidebar-submenu-link">Feedbacks</a></li>
                    <li class="sidebar-submenu-item"><a href="#" class="sidebar-submenu-link">Mural</a></li>
                    <li class="sidebar-submenu-item"><a href="#" class="sidebar-submenu-link">Atas de Reunião</a></li>
                </ul>
            </li>

            <!-- Áreas & Eventos -->
            <li class="sidebar-menu-item">
                <a href="#" class="sidebar-menu-link disabled" onclick="toggleSubmenu('eventos'); return false;">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    Áreas & Eventos
                    <svg style="width: 12px; height: 12px; margin-left: auto;" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 4 6 8 9 4"></polyline>
                    </svg>
                </a>
                <ul id="submenu-eventos" class="sidebar-submenu">
                    <li class="sidebar-submenu-item"><a href="#" class="sidebar-submenu-link">Reservas</a></li>
                    <li class="sidebar-submenu-item"><a href="#" class="sidebar-submenu-link">Agendadas</a></li>
                </ul>
            </li>

            <!-- Financeiro -->
            <li class="sidebar-menu-item">
                <a href="#" class="sidebar-menu-link disabled" onclick="toggleSubmenu('financeiro'); return false;">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="8"></circle>
                        <path d="M12 6v12M15 9h-6"></path>
                    </svg>
                    Financeiro
                    <svg style="width: 12px; height: 12px; margin-left: auto;" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 4 6 8 9 4"></polyline>
                    </svg>
                </a>
                <ul id="submenu-financeiro" class="sidebar-submenu">
                    <li class="sidebar-submenu-item"><a href="#" class="sidebar-submenu-link">Receitas</a></li>
                    <li class="sidebar-submenu-item"><a href="#" class="sidebar-submenu-link">Despesas</a></li>
                    <li class="sidebar-submenu-item"><a href="#" class="sidebar-submenu-link">Inadimplências</a></li>
                    <li class="sidebar-submenu-item"><a href="#" class="sidebar-submenu-link">Boletos</a></li>
                    <li class="sidebar-submenu-item"><a href="#" class="sidebar-submenu-link">Relatórios</a></li>
                </ul>
            </li>

            <!-- Ocorrências -->
            <li class="sidebar-menu-item">
                <a href="#" class="sidebar-menu-link">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2m0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8m3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"></path>
                    </svg>
                    Ocorrências
                </a>
            </li>

            <!-- Moradores -->
            <li class="sidebar-menu-item">
                <a href="#" class="sidebar-menu-link disabled">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    Moradores
                </a>
            </li>

            <!-- Documentação -->
            <li class="sidebar-menu-item">
                <a href="#" class="sidebar-menu-link disabled">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="12" y1="19" x2="12" y2="11"></line>
                        <line x1="9" y1="16" x2="15" y2="16"></line>
                    </svg>
                    Documentação
                </a>
            </li>

            <!-- Configurações (Seção Admin) -->
            <li class="sidebar-menu-item" style="margin-top: 32px; border-top: 1px solid rgba(255, 255, 255, 0.1); padding-top: 16px;">
                <a href="#" class="sidebar-menu-link disabled">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M12 1v6m0 6v6M4.22 4.22l4.24 4.24m6.08 0l4.24-4.24M1 12h6m6 0h6m-1.78 7.78l-4.24-4.24m-6.08 0l-4.24 4.24"></path>
                    </svg>
                    Configurações
                </a>
            </li>

            <li class="sidebar-menu-item">
                <a href="#" class="sidebar-menu-link disabled">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm0-2a8 8 0 100-16 8 8 0 000 16zm3.54-4.46L10.88 9.88a1 1 0 00-1.41 1.41l4.25 4.25a1 1 0 001.41 0l6.36-6.36a1 1 0 00-1.41-1.41L13.54 15.54z"></path>
                    </svg>
                    Permissões
                </a>
            </li>

            <li class="sidebar-menu-item">
                <a href="#" class="sidebar-menu-link disabled">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M12 6v6l4 2"></path>
                    </svg>
                    Suporte
                </a>
            </li>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <a href="./config/logout.php" class="sidebar-logout">
            <svg style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10 3H5a2 2 0 00-2 2v14c0 1.1.9 2 2 2h5m7-4l4-4m0 0l-4-4m4 4H9"></path>
            </svg>
            Sair
        </a>
    </div>
</aside>

<script>
function toggleSubmenu(id) {
    const submenu = document.getElementById('submenu-' + id);
    if (submenu) {
        submenu.classList.toggle('active');
    }
}
</script>
