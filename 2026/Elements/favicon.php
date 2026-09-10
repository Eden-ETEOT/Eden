<?php
/**
 * Detecta automaticamente o ambiente (beta ou stable) e injeta o favicon correto.
 * Usado via include no <head> das páginas.
 */
$favicon_uri = $_SERVER['REQUEST_URI'] ?? '/';
$ambiente = 'stable';

if (preg_match('#^/(beta|stable)(/|$)#', $favicon_uri, $m)) {
    $ambiente = $m[1];
} else {
    $caminho = str_replace('\\', '/', __FILE__);
    if (strpos($caminho, '/beta/') !== false) {
        $ambiente = 'beta';
    }
}

?>
<link rel="icon" type="image/png" href="/<?= $ambiente ?>/assets/Favicon-<?= $ambiente ?>.png">
