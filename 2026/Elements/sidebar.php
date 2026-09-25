<?php
// Sidebar Component
// $menuAtivo: chave do item ativo ('dashboard', 'ocorrencias', 'moradores', 'apartamentos', 'documentacao', 'relatorios', 'configuracoes', 'permissoes', 'suporte')
$menuAtivo = $menuAtivo ?? 'dashboard';
function menuAtivoCls($chave, $menuAtivo) {
    return 'sidebar-menu-link' . ($menuAtivo === $chave ? ' active' : '');
}
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <img src="./assets/PNG/logobranca-laranja.png" alt="éden Systems" class="sidebar-brand">
    </div>

    <nav class="sidebar-nav">
        <ul class="sidebar-menu">

            <!-- Dashboard -->
            <li class="sidebar-menu-item">
                <a href="./dashboard.php" class="<?= menuAtivoCls('dashboard', $menuAtivo) ?>">
                    <img class="sidebar-icon" src="./assets/icones/layout-dashboard.svg" alt="Dashboard">
                    Dashboard
                </a>
            </li>

            <!-- Ocorrências -->
            <li class="sidebar-menu-item">
                <a href="./ocorrencias.php" class="<?= menuAtivoCls('ocorrencias', $menuAtivo) ?>">
                    <img class="sidebar-icon" src="./assets/icones/triangle-alert.svg" alt="Ocorrências">
                    Ocorrências
                </a>
            </li>

            <!-- Moradores -->
            <li class="sidebar-menu-item">
                <a href="./moradores.php" class="<?= menuAtivoCls('moradores', $menuAtivo) ?>">
                    <img class="sidebar-icon" src="./assets/icones/users.svg" alt="Moradores">
                    Moradores
                </a>
            </li>

            <!-- Apartamentos -->
            <li class="sidebar-menu-item">
                <a href="./apartamentos.php" class="<?= menuAtivoCls('apartamentos', $menuAtivo) ?>">
                    <img class="sidebar-icon" src="./assets/icones/house.svg" alt="Apartamentos">
                    Apartamentos
                </a>
            </li>

            <!-- Documentação -->
            <li class="sidebar-menu-item">
                <a href="./documentacao.php" class="<?= menuAtivoCls('documentacao', $menuAtivo) ?>">
                    <img class="sidebar-icon" src="./assets/icones/file-text.svg" alt="Documentação">
                    Documentação
                </a>
            </li>

            <!-- Relatórios -->
            <li class="sidebar-menu-item">
                <a href="./relatorios.php" class="<?= menuAtivoCls('relatorios', $menuAtivo) ?>">
                    <img class="sidebar-icon" src="./assets/icones/chart-column.svg" alt="Relatórios">
                    Relatórios
                </a>
            </li>

        </ul>

        <div class="sidebar-admin">
            <ul class="sidebar-menu">

            <!-- Configurações -->
            <li class="sidebar-menu-item">
                <a href="./configuracoes.php" class="<?= menuAtivoCls('configuracoes', $menuAtivo) ?>">
                    <img class="sidebar-icon" src="./assets/icones/settings.svg" alt="Configurações">
                    Configurações
                </a>
            </li>

            <!-- Permissões -->
            <li class="sidebar-menu-item">
                <a href="./permissoes.php" class="<?= menuAtivoCls('permissoes', $menuAtivo) ?>">
                    <img class="sidebar-icon" src="./assets/icones/lock-keyhole.svg" alt="Permissões">
                    Permissões
                </a>
            </li>

            <!-- Suporte -->
            <li class="sidebar-menu-item">
                <a href="./suporte.php" class="<?= menuAtivoCls('suporte', $menuAtivo) ?>">
                    <img class="sidebar-icon" src="./assets/icones/message-square.svg" alt="Suporte">
                    Suporte
                </a>
            </li>
            </ul>
        </div>
    </nav>

    <div class="sidebar-footer">
        <a href="./logout.php" class="sidebar-logout">
<img style="width: 16px; height: 16px;" src="./assets/icones/log-out.svg" alt="Sair">
            Sair
        </a>
    </div>
</aside>