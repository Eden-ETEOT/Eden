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
$tipoRot = $rotuloTipo[$moradia['tipoMorador']] ?? 'Morador';
$sub = $tipoRot . ' • Apto ' . $moradia['numResid'];

moradorHead('Meu Perfil');
moradorSidebar('perfil');
moradorHeader($user['nome'] ?? 'Morador', $sub, 'Meu Perfil');
moradorFlash($msg, $erro);
?>
            <section class="page-title">
                <h1>Meu Perfil</h1>
                <p>Consulte e atualize suas informações pessoais.</p>
            </section>
            <form method="post" class="panel profile-card">
                <div class="profile-top">
                    <div class="big-avatar"><i data-lucide="user-round"></i></div>
                    <div>
                        <h2><?= htmlspecialchars($user['nome'] ?? '') ?></h2>
                        <p class="sub"><?= htmlspecialchars($tipoRot) ?> • Apartamento <?= htmlspecialchars($moradia['numResid']) ?> • Bloco <?= htmlspecialchars($moradia['bloco']) ?></p>
                    </div>
                </div>
                <div class="two">
                    <div class="field"><label>Nome completo</label><input class="input" name="nome" value="<?= htmlspecialchars($user['nome'] ?? '') ?>" required></div>
                    <div class="field"><label>Telefone</label><input class="input" name="telefone" value="<?= htmlspecialchars($user['telefone'] ?? '') ?>" placeholder="(00) 00000-0000" maxlength="15"></div>
                </div>
                <div class="field"><label>E-mail</label><input class="input" type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required></div>
                <div class="two">
                    <div class="field"><label>Apartamento</label><input class="input" value="<?= htmlspecialchars($moradia['numResid']) ?>" disabled></div>
                    <div class="field"><label>Bloco</label><input class="input" value="<?= htmlspecialchars($moradia['bloco']) ?>" disabled></div>
                </div>
                <div class="form-actions"><a class="btn outline" href="./perfil.php" style="text-decoration:none">Cancelar</a><button type="submit" class="btn green">Salvar Alterações</button></div>
            </form>
<?php moradorFoot(); ?>
