<?php
// Guard da área do morador: exige login + vínculo ativo de morador em unidade.
// Expõe: $idMorador, $moradorCtx (idMorador, tipoMorador, unidade...).
// Sem vínculo -> volta para a área do síndico.
require_once __DIR__ . '/../Elements/auth.php';
require_once __DIR__ . '/../Elements/ui.php';

$stmt = $conexao->prepare(
    "SELECT m.idMorador, m.tipoMorador,
            un.idUnidade, un.numResid, un.bloco, un.andar, un.metragem, un.ativo,
            un.Condominio_idCondominio
     FROM morador m
     JOIN moradorunidade mu ON mu.Morador_idMorador = m.idMorador AND mu.dataFim IS NULL
     JOIN unidade un ON un.idUnidade = mu.Unidade_idUnidade
     WHERE m.idUsuario = :u LIMIT 1"
);
$stmt->execute(['u' => $idUsuario]);
$moradorCtx = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$moradorCtx) {
    header('Location: ../dashboard.php');
    exit;
}
$idMorador = (int) $moradorCtx['idMorador'];

// Avatar: caminho relativo a morador/
if (!empty($user_foto)) {
    if (strpos($user_foto, './uploads/') === 0) {
        $user_foto = '../' . substr($user_foto, 2);
    } elseif (strpos($user_foto, '/') === false) {
        $user_foto = '../uploads/usuarios/' . $user_foto;
    }
}
$user_type = 'Morador';
