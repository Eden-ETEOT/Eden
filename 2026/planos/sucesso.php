<?php
require_once __DIR__ . '/_guard.php';

$ok = $_SESSION['plano_ok'] ?? null;
unset($_SESSION['plano_ok']);
if (!$ok) {
    header('Location: ./index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento efetuado - Eden Systems</title>
    <link rel="stylesheet" href="../CSS/planos.css?v=1">
    <?php include '../Elements/favicon.php'; ?>
</head>
<body class="pl-planos-body">
    <main class="pl-main">
        <a class="pl-back" href="../configuracoes.php">Voltar</a>
        <div class="pl-panel">
            <img src="../assets/Logo.png" alt="Éden Systems" class="pl-logo">
            <div class="pl-result-icon ok">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="4 12 10 18 20 6"></polyline></svg>
            </div>
            <h1>Pagamento efetuado</h1>
            <p class="pl-lead">Plano <strong><?= htmlspecialchars($ok['nome']) ?></strong> ativo no seu condomínio
                (R$ <?= number_format((float) $ok['valor'], 2, ',', '.') ?>/mês).</p>
            <a class="pl-btn" href="../dashboard.php">Ir para o painel</a>
        </div>
    </main>
    <aside class="pl-image-side"></aside>
</body>
</html>
