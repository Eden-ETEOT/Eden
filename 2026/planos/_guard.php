<?php
// Guard do fluxo de planos: exige login + perfil síndico.
require_once __DIR__ . '/../Elements/auth.php';

$stmt = $conexao->prepare("SELECT 1 FROM sindico WHERE idUsuario = :u LIMIT 1");
$stmt->execute(['u' => $idUsuario]);
if (!$stmt->fetchColumn()) {
    header('Location: ../dashboard.php');
    exit;
}

function planoSessaoAtual(): ?int {
    $id = $_SESSION['plano_assinatura']['idPlano'] ?? null;
    return ($id !== null && (int) $id > 0) ? (int) $id : null;
}
