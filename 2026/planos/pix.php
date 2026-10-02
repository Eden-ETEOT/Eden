<?php
require_once __DIR__ . '/_guard.php';

$idPlano = planoSessaoAtual();
if ($idPlano === null || $filtroCondominio <= 0) {
    header('Location: ./index.php');
    exit;
}
$stmt = $conexao->prepare("SELECT nome, valor FROM plano WHERE idPlano = :id AND ativo = 1");
$stmt->execute(['id' => $idPlano]);
$plano = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$plano) {
    header('Location: ./index.php');
    exit;
}

if (!isset($_SESSION['plano_pix_chave'])) {
    $hex = bin2hex(random_bytes(16));
    $_SESSION['plano_pix_chave'] = substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-'
        . substr($hex, 12, 4) . '-' . substr($hex, 16, 4) . '-' . substr($hex, 20, 12);
}
$chavePix = $_SESSION['plano_pix_chave'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conexao->prepare("UPDATE condominio SET Plano_idPlano = :p WHERE idCondominio = :c");
    $stmt->execute(['p' => $idPlano, 'c' => $filtroCondominio]);
    $_SESSION['plano_ok'] = ['nome' => $plano['nome'], 'valor' => $plano['valor']];
    unset($_SESSION['plano_assinatura'], $_SESSION['plano_pix_chave']);
    header('Location: ./sucesso.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento por Pix - Eden Systems</title>
    <link rel="stylesheet" href="../CSS/planos.css?v=1">
    <?php include '../Elements/favicon.php'; ?>
</head>
<body class="pl-planos-body">
    <main class="pl-main">
        <a class="pl-back" href="./forma-pagamento.php">Voltar</a>
        <div class="pl-panel">
            <img src="../assets/Logo.png" alt="Éden Systems" class="pl-logo">
            <h1>Pagamento por Pix</h1>
            <p class="pl-lead">Leia o QRCode abaixo ou copie e cole a chave.</p>
            <img src="../assets/pix-qrcode.png" alt="QRCode Pix" class="pl-qr">
            <div class="pl-chave-label">Chave aleatória</div>
            <div class="pl-chave"><?= htmlspecialchars($chavePix) ?></div>
            <form method="post">
                <button type="submit" class="pl-btn">Continuar</button>
            </form>
        </div>
    </main>
    <aside class="pl-image-side"></aside>
</body>
</html>
