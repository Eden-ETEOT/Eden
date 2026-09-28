<?php
// Topo do portal do morador: sessão, gate de papel, sidebar e header do mockup.
// Requer $menuAtivo ('dashboard'|'ocorrencias'|'apartamentos'|'configuracoes'|'suporte'|'perfil')
// e $tituloPagina definidos antes do include.
session_start();
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../Elements/condominio.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../auth/login.php');
    exit;
}
$idUsuario = (int) $_SESSION['id_usuario'];
$moradia = moradorAtivo($conexao, $idUsuario);
$condMor = $moradia === null ? null : (int) $moradia['condominio'];
if ($moradia === null
    || eSindico($conexao, $idUsuario, (int) $condMor)
    || funcaoNoCondominio($conexao, $idUsuario, (int) $condMor) !== null) {
    header('Location: ../dashboard.php');
    exit;
}
$stmt = $conexao->prepare("SELECT nome, foto FROM usuario WHERE idUsuario = :u");
$stmt->execute(['u' => $idUsuario]);
$eu = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['nome' => 'Morador', 'foto' => null];
$nomeMorador = $eu['nome'];
$papelMorador = ucfirst($moradia['tipoMorador']);
$navAtivo = function (string $k) use ($menuAtivo): string {
    return $menuAtivo === $k ? 'class="active"' : '';
};
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($tituloPagina) ?> | Éden Systems</title>
<link rel="stylesheet" href="../CSS/MoradorFront.css?v=<?= filemtime(__DIR__ . '/../CSS/MoradorFront.css') ?>">
<script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
<div class="fm">
<aside class="sidebar">
    <img class="logo" src="../telas-mor-front-eden/assets/logo-negativo.png" alt="Éden Systems">
    <div class="line"></div>
    <nav><a <?= $navAtivo('dashboard') ?> href="./dashboard.php"><i data-lucide="layout-dashboard"></i>Dashboard</a><a <?= $navAtivo('ocorrencias') ?> href="./ocorrencias.php"><i data-lucide="badge-alert"></i>Ocorrências</a><a <?= $navAtivo('apartamentos') ?> href="./apartamentos.php"><i data-lucide="building-2"></i>Apartamentos</a></nav>
    <div class="tools">
        <div class="line"></div><b>FERRAMENTAS</b><a <?= $navAtivo('configuracoes') ?> href="./configuracoes.php"><i data-lucide="settings"></i>Configurações</a><a <?= $navAtivo('suporte') ?> href="./suporte.php"><i data-lucide="message-square-text"></i>Suporte</a></div>
    <div class="logout">
        <div class="line"></div><a href="../logout.php"><i data-lucide="log-out"></i>Log out</a></div>
</aside>
<div class="app">
    <header class="header">
        <div class="header-page"><?= htmlspecialchars($tituloPagina) ?></div>
        <div class="profile" id="profileButton">
            <div class="avatar"><i data-lucide="user-round"></i></div>
            <div class="profile-info"><strong><?= htmlspecialchars($nomeMorador) ?></strong><small><?= htmlspecialchars($papelMorador) ?></small></div><i data-lucide="chevron-down"></i>
            <div class="profile-menu" id="profileMenu">
                <div class="head"><strong><?= htmlspecialchars($nomeMorador) ?></strong><small><?= htmlspecialchars($papelMorador) ?></small></div><a href="./perfil.php"><i data-lucide="user"></i>Meu Perfil</a><a href="./configuracoes.php"><i data-lucide="settings"></i>Configurações</a><a class="exit" href="../logout.php"><i data-lucide="log-out"></i>Sair</a></div>
        </div>
    </header>
    <main>
