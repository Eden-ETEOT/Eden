<?php
// Aceite de convite de morador via token. Fluxo granular em explicacao-sistema-convites.md.
session_start();
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../Elements/convites.php';

$token = trim($_GET['token'] ?? $_POST['token'] ?? $_SESSION['convite_token'] ?? '');
$msg = '';
$erro = '';
$convite = null;

if ($token === '') {
    $erro = 'Link de convite inválido.';
} else {
    // Guarda o token na sessão para atravessar login/cadastro.
    $_SESSION['convite_token'] = $token;
    if (!isset($_SESSION['id_usuario'])) {
        header('Location: ../auth/login.php?convite=1');
        exit;
    }
    [$ok, $dados] = validarConvite($conexao, $token);
    if (!$ok) {
        $erro = $dados;
    } else {
        $convite = $dados;
        $idUsuario = (int) $_SESSION['id_usuario'];
        // E-mail esperado confere com a conta logada?
        $stmt = $conexao->prepare("SELECT email, nome FROM usuario WHERE idUsuario = :u");
        $stmt->execute(['u' => $idUsuario]);
        $eu = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($convite['emailEsperado'] !== null && strcasecmp($convite['emailEsperado'], (string) ($eu['email'] ?? '')) !== 0) {
            $erro = 'Este convite foi emitido para outro e-mail (' . htmlspecialchars($convite['emailEsperado']) . ').';
            $convite = null;
        } elseif (vinculoAtivo($conexao, $idUsuario) !== null) {
            $erro = 'Você já está vinculado a uma unidade. Fale com o síndico para transferência.';
            $convite = null;
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dataNascimento = trim($_POST['data_nascimento'] ?? '');
            [$okA, $msgA] = aceitarConvite($conexao, $token, $idUsuario, $dataNascimento);
            if ($okA) {
                unset($_SESSION['convite_token']);
                $msg = $msgA;
                $convite = null;
            } else {
                $erro = $msgA;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Aceitar convite - Eden Systems</title>
<style>
body{font-family:Inter,Arial,sans-serif;background:#f4f6f4;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}
.card{background:#fff;border-radius:16px;padding:32px;max-width:440px;width:90%;box-shadow:0 4px 24px rgba(0,0,0,.08)}
.card h1{font-size:20px;color:#283E2E;margin:0 0 8px}
.card p{font-size:14px;color:#555;margin:6px 0}
.tag{display:inline-block;background:#283E2E;color:#fff;border-radius:999px;padding:4px 14px;font-size:13px;margin:4px 4px 4px 0}
label{display:block;font-size:13px;font-weight:600;margin:14px 0 6px}
input[type=date]{width:100%;padding:10px;border:1px solid #ccc;border-radius:8px;font-size:15px;box-sizing:border-box}
button{width:100%;margin-top:18px;background:#283E2E;color:#fff;border:none;border-radius:8px;padding:12px;font-size:15px;font-weight:700;cursor:pointer}
button:hover{background:#1e2f23}
.ok{background:#e6f4ea;color:#1a6b2e;border-radius:8px;padding:12px;font-size:14px;margin-bottom:12px}
.err{background:#fdecea;color:#a3261b;border-radius:8px;padding:12px;font-size:14px;margin-bottom:12px}
a{color:#283E2E}
</style>
</head>
<body>
<div class="card">
<h1>Convite de moradia</h1>
<?php if ($msg !== ''): ?><div class="ok"><?= htmlspecialchars($msg) ?></div><p><a href="../dashboard.php">Ir para o painel</a></p><?php endif; ?>
<?php if ($erro !== ''): ?><div class="err"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
<?php if ($convite !== null): ?>
<p>Você foi convidado(a) para:</p>
<p><strong><?= htmlspecialchars($convite['condominioNome']) ?></strong><br>
Bloco <?= htmlspecialchars($convite['bloco']) ?> — Apto <?= htmlspecialchars($convite['numResid']) ?></p>
<p><span class="tag"><?= htmlspecialchars($convite['tipoMorador']) ?></span>
<?php if ($convite['emailEsperado'] === null): ?><span class="tag">link aberto</span><?php endif; ?></p>
<p>Válido até <?= date('d/m/Y H:i', strtotime($convite['dataExpiracao'])) ?></p>
<form method="post">
<input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
<label for="data_nascimento">Sua data de nascimento</label>
<input type="date" id="data_nascimento" name="data_nascimento" required>
<button type="submit">Aceitar convite</button>
</form>
<?php endif; ?>
</div>
</body>
</html>
