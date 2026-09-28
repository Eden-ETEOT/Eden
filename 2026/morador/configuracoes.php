<?php
$menuAtivo = 'configuracoes';
$tituloPagina = 'Configurações';
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
        $msg = 'Alterações salvas com sucesso.';
    } catch (Exception $e) {
        $msg = $e->getMessage();
    }
}

$stmt = $conexao->prepare("SELECT nome, telefone, email FROM usuario WHERE idUsuario = :u");
$stmt->execute(['u' => $idUsuario]);
$dad = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<section class="page-title">
    <h1>Configurações</h1>
    <p>Dados e preferências do morador</p>
</section>
<?php if ($msg !== ''): ?><div class="panel" style="margin-bottom:14px"><p class="sub"><?= htmlspecialchars($msg) ?></p></div><?php endif; ?>
<form method="post">
    <input type="hidden" name="acao" value="salvar">
    <div class="panel form-card">
        <div class="section-head">Dados do morador</div>
        <div class="two">
            <div class="field"><label>Nome</label><input class="input" name="nome" value="<?= htmlspecialchars($dad['nome']) ?>" required></div>
            <div class="field"><label>Telefone</label><input class="input" id="telefoneCfg" name="telefone" placeholder="(00) 00000-0000" maxlength="15" value="<?= htmlspecialchars($dad['telefone'] ?? '') ?>"></div>
        </div>
        <div class="field"><label>E-mail</label><input class="input" type="email" name="email" value="<?= htmlspecialchars($dad['email']) ?>" required></div>
    </div>
    <div class="panel form-card">
        <div class="section-head">Notificações</div>
        <div class="switch-row">
            <div><strong>Notificações por E-mail</strong><small>Receber alertas por e-mail</small></div>
            <div class="switch on"></div>
        </div>
        <div class="switch-row">
            <div><strong>Notificações por WhatsApp</strong><small>Receber alertas por WhatsApp</small></div>
            <div class="switch"></div>
        </div>
        <div class="switch-row">
            <div><strong>Atualizações das minhas ocorrências</strong><small>Notificar quando houver mudança de status</small></div>
            <div class="switch on"></div>
        </div>
    </div>
    <div class="form-actions"><button type="button" class="btn outline" onclick="history.back()">Cancelar</button><button type="submit" class="btn green">Salvar Alterações</button></div>
</form>
<script>
document.getElementById('telefoneCfg')?.addEventListener('input', function (e) {
    let v = (e.target.value || '').replace(/\D/g, '').slice(0, 11);
    if (v.length > 10) e.target.value = v.replace(/(\d{2})(\d{5})(\d{1,4})/, '($1) $2-$3');
    else if (v.length > 6) e.target.value = v.replace(/(\d{2})(\d{4})(\d{1,4})/, '($1) $2-$3');
    else if (v.length > 2) e.target.value = v.replace(/(\d{2})(\d{1,5})/, '($1) $2');
    else e.target.value = v;
});
</script>
<?php include __DIR__ . '/_bottom.php'; ?>
