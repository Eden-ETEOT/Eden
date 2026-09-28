<?php
require_once __DIR__ . '/_guard.php';

$pageTitle = 'Meu Perfil';
$menuAtivo = 'perfil';
$msg = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'salvar') {
    try {
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
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
        $stmt = $conexao->prepare("SELECT foto FROM usuario WHERE idUsuario = :u");
        $stmt->execute(['u' => $idUsuario]);
        $fotoNova = $stmt->fetchColumn();
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
            $arq = $_FILES['foto'];
            $perm = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
            $img = $arq['error'] === UPLOAD_ERR_OK ? getimagesize($arq['tmp_name']) : false;
            if ($img === false || !isset($perm[$img['mime']]) || $arq['size'] > 5 * 1024 * 1024) {
                throw new Exception('Foto inválida. Use JPG ou PNG de até 5 MB.');
            }
            $dir = __DIR__ . '/../uploads/usuarios';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $fotoNova = uniqid('foto_') . '.' . $perm[$img['mime']];
            if (!move_uploaded_file($arq['tmp_name'], $dir . DIRECTORY_SEPARATOR . $fotoNova)) {
                throw new Exception('Não foi possível salvar a foto.');
            }
        }
        $stmt = $conexao->prepare(
            "UPDATE usuario SET nome = :n, email = :e, telefone = :t, foto = :f WHERE idUsuario = :u"
        );
        $stmt->execute([
            'n' => $nome, 'e' => $email,
            't' => $telefone !== '' ? $telefone : null,
            'f' => $fotoNova, 'u' => $idUsuario,
        ]);
        $msg = 'Perfil atualizado com sucesso.';
        $user_name = $nome;
    } catch (PDOException $e) {
        $erro = 'Erro no banco de dados.';
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

$stmt = $conexao->prepare(
    "SELECT u.nome, u.CPF, u.email, u.telefone, u.foto, m.dataNascimento, m.tipoMorador
     FROM usuario u LEFT JOIN morador m ON m.idUsuario = u.idUsuario
     WHERE u.idUsuario = :u LIMIT 1"
);
$stmt->execute(['u' => $idUsuario]);
$eu = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $conexao->prepare(
    "SELECT COUNT(*) FROM chamados WHERE morador_idMorador = :m AND status IN ('analise', 'andamento')"
);
$stmt->execute(['m' => $idMorador]);
$ocorrenciasAtivas = (int) $stmt->fetchColumn();

$stmt = $conexao->prepare("SELECT nome FROM condominio WHERE idCondominio = :c");
$stmt->execute(['c' => $moradorCtx['Condominio_idCondominio']]);
$nomeCondominio = $stmt->fetchColumn() ?: '—';

$fotoUrl = !empty($eu['foto'])
    ? (strpos($eu['foto'], '/') === false ? '../uploads/usuarios/' . $eu['foto'] : $eu['foto'])
    : null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<?php pageHead('Meu Perfil - Eden Systems', ['../CSS/tabelas.css', '../CSS/perfil.css'], ['https://unpkg.com/lucide@latest'], '..'); ?>
<body>
<div class="dashboard-wrapper">
<?php include __DIR__ . '/sidebar.php'; ?>
<div class="main-content">
<?php include __DIR__ . '/header.php'; ?>
<div class="dashboard-content">
                <section class="pf-hero">
                    <?php if ($fotoUrl): ?>
                    <div class="pf-avatar" style="background-image:url('<?= htmlspecialchars($fotoUrl) ?>')"></div>
                    <?php else: ?>
                    <div class="pf-avatar"><i data-lucide="user"></i></div>
                    <?php endif; ?>
                    <div>
                        <h2><?= htmlspecialchars($eu['nome'] ?? $user_name) ?></h2>
                        <div class="pf-role">Morador</div>
                        <div class="pf-contact">
                            <span><i data-lucide="mail"></i><?= htmlspecialchars($eu['email'] ?? '') ?></span>
                            <span><i data-lucide="phone"></i><?= htmlspecialchars($eu['telefone'] ?? '—') ?></span>
                        </div>
                    </div>
                </section>
                <?php banner($msg, $erro); ?>

                <div class="pf-grid">
                    <section class="pf-card">
                        <h3 class="pf-card-title"><i data-lucide="user"></i>Dados Pessoais</h3>
                        <div class="pf-row"><span>Nome Completo</span><strong><?= htmlspecialchars($eu['nome'] ?? '') ?></strong></div>
                        <div class="pf-row"><span>CPF</span><strong><?= htmlspecialchars($eu['CPF'] ?? '') ?></strong></div>
                        <div class="pf-row"><span>Data de nascimento</span><strong><?= !empty($eu['dataNascimento']) ? date('d/m/Y', strtotime($eu['dataNascimento'])) : '—' ?></strong></div>
                        <div class="pf-row"><span>Telefone</span><strong><?= htmlspecialchars($eu['telefone'] ?? '—') ?></strong></div>
                        <div class="pf-row"><span>E-mail</span><strong><?= htmlspecialchars($eu['email'] ?? '') ?></strong></div>
                        <div style="margin-top:18px">
                            <button type="button" class="btn btn-green" onclick="morAbrirModal('pfEditModal')"><i data-lucide="pencil"></i>Editar perfil</button>
                        </div>
                    </section>
                    <section class="pf-card">
                        <h3 class="pf-card-title">Minhas Ocorrências</h3>
                        <div class="pf-stat-num"><?= (int) $ocorrenciasAtivas ?></div>
                        <div class="pf-stat-label">Ocorrências ativas</div>
                    </section>
                </div>

                <div class="pf-extra">
                    <section class="pf-card">
                        <h3 class="pf-card-title">Informações adicionais</h3>
                        <div class="pf-row"><span>Tipo</span><strong><?= htmlspecialchars(ucfirst($eu['tipoMorador'] ?? '')) ?></strong></div>
                        <div class="pf-row"><span>Apartamento</span><strong><?= htmlspecialchars($moradorCtx['numResid']) ?> · Bloco <?= htmlspecialchars($moradorCtx['bloco']) ?></strong></div>
                        <div class="pf-row"><span>Condomínio</span><strong><?= htmlspecialchars($nomeCondominio) ?></strong></div>
                    </section>
                    <div class="pf-quote">
                        <div class="pf-quote-dot"></div>
                        <p>Trabalhando para um condomínio mais seguro, organizado e valorizado</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="pf-modal-overlay" id="pfEditModal">
        <div class="pf-modal">
            <div class="pf-modal-header">
                <h2>Editar perfil do morador</h2>
                <button type="button" class="pf-modal-close" onclick="morFecharModal('pfEditModal')"><i data-lucide="x"></i></button>
            </div>
            <form class="pf-modal-body" method="post" enctype="multipart/form-data">
                <input type="hidden" name="acao" value="salvar">
                <div class="pf-photo-row">
                    <div class="pf-photo-preview" id="pfPhotoPreview"<?= $fotoUrl ? " style=\"background-image:url('" . htmlspecialchars($fotoUrl) . "')\"" : '' ?>>
                        <?php if (!$fotoUrl): ?><i data-lucide="user"></i><?php endif; ?>
                        <button type="button" class="pf-avatar-cam" title="Alterar imagem" onclick="document.getElementById('pfFotoInput').click()"><i data-lucide="camera"></i></button>
                    </div>
                    <div>
                        <div class="pf-photo-meta">Foto de perfil</div>
                        <button type="button" class="pf-upload-btn" onclick="document.getElementById('pfFotoInput').click()"><i data-lucide="upload"></i>Alterar imagem</button>
                        <div class="pf-photo-hint">Formatos: JPG, PNG</div>
                    </div>
                </div>
                <input type="file" id="pfFotoInput" name="foto" accept="image/jpeg,image/png" hidden onchange="pfPreviewFoto(this)">
                <div class="pf-field">
                    <label>Nome completo</label>
                    <input type="text" name="nome" value="<?= htmlspecialchars($eu['nome'] ?? '') ?>" required>
                </div>
                <div class="pf-field">
                    <label>E-mail</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($eu['email'] ?? '') ?>" required>
                </div>
                <div class="pf-field">
                    <label>Telefone</label>
                    <input type="text" name="telefone" value="<?= htmlspecialchars($eu['telefone'] ?? '') ?>">
                </div>
                <div class="pf-form-actions">
                    <button type="button" class="btn btn-green-ghost" onclick="morFecharModal('pfEditModal')">Cancelar</button>
                    <button type="submit" class="btn btn-green">Salvar Informações</button>
                </div>
            </form>
        </div>
    </div>

    <script src="<?= assetUrl('../js/app.js') ?>"></script>
    <script>
        if (window.lucide) lucide.createIcons();
        function morAbrirModal(id) {
            document.getElementById(id).classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        function morFecharModal(id) {
            document.getElementById(id).classList.remove('active');
            document.body.style.overflow = '';
        }
        document.querySelectorAll('.pf-modal-overlay').forEach(m => m.addEventListener('click', e => { if (e.target === m) morFecharModal(m.id); }));
        document.addEventListener('keydown', e => { if (e.key === 'Escape') document.querySelectorAll('.pf-modal-overlay.active').forEach(m => m.classList.remove('active')); });
        function pfPreviewFoto(inp) {
            if (inp.files && inp.files[0]) {
                document.getElementById('pfPhotoPreview').style.backgroundImage =
                    "url('" + URL.createObjectURL(inp.files[0]) + "')";
            }
        }
    </script>
</body>
</html>
