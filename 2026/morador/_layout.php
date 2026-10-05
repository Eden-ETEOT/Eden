<?php
// Layout compartilhado da área do morador (mesmo visual do protótipo).
function moradorHead(string $titulo): void {
    $v = filemtime(__DIR__ . '/css/style.css');
    echo '<!DOCTYPE html><html lang="pt-BR"><head>';
    echo '<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<title>' . htmlspecialchars($titulo) . ' | Éden Systems</title>';
    echo '<link rel="stylesheet" href="./css/style.css?v=' . $v . '">';
    echo '<link rel="stylesheet" href="../CSS/fontes.css?v=1">';
    echo '<link rel="icon" type="image/png" href="../assets/Favicon-beta.png">';
    echo '<script src="../js/lucide.min.js?v=1"></script>';
    echo '</head><body>';
}
function moradorSidebar(string $ativo): void {
    $itens = [
        ['pg' => 'index', 'href' => './index.php', 'label' => 'Dashboard', 'icon' => 'layout-dashboard'],
        ['pg' => 'ocorrencias', 'href' => './ocorrencias.php', 'label' => 'Ocorrências', 'icon' => 'badge-alert'],
        ['pg' => 'apartamento', 'href' => './apartamento.php', 'label' => 'Apartamentos', 'icon' => 'building-2'],
    ];
    $ferr = [
        ['pg' => 'configuracoes', 'href' => './configuracoes.php', 'label' => 'Configurações', 'icon' => 'settings'],
        ['pg' => 'suporte', 'href' => './suporte.php', 'label' => 'Suporte', 'icon' => 'message-square-text'],
    ];
    echo '<aside class="sidebar"><img class="logo" src="./assets/logo-negativo.png" alt="Éden Systems"><div class="line"></div><nav>';
    foreach ($itens as $it) {
        $cls = $ativo === $it['pg'] ? ' class="active"' : '';
        echo '<a' . $cls . ' href="' . $it['href'] . '"><i data-lucide="' . $it['icon'] . '"></i>' . $it['label'] . '</a>';
    }
    echo '</nav><div class="tools"><div class="line"></div><b>FERRAMENTAS</b>';
    foreach ($ferr as $it) {
        $cls = $ativo === $it['pg'] ? ' class="active"' : '';
        echo '<a' . $cls . ' href="' . $it['href'] . '"><i data-lucide="' . $it['icon'] . '"></i>' . $it['label'] . '</a>';
    }
    echo '</div><div class="logout"><div class="line"></div><a href="../logout.php"><i data-lucide="log-out"></i>Log out</a></div></aside>';
}
function moradorHeader(string $nome, string $sub, string $tituloPag): void {
    echo '<div class="app"><header class="header"><div class="header-page">' . htmlspecialchars($tituloPag) . '</div>';
    echo '<div class="profile" id="profileButton"><div class="avatar"><i data-lucide="user-round"></i></div>';
    echo '<div class="profile-info"><strong>' . htmlspecialchars($nome) . '</strong><small>' . htmlspecialchars($sub) . '</small></div><i data-lucide="chevron-down"></i>';
    echo '<div class="profile-menu" id="profileMenu"><div class="head"><strong>' . htmlspecialchars($nome) . '</strong><small>' . htmlspecialchars($sub) . '</small></div>';
    echo '<a href="./perfil.php"><i data-lucide="user"></i>Meu Perfil</a>';
    echo '<a href="./configuracoes.php"><i data-lucide="settings"></i>Configurações</a>';
    echo '<a class="exit" href="../logout.php"><i data-lucide="log-out"></i>Sair</a></div></div></header><main>';
}
function moradorFlash(string $msg, string $erro): void {
    if ($msg !== '') echo '<div class="flash ok">' . htmlspecialchars($msg) . '</div>';
    if ($erro !== '') echo '<div class="flash erro">' . htmlspecialchars($erro) . '</div>';
}
function moradorFoot(): void {
    echo '</main></div><script src="./js/script.js"></script></body></html>';
}
