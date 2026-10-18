<?php
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/_layout.php';

$msg = '';
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nome = trim($_POST['nome'] ?? '');
        $tel = trim($_POST['telefone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $dig = preg_replace('/\D/', '', $tel);
        if ($nome === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Informe nome e um e-mail válido.');
        }
        if ($dig !== '' && (strlen($dig) < 10 || strlen($dig) > 11)) {
            throw new Exception('Telefone inválido! Use DDD + número.');
        }
        $stmt = $conexao->prepare("UPDATE usuario SET nome = :n, telefone = :t, email = :e WHERE idUsuario = :id");
        $stmt->execute(['n' => $nome, 't' => $tel, 'e' => $email, 'id' => $idUsuario]);
        $user['nome'] = $nome;
        $user['telefone'] = $tel;
        $user['email'] = $email;
        $_SESSION['nome'] = $nome;
        $msg = 'Alterações salvas com sucesso.';
    } catch (PDOException $e) {
        $erro = 'Não foi possível salvar. Verifique os dados e tente novamente.';
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

$rotuloTipo = ['proprietario' => 'Proprietário', 'inquilino' => 'Inquilino', 'dependente' => 'Dependente'];
$sub = ($rotuloTipo[$moradia['tipoMorador']] ?? 'Morador') . ' • Apto ' . $moradia['numResid'];

moradorHead('Configurações');
moradorSidebar('configuracoes');
moradorHeader($user['nome'] ?? 'Morador', $sub, 'Configurações');
moradorFlash($msg, $erro);
?>
            <section class="page-title">
                <h1>Configurações</h1>
                <p>Dados e preferências do morador</p>
            </section>
            <form method="post" class="panel form-card">
                <div class="section-head">Dados do morador</div>
                <div class="two">
                    <div class="field"><label>Nome</label><input class="input" name="nome" value="<?= htmlspecialchars($user['nome'] ?? '') ?>" required></div>
                    <div class="field"><label>Telefone</label><input class="input" name="telefone" value="<?= htmlspecialchars($user['telefone'] ?? '') ?>" placeholder="(00) 00000-0000" maxlength="15"></div>
                </div>
                <div class="field"><label>E-mail</label><input class="input" type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required></div>
                <div class="form-actions" style="margin-top:14px"><button type="submit" class="btn green">Salvar Alterações</button></div>
            </form>
            <div class="panel form-card">
                <div class="section-head">Notificações</div>
                <div class="switch-row">
                    <div><strong>Notificações por E-mail</strong><small>Receber alertas por e-mail</small></div>
                    <div class="switch on disabled"></div>
                </div>
                <div class="switch-row">
                    <div><strong>Notificações por WhatsApp</strong><small>Receber alertas por WhatsApp</small></div>
                    <div class="switch disabled"></div>
                </div>
                <div class="switch-row">
                    <div><strong>Atualizações das minhas ocorrências</strong><small>Notificar quando houver mudança de status</small></div>
                    <div class="switch on disabled"></div>
                </div>
            </div>
<?php moradorFoot(); ?>
