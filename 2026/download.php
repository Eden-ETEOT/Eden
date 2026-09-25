<?php
// Download de documentos com checagem de pertencimento ao condomínio da sessão.
include './Elements/auth.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    exit('Documento inválido.');
}
if (!pertenceAoCondominio($conexao, 'documentos', $id, $filtroCondominio)) {
    http_response_code(403);
    exit('Sem permissão para este documento.');
}
$stmt = $conexao->prepare("SELECT caminho, nome FROM documentos WHERE idDocumento = :id LIMIT 1");
$stmt->execute(['id' => $id]);
$doc = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$doc || empty($doc['caminho'])) {
    http_response_code(404);
    exit('Arquivo não encontrado.');
}
$base = realpath(__DIR__ . '/uploads/documentos');
$arq = realpath(__DIR__ . '/' . $doc['caminho']);
if ($arq === false || $base === false || strpos($arq, $base) !== 0 || !is_file($arq)) {
    http_response_code(404);
    exit('Arquivo não encontrado.');
}
header('Content-Type: application/octet-stream');
header('Content-Length: ' . filesize($arq));
header('Content-Disposition: attachment; filename="' . basename($arq) . '"');
readfile($arq);
