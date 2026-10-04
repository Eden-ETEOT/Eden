<?php
require_once __DIR__ . '/_guard.php';

if (isset($_GET['plano'])) {
    $idPlano = (int) $_GET['plano'];
    $stmt = $conexao->prepare("SELECT idPlano FROM plano WHERE idPlano = :id AND ativo = 1");
    $stmt->execute(['id' => $idPlano]);
    if ($stmt->fetchColumn()) {
        $_SESSION['plano_assinatura'] = ['idPlano' => $idPlano];
    }
}

$idPlano = planoSessaoAtual();
if ($idPlano === null) {
    header('Location: ./index.php');
    exit;
}
$stmt = $conexao->prepare("SELECT nome, valor FROM plano WHERE idPlano = :id");
$stmt->execute(['id' => $idPlano]);
$plano = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$plano) {
    unset($_SESSION['plano_assinatura']);
    header('Location: ./index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forma de pagamento - Eden Systems</title>
    <link rel="stylesheet" href="../CSS/planos.css?v=1">
    <link rel="stylesheet" href="../CSS/fontes.css?v=1">
    <?php include '../Elements/favicon.php'; ?>
</head>
<body class="pl-planos-body">
    <main class="pl-main">
        <a class="pl-back" href="./index.php">Voltar</a>
        <div class="pl-panel">
            <img src="../assets/Logo.png" alt="Éden Systems" class="pl-logo">
            <h1>Forma de pagamento</h1>
            <p class="pl-lead">Escolha pagar em pix ou cartão</p>
            <div class="pl-summary">
                <strong><?= htmlspecialchars($plano['nome']) ?></strong><br>
                R$ <?= number_format((float) $plano['valor'], 2, ',', '.') ?> /mês
            </div>
            <form method="get" action="./forma-pagamento.php" id="formaForm"></form>
            <div class="pl-options">
                <div class="pl-option selected" id="optPix" onclick="selecionarForma('pix')">
                    <img class="pl-option-icon" src="../assets/pix-logo.svg" alt="Pix">
                    Pix
                </div>
                <div class="pl-option" id="optCartao" onclick="selecionarForma('cartao')">
                    <svg viewBox="0 0 24 24" fill="currentColor"><rect x="2" y="5" width="20" height="14" rx="2"/><rect x="2" y="9" width="20" height="3" fill="#fff" opacity=".85"/><circle cx="7" cy="15.5" r="1.2" fill="#fff"/></svg>
                    Cartão
                </div>
            </div>
            <button type="button" class="pl-btn" onclick="continuarPagamento()">Continuar</button>
        </div>
    </main>
    <aside class="pl-image-side"></aside>
    <script>
        let formaSelecionada = 'pix';
        function selecionarForma(forma) {
            formaSelecionada = forma;
            document.getElementById('optPix').classList.toggle('selected', forma === 'pix');
            document.getElementById('optCartao').classList.toggle('selected', forma === 'cartao');
        }
        function continuarPagamento() {
            window.location.href = formaSelecionada === 'pix' ? './pix.php' : './cartao.php';
        }
    </script>
</body>
</html>
