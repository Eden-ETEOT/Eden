<?php
// Polling do Pix fake: informa se o token foi pago no celular.
require_once __DIR__ . '/_guard.php';

header('Content-Type: application/json; charset=utf-8');
$out = ['pago' => false, 'expirado' => false];
$token = $_GET['token'] ?? '';
if (is_string($token) && preg_match('/^[0-9a-f]{64}$/', $token) === 1) {
    $stmt = $conexao->prepare(
        "SELECT status, (dataExpiracao > NOW()) AS valida FROM pagamento_pix
         WHERE token = :t AND Condominio_idCondominio = :c LIMIT 1"
    );
    $stmt->execute(['t' => $token, 'c' => $filtroCondominio]);
    $pg = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($pg) {
        if ($pg['status'] === 'pago') {
            $out['pago'] = true;
        } elseif ($pg['status'] === 'expirado' || !$pg['valida']) {
            $out['expirado'] = true;
        }
    }
}
echo json_encode($out);
