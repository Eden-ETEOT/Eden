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

$erro = '';
// Confirmação após pagamento no celular: só confirma se o token foi pago.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $stmt = $conexao->prepare(
        "SELECT idPagamento, valor FROM pagamento_pix
         WHERE token = :t AND Condominio_idCondominio = :c AND Plano_idPlano = :p
           AND status = 'pago' LIMIT 1"
    );
    $stmt->execute(['t' => $token, 'c' => $filtroCondominio, 'p' => $idPlano]);
    $pg = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($pg) {
        $stmt = $conexao->prepare("UPDATE condominio SET Plano_idPlano = :p WHERE idCondominio = :c");
        $stmt->execute(['p' => $idPlano, 'c' => $filtroCondominio]);
        $_SESSION['plano_ok'] = ['nome' => $plano['nome'], 'valor' => $plano['valor']];
        unset($_SESSION['plano_assinatura'], $_SESSION['plano_pix_token']);
        header('Location: ./sucesso.php');
        exit;
    }
    $erro = 'Pagamento ainda não identificado. Conclua o pagamento no celular e aguarde a confirmação automática.';
}

// Reaproveita token pendente válido ou gera um novo (30 min).
$stmt = $conexao->prepare(
    "SELECT token FROM pagamento_pix
     WHERE Condominio_idCondominio = :c AND Plano_idPlano = :p AND status = 'pendente'
       AND dataExpiracao > NOW() ORDER BY dataCriacao DESC LIMIT 1"
);
$stmt->execute(['c' => $filtroCondominio, 'p' => $idPlano]);
$token = $stmt->fetchColumn() ?: null;
if ($token === null) {
    $conexao->prepare(
        "UPDATE pagamento_pix SET status = 'expirado'
         WHERE Condominio_idCondominio = :c AND status = 'pendente'"
    )->execute(['c' => $filtroCondominio]);
    $token = bin2hex(random_bytes(32));
    $stmt = $conexao->prepare(
        "INSERT INTO pagamento_pix (token, Condominio_idCondominio, Plano_idPlano, valor, dataExpiracao)
         VALUES (:t, :c, :p, :v, DATE_ADD(NOW(), INTERVAL 30 MINUTE))"
    );
    $stmt->execute(['t' => $token, 'c' => $filtroCondominio, 'p' => $idPlano, 'v' => $plano['valor']]);
}
$_SESSION['plano_pix_token'] = $token;
$pixUrl = 'https://eden.gabsprojects.uk/pagamento-pix?token=' . $token;
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
            <p class="pl-lead">Escaneie o QR Code com o celular para pagar<br>
                <strong><?= htmlspecialchars($plano['nome']) ?></strong> ·
                R$ <?= number_format((float) $plano['valor'], 2, ',', '.') ?>/mês</p>
            <div id="qrCode" class="pl-qr" style="display:flex;justify-content:center"></div>
            <div class="pl-chave-label">Aguardando pagamento…</div>
            <div class="pl-chave" style="font-size:12px;word-break:break-all"><?= htmlspecialchars($pixUrl) ?></div>
            <?php if ($erro): ?><p style="color:#b3261e;font-size:14px;margin-top:10px"><?= htmlspecialchars($erro) ?></p><?php endif; ?>
            <form method="post" id="confirmForm" style="margin-top:14px">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <button type="submit" class="pl-btn" style="background:#fff;color:#294633;border:1px solid #294633">Já paguei, confirmar</button>
            </form>
        </div>
    </main>
    <aside class="pl-image-side"></aside>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        new QRCode(document.getElementById('qrCode'), {
            text: <?= json_encode($pixUrl) ?>,
            width: 220,
            height: 220,
            correctLevel: QRCode.CorrectLevel.M
        });
        const token = <?= json_encode($token) ?>;
        const timer = setInterval(async () => {
            try {
                const r = await fetch('./status-pagamento.php?token=' + encodeURIComponent(token));
                const j = await r.json();
                if (j.pago) {
                    clearInterval(timer);
                    document.getElementById('confirmForm').submit();
                } else if (j.expirado) {
                    clearInterval(timer);
                    document.querySelector('.pl-chave-label').textContent = 'Código expirado — recarregue a página para gerar outro.';
                }
            } catch (e) { /* tenta de novo no próximo ciclo */ }
        }, 3000);
    </script>
</body>
</html>
