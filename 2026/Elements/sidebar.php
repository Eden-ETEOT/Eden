<?php
// Sidebar Component
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

            <!-- Ocorrências -->
            <li class="sidebar-menu-item">
                <a href="#" class="sidebar-menu-link">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2m0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8m3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"></path>
                    </svg>
                    Ocorrências
                </a>
            </li>

            <!-- Itens desabilitados -->
            <li class="sidebar-menu-item">
                <a href="#" class="sidebar-menu-link disabled">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M12 1v6m0 6v6M4.22 4.22l4.24 4.24m6.08 0l4.24-4.24M1 12h6m6 0h6m-1.78 7.78l-4.24-4.24m-6.08 0l-4.24 4.24"></path>
                    </svg>
                    Gerenciamento
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="#" class="sidebar-menu-link disabled">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M12 1v6m0 6v6M4.22 4.22l4.24 4.24m6.08 0l4.24-4.24M1 12h6m6 0h6m-1.78 7.78l-4.24-4.24m-6.08 0l-4.24 4.24"></path>
                    </svg>
                    Portaria
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="#" class="sidebar-menu-link disabled">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M12 1v6m0 6v6M4.22 4.22l4.24 4.24m6.08 0l4.24-4.24M1 12h6m6 0h6m-1.78 7.78l-4.24-4.24m-6.08 0l-4.24 4.24"></path>
                    </svg>
                    Áreas & Eventos
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="#" class="sidebar-menu-link disabled">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M12 1v6m0 6v6M4.22 4.22l4.24 4.24m6.08 0l4.24-4.24M1 12h6m6 0h6m-1.78 7.78l-4.24-4.24m-6.08 0l-4.24 4.24"></path>
                    </svg>
                    Financeiro
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="#" class="sidebar-menu-link disabled">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M12 1v6m0 6v6M4.22 4.22l4.24 4.24m6.08 0l4.24-4.24M1 12h6m6 0h6m-1.78 7.78l-4.24-4.24m-6.08 0l-4.24 4.24"></path>
                    </svg>
                    Moradores
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="#" class="sidebar-menu-link disabled">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M12 1v6m0 6v6M4.22 4.22l4.24 4.24m6.08 0l4.24-4.24M1 12h6m6 0h6m-1.78 7.78l-4.24-4.24m-6.08 0l-4.24 4.24"></path>
                    </svg>
                    Documentação
                </a>
            </li>
            <li class="sidebar-menu-item">
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
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M12 1v6m0 6v6M4.22 4.22l4.24 4.24m6.08 0l4.24-4.24M1 12h6m6 0h6m-1.78 7.78l-4.24-4.24m-6.08 0l-4.24 4.24"></path>
                    </svg>
                    Permissões
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="#" class="sidebar-menu-link disabled">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M12 1v6m0 6v6M4.22 4.22l4.24 4.24m6.08 0l4.24-4.24M1 12h6m6 0h6m-1.78 7.78l-4.24-4.24m-6.08 0l-4.24 4.24"></path>
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
