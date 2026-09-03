<?php
// Sidebar Component
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <?php $cond_name = $cond_name ?? 'Condomínio'; ?>
        <?php if (!empty($cond_foto)): ?>
            <div class="sidebar-logo sidebar-logo-img" style="background-image: url('<?= htmlspecialchars($cond_foto) ?>'); background-size: cover; background-position: center;"></div>
        <?php else: ?>
            <div class="sidebar-logo">é</div>
        <?php endif; ?>
        <div class="sidebar-account">
            <span class="sidebar-account-name"><?= htmlspecialchars($cond_name) ?></span>
            <span class="sidebar-account-type">Administração</span>
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
                <a href="#" class="sidebar-menu-link">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    Moradores
                </a>
            </li>

            <!-- Apartamentos -->
            <li class="sidebar-menu-item">
                <a href="#" class="sidebar-menu-link">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M3 21h18"></path>
                        <path d="M5 21V7l8-4 8 4v14"></path>
                        <path d="M9 9h1m4 0h1M9 13h1m4 0h1M9 17h1m4 0h1"></path>
                    </svg>
                    Apartamentos
                </a>
            </li>

            <!-- Documentação -->
            <li class="sidebar-menu-item">
                <a href="#" class="sidebar-menu-link">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="8" y1="13" x2="16" y2="13"></line>
                        <line x1="8" y1="17" x2="16" y2="17"></line>
                    </svg>
                    Documentação
                </a>
            </li>

            <!-- Relatórios -->
            <li class="sidebar-menu-item">
                <a href="#" class="sidebar-menu-link">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <line x1="18" y1="20" x2="18" y2="10"></line>
                        <line x1="12" y1="20" x2="12" y2="4"></line>
                        <line x1="6" y1="20" x2="6" y2="14"></line>
                    </svg>
                    Relatórios
                </a>
            </li>

            <li class="sidebar-section-label">Administração</li>

            <!-- Configurações -->
            <li class="sidebar-menu-item">
                <a href="#" class="sidebar-menu-link">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M12 1v6m0 6v6M4.22 4.22l4.24 4.24m6.08 0l4.24-4.24M1 12h6m6 0h6m-1.78 7.78l-4.24-4.24m-6.08 0l-4.24 4.24"></path>
                    </svg>
                    Configurações
                </a>
            </li>

            <!-- Permissões -->
            <li class="sidebar-menu-item">
                <a href="#" class="sidebar-menu-link">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                    Permissões
                </a>
            </li>

            <!-- Suporte -->
            <li class="sidebar-menu-item">
                <a href="#" class="sidebar-menu-link">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M4 15v-3a8 8 0 0 1 16 0v3"></path>
                        <path d="M21 16a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-1a2 2 0 0 1 2-2h3zm-18 0a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-1a2 2 0 0 0-2-2H3z"></path>
                    </svg>
                    Suporte
                </a>
            </li>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <a href="./logout.php" class="sidebar-logout">
            <svg style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10 3H5a2 2 0 00-2 2v14c0 1.1.9 2 2 2h5m7-4l4-4m0 0l-4-4m4 4H9"></path>
            </svg>
            Log out
        </a>
    </div>
</aside>