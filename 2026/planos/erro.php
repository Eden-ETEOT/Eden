<?php
require_once __DIR__ . '/_guard.php';

$motivo = $_GET['motivo'] ?? 'erro';
$mensagem = $motivo === 'recusado'
    ? 'Pagamento recusado pela operadora. Confira os dados e tente novamente.'
    : 'Não foi possível concluir o pagamento. Tente novamente.';
$voltar = planoSessaoAtual() !== null ? './forma-pagamento.php' : './index.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Falha no pagamento - Eden Systems</title>
    <link rel="stylesheet" href="../CSS/planos.css?v=1">
    <link rel="stylesheet" href="../CSS/fontes.css?v=1">
    <?php include '../Elements/favicon.php'; ?>
</head>
<body class="pl-planos-body">
    <main class="pl-main">
        <a class="pl-back" href="./index.php">Voltar</a>
        <div class="pl-panel">
            <img src="../assets/Logo.png" alt="Éden Systems" class="pl-logo">
            <div class="pl-result-icon err">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="6" y1="6" x2="18" y2="18"></line><line x1="18" y1="6" x2="6" y2="18"></line></svg>
            </div>
            <h1>Falha no pagamento</h1>
            <p class="pl-lead"><?= htmlspecialchars($mensagem) ?></p>
            <a class="pl-btn" href="<?= htmlspecialchars($voltar) ?>">Tentar novamente</a>
        </div>
    </main>
    <aside class="pl-image-side"></aside>
</body>
</html>
