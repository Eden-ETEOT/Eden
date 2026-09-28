<?php
require_once __DIR__ . '/_guard.php';

$pageTitle = 'Configurações';
$menuAtivo = 'configuracoes';
$msg = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'salvar') {
    try {
        $nome = trim($_POST['nome'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        if ($nome === '' || $email === '') {
            throw new Exception('Preencha nome e e-mail.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('E-mail inválido.');
        }
        $stmt = $conexao->prepare("SELECT idUsuario FROM usuario WHERE email = :e AND idUsuario <> :u");
        $stmt->execute(['e' => $email, 'u' => $idUsuario]);
        if ($stmt->fetchColumn()) {
            throw new Exception('E-mail já cadastrado.');
        }
        $stmt = $conexao->prepare("UPDATE usuario SET nome = :n, telefone = :t, email = :e WHERE idUsuario = :u");
        $stmt->execute(['n' => $nome, 't' => $telefone !== '' ? $telefone : null, 'e' => $email, 'u' => $idUsuario]);
        $msg = 'Alterações salvas com sucesso.';
        $user_name = $nome;
    } catch (PDOException $e) {
        $erro = 'Erro no banco de dados.';
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

$stmt = $conexao->prepare("SELECT nome, telefone, email FROM usuario WHERE idUsuario = :u");
$stmt->execute(['u' => $idUsuario]);
$eu = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<?php pageHead('Configurações - Eden Systems', ['../CSS/tabelas.css', '../CSS/morador.css'], ['https://unpkg.com/lucide@latest'], '..'); ?>
<body>
<div class="dashboard-wrapper">
<?php include __DIR__ . '/sidebar.php'; ?>
<div class="main-content">
<?php include __DIR__ . '/header.php'; ?>
<div class="dashboard-content">
                <h1 class="page-title">Configurações</h1>
                <?php banner($msg, $erro); ?>

                <form method="post">
                    <input type="hidden" name="acao" value="salvar">
                    <div class="mor-panel">
                        <h2 class="mor-panel-title">Dados do morador</h2>
                        <div class="mor-form-row" style="margin-top:14px">
                            <div class="mor-field">
                                <label>Nome</label>
                                <input type="text" name="nome" value="<?= htmlspecialchars($eu['nome'] ?? '') ?>" required>
                            </div>
                            <div class="mor-field">
                                <label>Telefone</label>
                                <input type="text" name="telefone" value="<?= htmlspecialchars($eu['telefone'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="mor-field">
                            <label>E-mail</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($eu['email'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="mor-panel">
                        <h2 class="mor-panel-title">Notificações</h2>
                        <div class="mor-switch-row">
                            <div><strong>Notificações por E-mail</strong><small>Receber alertas por e-mail</small></div>
                            <label class="mor-switch"><input type="checkbox" id="ntEmail" checked><span class="track"></span></label>
                        </div>
                        <div class="mor-switch-row">
                            <div><strong>Notificações por WhatsApp</strong><small>Receber alertas por WhatsApp</small></div>
                            <label class="mor-switch"><input type="checkbox" id="ntZap"><span class="track"></span></label>
                        </div>
                        <div class="mor-switch-row">
                            <div><strong>Atualizações das minhas ocorrências</strong><small>Notificar quando houver mudança de status</small></div>
                            <label class="mor-switch"><input type="checkbox" id="ntOcc" checked><span class="track"></span></label>
                        </div>
                    </div>
                    <div class="mor-form-actions">
                        <a class="btn btn-green-ghost" href="./dashboard.php">Cancelar</a>
                        <button type="submit" class="btn btn-green">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="<?= assetUrl('../js/app.js') ?>"></script>
    <script>
        if (window.lucide) lucide.createIcons();
        // Preferências de notificação (somente neste navegador).
        ['ntEmail', 'ntZap', 'ntOcc'].forEach(id => {
            const el = document.getElementById(id);
            try {
                const v = localStorage.getItem('eden_' + id);
                if (v !== null) el.checked = v === '1';
                el.addEventListener('change', () => localStorage.setItem('eden_' + id, el.checked ? '1' : '0'));
            } catch (e) { /* sem storage: mantém padrão */ }
        });
    </script>
</body>
</html>
