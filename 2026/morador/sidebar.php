<?php
// Sidebar da área do morador (mesmo design system da sidebar do síndico).
// $menuAtivo: 'dashboard', 'ocorrencias', 'apartamentos', 'configuracoes', 'suporte'.
$menuAtivo = $menuAtivo ?? 'dashboard';
function moradorMenuAtivoCls($chave, $menuAtivo) {
    return 'sidebar-menu-link' . ($menuAtivo === $chave ? ' active' : '');
}
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <a href="./dashboard.php" title="Ir para o painel">
            <img src="../assets/PNG/logobranca-laranja.png" alt="éden Systems" class="sidebar-brand">
        </a>
    </div>

    <nav class="sidebar-nav">
        <ul class="sidebar-menu">

            <!-- Dashboard -->
            <li class="sidebar-menu-item">
                <a href="./dashboard.php" class="<?= moradorMenuAtivoCls('dashboard', $menuAtivo) ?>">
                    <img class="sidebar-icon" src="../assets/icones/layout-dashboard.svg" alt="Dashboard">
                    Dashboard
                </a>
            </li>

            <!-- Ocorrências -->
            <li class="sidebar-menu-item">
                <a href="./ocorrencias.php" class="<?= moradorMenuAtivoCls('ocorrencias', $menuAtivo) ?>">
                    <img class="sidebar-icon" src="../assets/icones/triangle-alert.svg" alt="Ocorrências">
                    Ocorrências
                </a>
            </li>

            <!-- Apartamentos -->
            <li class="sidebar-menu-item">
                <a href="./apartamentos.php" class="<?= moradorMenuAtivoCls('apartamentos', $menuAtivo) ?>">
                    <img class="sidebar-icon" src="../assets/icones/house.svg" alt="Apartamentos">
                    Apartamentos
                </a>
            </li>

        </ul>

        <div class="sidebar-admin">
            <ul class="sidebar-menu">

            <!-- Configurações -->
            <li class="sidebar-menu-item">
                <a href="./configuracoes.php" class="<?= moradorMenuAtivoCls('configuracoes', $menuAtivo) ?>">
                    <img class="sidebar-icon" src="../assets/icones/settings.svg" alt="Configurações">
                    Configurações
                </a>
            </li>

            <!-- Suporte -->
            <li class="sidebar-menu-item">
                <a href="./suporte.php" class="<?= moradorMenuAtivoCls('suporte', $menuAtivo) ?>">
                    <img class="sidebar-icon" src="../assets/icones/message-square.svg" alt="Suporte">
                    Suporte
                </a>
            </li>
            </ul>
        </div>
    </nav>

    <div class="sidebar-footer">
        <a href="../logout.php" class="sidebar-logout">
            <img style="width: 16px; height: 16px;" src="../assets/icones/log-out.svg" alt="Sair">
            Sair
        </a>
    </div>
</aside>
