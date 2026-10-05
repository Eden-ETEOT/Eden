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
                    <svg class="pl-option-icon pl-pix" viewBox="0 0 16 16" fill="currentColor" aria-label="Pix" role="img"><path d="M11.917 11.71a2.046 2.046 0 0 1-1.454-.602l-2.1-2.1a.4.4 0 0 0-.551 0l-2.108 2.108a2.044 2.044 0 0 1-1.454.602h-.414l2.66 2.66c.83.83 2.177.83 3.007 0l2.667-2.668h-.253zM4.25 4.282c.55 0 1.066.214 1.454.602l2.108 2.108a.39.39 0 0 0 .552 0l2.1-2.1a2.044 2.044 0 0 1 1.453-.602h.253L9.503 1.623a2.127 2.127 0 0 0-3.007 0l-2.66 2.66h.414z"/><path d="m14.377 6.496-1.612-1.612a.307.307 0 0 1-.114.023h-.733c-.379 0-.75.154-1.017.422l-2.1 2.1a1.005 1.005 0 0 1-1.425 0L5.268 5.32a1.448 1.448 0 0 0-1.018-.422h-.9a.306.306 0 0 1-.109-.021L1.623 6.496c-.83.83-.83 2.177 0 3.008l1.618 1.618a.305.305 0 0 1 .108-.022h.901c.38 0 .75-.153 1.018-.421L7.375 8.57a1.034 1.034 0 0 1 1.426 0l2.1 2.1c.267.268.638.421 1.017.421h.733c.04 0 .079.01.114.024l1.612-1.612c.83-.83.83-2.178 0-3.008z"/></svg>
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
