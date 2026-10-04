<?php
// pagamento-pix — página PÚBLICA (sem login) do Pix fake de demonstração.
// Aberta pelo celular via QR Code: /pagamento-pix?token=...
// Apenas marca o token como pago; a confirmação da assinatura acontece
// na tela do computador (2026/planos/pix.php), que consulta o status.
require_once __DIR__ . '/../config/conexao.php';

function tokenValido($t) {
    return is_string($t) && preg_match('/^[0-9a-f]{64}$/', $t) === 1;
}

$token = $_GET['token'] ?? ($_POST['token'] ?? '');
$estado = 'invalido'; // invalido | expirado | pago | pendente | aprovado
$pag = null;
$plano = null;

if (tokenValido($token)) {
    $stmt = $conexao->prepare(
        "SELECT p.token, p.status, p.valor, pl.nome AS plano_nome,
                (p.dataExpiracao > NOW()) AS valida
         FROM pagamento_pix p
         JOIN plano pl ON pl.idPlano = p.Plano_idPlano
         WHERE p.token = :t LIMIT 1"
    );
    $stmt->execute(['t' => $token]);
    $pag = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    if ($pag) {
        if ($pag['status'] === 'pago') {
            $estado = ($_SERVER['REQUEST_METHOD'] === 'POST') ? 'aprovado' : 'pago';
        } elseif ($pag['status'] === 'expirado' || !$pag['valida']) {
            if ($pag['status'] === 'pendente') {
                $conexao->prepare(
                    "UPDATE pagamento_pix SET status = 'expirado'
                     WHERE token = :t AND dataExpiracao <= NOW()"
                )->execute(['t' => $token]);
            }
            $estado = 'expirado';
        } else {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $up = $conexao->prepare(
                    "UPDATE pagamento_pix SET status = 'pago', dataPagamento = NOW()
                     WHERE token = :t AND status = 'pendente'"
                );
                $up->execute(['t' => $token]);
                $estado = 'aprovado';
            } else {
                $estado = 'pendente';
            }
        }
    }
}

$valorFmt = $pag ? number_format((float) $pag['valor'], 2, ',', '.') : '0,00';
$chaveMock = 'eden-pagamentos@eden.tcc';
// Assets: no Alias raiz (/pagamento-pix) usa /beta; senão, o próprio mount.
$sn = $_SERVER['SCRIPT_NAME'] ?? '';
$assetBase = (strpos($sn, '/pagamento-pix') === 0) ? '/beta' : rtrim(dirname(dirname($sn)), '/');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Pagamento Pix - Eden Systems</title>
    <link rel="stylesheet" href="<?= $assetBase ?>/CSS/pagamento-pix.css">
    <link rel="icon" type="image/png" href="<?= $assetBase ?>/assets/Favicon-beta.png">
</head>
<body class="pix-body">
    <div class="pix-wrap">
        <div class="pix-brand">
            <img src="<?= $assetBase ?>/assets/Favicon-beta.png" alt="Eden Systems">
            <strong>Eden Systems<span>Pagamento via Pix</span></strong>
        </div>

        <?php if ($estado === 'pendente'): ?>
        <div class="pix-card">
            <h1>Quase lá!</h1>
            <p class="pix-sub">Confirme o pagamento da assinatura abaixo.</p>
            <div class="pix-plan">
                <div>
                    <div class="nome"><?= htmlspecialchars($pag['plano_nome']) ?></div>
                    <div class="periodo">Cobrança mensal</div>
                </div>
                <div class="pix-valor">R$ <?= $valorFmt ?></div>
            </div>
            <div class="pix-row"><span>Pagador (demo)</span><span>Eden Demo · demo@eden.tcc</span></div>
            <div class="pix-row"><span>Recebedor (demo)</span><span>Eden Systems</span></div>
            <div class="pix-row"><span>Chave Pix (demo)</span><span><?= htmlspecialchars($chaveMock) ?></span></div>
            <div class="pix-chave-box">
                <code id="pixCopiaCola">eden-pagamento-<?= substr($token, 0, 12) ?>-fake</code>
                <button type="button" class="pix-copy" onclick="copiarChave()">Copiar</button>
            </div>
            <form method="post" style="margin-top:14px">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <button type="submit" class="pix-pay">Pagar R$ <?= $valorFmt ?></button>
            </form>
            <p class="pix-note">Demonstração — nenhum valor real é cobrado.</p>
        </div>
        <?php elseif ($estado === 'aprovado' || $estado === 'pago'): ?>
        <div class="pix-card">
            <div class="pix-success">
                <div class="pix-check">✓</div>
                <h1>Pagamento aprovado!</h1>
                <p class="pix-sub"><?= htmlspecialchars($pag['plano_nome'] ?? '') ?> · R$ <?= $valorFmt ?></p>
                <p class="pix-note">Volte à tela do computador — sua assinatura será confirmada automaticamente.</p>
            </div>
        </div>
        <?php elseif ($estado === 'expirado'): ?>
        <div class="pix-card">
            <div class="pix-success">
                <h1>Código expirado</h1>
                <p class="pix-note">Este QR Code venceu (30 min). Gere um novo na tela de assinatura do computador.</p>
            </div>
        </div>
        <?php else: ?>
        <div class="pix-card">
            <div class="pix-success">
                <h1>Link inválido</h1>
                <p class="pix-note">Escaneie novamente o QR Code exibido na tela de assinatura.</p>
            </div>
        </div>
        <?php endif; ?>

        <p class="pix-demo">Ambiente de demonstração Eden Systems — pagamento simulado.</p>
    </div>
    <script>
        function copiarChave() {
            const t = document.getElementById('pixCopiaCola').textContent;
            if (navigator.clipboard) navigator.clipboard.writeText(t).then(() => alert('Código copiado!'));
        }
    </script>
</body>
</html>
