<?php
// Helpers visuais compartilhados das páginas da área logada.

/**
 * Emite o <head> padrão (metas, título, CSS, JS de CDN e favicon).
 * $cssExtras e $jsExtras mantêm a ordem de inclusão.
 */
/**
 * URL com cache-busting automático (?v=filemtime) para arquivos locais.
 * URLs externas (CDN) passam intactas.
 */
function assetUrl($rel) {
    if (strpos($rel, './') === 0) {
        $file = __DIR__ . '/../' . substr($rel, 2);
        $v = @filemtime($file);
        if ($v) return $rel . '?v=' . $v;
    }
    return $rel;
}

function pageHead($titulo, $cssExtras = [], $jsExtras = []) {
    echo "<head>\n";
    echo "    <meta charset=\"UTF-8\">\n";
    echo "    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n";
    echo '    <title>' . htmlspecialchars($titulo) . "</title>\n";
    $css = array_merge(['./CSS/dashboard.css', './CSS/botoes.css'], $cssExtras);
    foreach ($css as $c) {
        echo '    <link rel="stylesheet" href="' . htmlspecialchars(assetUrl($c)) . "\">\n";
    }
    foreach ($jsExtras as $j) {
        echo '    <script src="' . htmlspecialchars(assetUrl($j)) . "\"></script>\n";
    }
    include __DIR__ . '/favicon.php';
    echo "</head>\n";
}

/**
 * Abre o esqueleto padrão (sidebar + header + conteúdo).
 * Uso: pageHead(...); <body> <?php layoutOpen(); ?> ...conteúdo... </div></div></div>
 */
function layoutOpen() {
    // sidebar.php/header.php usam estas variáveis da página
    global $menuAtivo, $pageTitle, $user_name, $user_type, $user_avatar, $user_foto;
    echo '<div class="dashboard-wrapper">' . "\n";
    include __DIR__ . '/sidebar.php';
    echo '<div class="main-content">' . "\n";
    include __DIR__ . '/header.php';
    echo '<div class="dashboard-content">' . "\n";
}

/**
 * Banner de mensagem de sucesso/erro (mesmo padrão visual em todas as páginas).
 */
function banner($msg, $erro = '') {
    if ($msg !== '' && $msg !== null) {
        echo '<p style="width:100%;padding:8px 12px;border-radius:8px;background:#e9f7ee;color:#1e5c34;border:1px solid #bfe3cb;text-align:center;margin-bottom:16px">' . htmlspecialchars($msg) . "</p>\n";
    }
    if ($erro !== '' && $erro !== null) {
        echo '<p style="width:100%;padding:8px 12px;border-radius:8px;background:#fdecea;color:#8f1d1d;border:1px solid #f5c6c2;text-align:center;margin-bottom:16px">' . htmlspecialchars($erro) . "</p>\n";
    }
}
