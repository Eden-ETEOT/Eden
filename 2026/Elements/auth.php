<?php
// Auth centralizado das páginas da área logada.
// Requer estar em 2026/*.php. Garante sessão + $conexao e expõe:
// $idUsuario, $user, $user_name, $user_type, $user_avatar, $user_foto.
session_start();
include __DIR__ . '/../config/conexao.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ./auth/login.php');
    exit;
}

$idUsuario = $_SESSION['id_usuario'];
require_once __DIR__ . '/condominio.php';
$idCondominio = condominioDaSessao();
$filtroCondominio = condominioFiltro(); // -1 sem vínculo: não casa com nada

// Dados do usuário logado (header)
$stmt = $conexao->prepare("SELECT nome, foto, telefone, email FROM usuario WHERE idUsuario = :id");
$stmt->execute(['id' => $idUsuario]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$user_name = $user ? $user['nome'] : 'Usuário';
$user_type = papelUsuario($conexao, $idUsuario, $idCondominio);
$podeGerenciar = podeGerenciar($conexao, $idUsuario, $idCondominio);
$user_avatar = mb_substr($user_name, 0, 1);
$user_foto = ($user && !empty($user['foto'])) ? $user['foto'] : null;
// O cadastro salva só o nome do arquivo; monta o caminho até uploads/usuarios.
if ($user_foto !== null && strpos($user_foto, '/') === false) {
    $user_foto = './uploads/usuarios/' . $user_foto;
}
