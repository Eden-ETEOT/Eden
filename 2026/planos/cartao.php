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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titular = trim($_POST['titular'] ?? '');
    $numero = preg_replace('/\D/', '', $_POST['numero'] ?? '');
    $modo = $_POST['modo'] ?? '';
    $validade = trim($_POST['validade'] ?? '');
    $cvv = trim($_POST['cvv'] ?? '');
    if ($titular === '' || $numero === '' || $validade === '' || $cvv === '') {
        $erro = 'Preencha todos os campos.';
    } elseif (!in_array($modo, ['Crédito', 'Débito'], true)) {
        $erro = 'Escolha o modo de pagamento.';
    } elseif (strlen($numero) !== 16) {
        $erro = 'Número do cartão inválido. Use os 16 dígitos.';
    } elseif (!preg_match('#^(0[1-9]|[12][0-9]|3[01])/(0[1-9]|1[0-2])$#', $validade)) {
        $erro = 'Validade inválida. Use DD/MM.';
    } elseif (!preg_match('/^\d{3,4}$/', $cvv)) {
        $erro = 'CVV inválido.';
    }
    // Nenhum dado do cartão é armazenado: processamento mock.
    if ($erro === '') {
        $stmt = $conexao->prepare("UPDATE condominio SET Plano_idPlano = :p WHERE idCondominio = :c");
        $stmt->execute(['p' => $idPlano, 'c' => $filtroCondominio]);
        $_SESSION['plano_ok'] = ['nome' => $plano['nome'], 'valor' => $plano['valor']];
        unset($_SESSION['plano_assinatura']);
        header('Location: ./sucesso.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento por cartão - Eden Systems</title>
    <link rel="stylesheet" href="../CSS/planos.css?v=1">
    <?php include '../Elements/favicon.php'; ?>
</head>
<body class="pl-planos-body">
    <main class="pl-main">
        <a class="pl-back" href="./forma-pagamento.php">Voltar</a>
        <div class="pl-panel" style="align-items:stretch;text-align:left">
            <div style="text-align:center">
                <img src="../assets/Logo.png" alt="Éden Systems" class="pl-logo">
                <h1>Pagamento por cartão</h1>
                <p class="pl-lead">Insira os dados abaixo para realizar o pagamento.</p>
            </div>
            <?php if ($erro !== ''): ?><div class="pl-error"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
            <form method="post" class="pl-form">
                <div class="pl-field">
                    <label for="titular">Titular do cartão</label>
                    <input id="titular" name="titular" placeholder="Nome impresso no cartão" required>
                </div>
                <div class="pl-field">
                    <label for="numero">Número do cartão</label>
                    <input id="numero" name="numero" inputmode="numeric" placeholder="0000 0000 0000 0000" maxlength="19" required>
                </div>
                <div class="pl-field">
                    <label for="modo">Modo de pagamento</label>
                    <select id="modo" name="modo" required>
                        <option value="Crédito">Crédito</option>
                        <option value="Débito">Débito</option>
                    </select>
                </div>
                <div class="pl-field">
                    <label for="validade">Data de validade</label>
                    <input id="validade" name="validade" placeholder="DD/MM" maxlength="5" required>
                </div>
                <div class="pl-field">
                    <label for="cvv">Código de segurança</label>
                    <input id="cvv" name="cvv" inputmode="numeric" placeholder="CVV" maxlength="4" required>
                </div>
                <button type="submit" class="pl-btn">Continuar</button>
            </form>
        </div>
    </main>
    <aside class="pl-image-side"></aside>
    <script src="../js/mascaras.js"></script>
</body>
</html>
