<?php
$menuAtivo = 'perfil';
$tituloPagina = 'Meu Perfil';
include __DIR__ . '/_top.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'salvar') {
    try {
        $nome = trim($_POST['nome'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        if ($nome === '' || $email === '') throw new Exception('Preencha nome e e-mail.');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception('E-mail inválido.');
        $dig = preg_replace('/\D/', '', $telefone);
        if ($dig !== '' && (strlen($dig) < 10 || strlen($dig) > 11)) {
            throw new Exception('Telefone inválido! Use DDD + número.');
        }
        $stmt = $conexao->prepare("SELECT 1 FROM usuario WHERE email = :e AND idUsuario <> :u LIMIT 1");
        $stmt->execute(['e' => $email, 'u' => $idUsuario]);
        if ($stmt->fetchColumn()) throw new Exception('Este e-mail já está em uso.');
        $stmt = $conexao->prepare("UPDATE usuario SET nome = :n, telefone = :t, email = :e WHERE idUsuario = :u");
        $stmt->execute(['n' => $nome, 't' => $telefone !== '' ? $telefone : null, 'e' => $email, 'u' => $idUsuario]);
        $_SESSION['nome'] = $nome;
        $nomeMorador = $nome;
        $msg = 'Dados atualizados com sucesso.';
    } catch (Exception $e) {
        $msg = $e->getMessage();
    }
}

$stmt = $conexao->prepare("SELECT nome, telefone, email FROM usuario WHERE idUsuario = :u");
$stmt->execute(['u' => $idUsuario]);
$dad = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<section class="page-title">
    <h1>Meu Perfil</h1>
    <p>Consulte e atualize suas informações pessoais.</p>
</section>
<?php if ($msg !== ''): ?><div class="panel" style="margin-bottom:14px"><p class="sub"><?= htmlspecialchars($msg) ?></p></div><?php endif; ?>
<div class="panel profile-card">
    <form method="post">
        <input type="hidden" name="acao" value="salvar">
        <div class="profile-top">
            <div class="big-avatar"><i data-lucide="user-round"></i></div>
            <div>
                <h2><?= htmlspecialchars($dad['nome']) ?></h2>
                <p class="sub"><?= htmlspecialchars($papelMorador) ?> • Apartamento <?= htmlspecialchars($moradia['numResid']) ?> • Bloco <?= htmlspecialchars($moradia['bloco']) ?></p>
            </div>
        </div>
        <div class="two">
            <div class="field"><label>Nome completo</label><input class="input" name="nome" value="<?= htmlspecialchars($dad['nome']) ?>" required></div>
            <div class="field"><label>Telefone</label><input class="input" id="telefone" name="telefone" placeholder="(00) 00000-0000" maxlength="15" value="<?= htmlspecialchars($dad['telefone'] ?? '') ?>"></div>
        </div>
        <div class="field"><label>E-mail</label><input class="input" type="email" name="email" value="<?= htmlspecialchars($dad['email']) ?>" required></div>
        <div class="two">
            <div class="field"><label>Apartamento</label><input class="input" value="<?= htmlspecialchars($moradia['numResid']) ?>" disabled></div>
            <div class="field"><label>Bloco</label><input class="input" value="<?= htmlspecialchars($moradia['bloco']) ?>" disabled></div>
        </div>
        <div class="form-actions"><button type="button" class="btn outline" onclick="history.back()">Cancelar</button><button type="submit" class="btn green">Salvar Alterações</button></div>
    </form>
</div>
<script src="../js/mascaras.js"></script>
<?php include __DIR__ . '/_bottom.php'; ?>
