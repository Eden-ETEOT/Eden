<?php
// Guard da área do morador: exige login + vínculo ativo de morador.
// Uso: require_once __DIR__ . '/_guard.php'; (primeira linha da página)
// Expõe: $idMorador, $moradia (moradorAtivo), além das vars do auth.php.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../Elements/auth.php';

$moradia = moradorAtivo($conexao, (int) $idUsuario);
if ($moradia === null) {
    header('Location: ../dashboard.php');
    exit;
}
$idMorador = (int) $moradia['idMorador'];
