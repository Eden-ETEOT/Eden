<?php
include './Elements/auth.php';
include './Elements/ui.php';

$pageTitle = 'Perfil';
$menuAtivo = 'perfil';
$msg = '';
$erro = '';

// Papel: síndico > funcionário > morador (morador puro usa a própria página).
$stmt = $conexao->prepare("SELECT 1 FROM sindico WHERE idUsuario = :u LIMIT 1");
$stmt->execute(['u' => $idUsuario]);
$eSindico = (bool) $stmt->fetchColumn();

$funcionario = null;
if (!$eSindico) {
    $stmt = $conexao->prepare("SELECT idFuncionario, funcao FROM funcionario WHERE idUsuario = :u LIMIT 1");
    $stmt->execute(['u' => $idUsuario]);
    $funcionario = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}
$moradia = (!$eSindico && !$funcionario) ? moradorAtivo($conexao, $idUsuario) : null;
if (!$eSindico && !$funcionario && $moradia === null) {
    header('Location: ./configuracoes.php');
    exit;
}
$eMorador = $moradia !== null;
$minhasOcorrencias = 0;
if ($eMorador) {
    $stmt = $conexao->prepare(
        "SELECT COUNT(*) FROM chamados c JOIN morador m ON m.idMorador = c.morador_idMorador
         WHERE m.idUsuario = :u AND c.status IN ('analise', 'andamento')"
    );
    $stmt->execute(['u' => $idUsuario]);
    $minhasOcorrencias = (int) $stmt->fetchColumn();
}

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
        $fotoAtual = $conexao->prepare("SELECT foto FROM usuario WHERE idUsuario = :u");
        $fotoAtual->execute(['u' => $idUsuario]);
        $fotoAtual = $fotoAtual->fetchColumn();
        $fotoNova = $fotoAtual;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
            $arq = $_FILES['foto'];
            $perm = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
            $img = $arq['error'] === UPLOAD_ERR_OK ? getimagesize($arq['tmp_name']) : false;
            if ($img === false || !isset($perm[$img['mime']]) || $arq['size'] > 5 * 1024 * 1024) {
                throw new Exception('Foto inválida. Use JPG ou PNG de até 5 MB.');
            }
            $dir = __DIR__ . '/uploads/usuarios';
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
    } catch (PDOException $e) {
        $erro = 'Erro no banco de dados.';
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

$stmt = $conexao->prepare("SELECT nome, CPF, email, telefone, foto FROM usuario WHERE idUsuario = :u");
$stmt->execute(['u' => $idUsuario]);
$eu = $stmt->fetch(PDO::FETCH_ASSOC);

$rotuloPapel = $eSindico ? 'Síndico' : ($eMorador ? ucfirst($moradia['tipoMorador']) : ($funcionario['funcao'] ?? 'Funcionário'));
$pageTitle = $eSindico ? 'Perfil do Síndico' : ($eMorador ? 'Perfil do Morador' : 'Perfil do Funcionário');

$condominiosGeridos = [];
$desdeGestao = null;
$ocorrenciasAtivas = null;
if ($eSindico) {
    $stmt = $conexao->prepare(
        "SELECT c.idCondominio, c.nome, c.dataCadastro FROM sindico s
         JOIN condominio c ON c.idCondominio = s.Condominio_idCondominio
         WHERE s.idUsuario = :u ORDER BY c.nome"
    );
    $stmt->execute(['u' => $idUsuario]);
    $condominiosGeridos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($condominiosGeridos as $cg) {
        if (!empty($cg['dataCadastro']) && ($desdeGestao === null || $cg['dataCadastro'] < $desdeGestao)) {
            $desdeGestao = $cg['dataCadastro'];
        }
    }
} else {
    $stmt = $conexao->prepare(
        "SELECT COUNT(*) FROM chamados
         WHERE funcionario_idFuncionario = :f AND status IN ('analise', 'andamento')"
    );
    $stmt->execute(['f' => (int) $funcionario['idFuncionario']]);
    $ocorrenciasAtivas = (int) $stmt->fetchColumn();
    $condominiosAtuacao = [];
    try {
        $stmt = $conexao->prepare(
            "SELECT c.idCondominio, c.nome FROM funcionariocondominio fc
             JOIN condominio c ON c.idCondominio = fc.Condominio_idCondominio
             WHERE fc.Funcionario_idFuncionario = :f AND fc.dataDesligamento IS NULL
             ORDER BY c.nome"
        );
        $stmt->execute(['f' => (int) $funcionario['idFuncionario']]);
        $condominiosAtuacao = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $condominiosAtuacao = [];
    }
}

function tempoGestao($desde) {
    if (empty($desde)) return ['—', ''];
    $ini = new DateTime($desde);
    $agora = new DateTime();
    $diff = $ini->diff($agora);
    $partes = [];
    if ($diff->y > 0) $partes[] = $diff->y . ($diff->y > 1 ? ' anos' : ' ano');
    if ($diff->m > 0) $partes[] = $diff->m . ($diff->m > 1 ? ' meses' : ' mês');
    if (empty($partes)) $partes[] = 'menos de 1 mês';
    return [implode(' e ', $partes), 'Desde ' . $ini->format('d/m/Y')];
}
[$tempoTxt, $tempoDesde] = tempoGestao($desdeGestao);

$fotoUrl = !empty($eu['foto'])
    ? './uploads/usuarios/' . $eu['foto']
    : null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<?php pageHead($pageTitle . ' - Eden Systems', ['./CSS/tabelas.css', './CSS/perfil.css'], ['https://unpkg.com/lucide@latest']); ?>
<body>
    <?php layoutOpen(); ?>
                <div class="fd-perfil">
                    <section class="pf-hero">
                        <?php if ($fotoUrl): ?>
                        <div class="pf-avatar" style="background-image:url('<?= htmlspecialchars($fotoUrl) ?>')"></div>
                        <?php else: ?>
                        <div class="pf-avatar"><i data-lucide="user"></i></div>
                        <?php endif; ?>
                        <div>
                            <h2><?= htmlspecialchars($eu['nome'] ?? $user_name) ?></h2>
                            <div class="pf-role"><?= htmlspecialchars($rotuloPapel) ?></div>
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
                            <div class="pf-row"><span>Telefone</span><strong><?= htmlspecialchars($eu['telefone'] ?? '—') ?></strong></div>
                            <div class="pf-row"><span>E-mail</span><strong><?= htmlspecialchars($eu['email'] ?? '') ?></strong></div>
                            <div style="margin-top:18px">
                                <button type="button" class="btn btn-green" onclick="abrirModal('pfEditModal')"><i data-lucide="pencil"></i>Editar perfil</button>
                            </div>
                        </section>
                        <?php if ($eMorador): ?>
                        <section class="pf-card">
                            <h3 class="pf-card-title">Minha Unidade</h3>
                            <div class="pf-row"><span>Condomínio</span><strong><?= htmlspecialchars($moradia['condominioNome']) ?></strong></div>
                            <div class="pf-row"><span>Apartamento</span><strong><?= htmlspecialchars($moradia['numResid']) ?> · Bloco <?= htmlspecialchars($moradia['bloco']) ?></strong></div>
                            <div class="pf-row"><span>Ocorrências em aberto</span><strong><?= (int) $minhasOcorrencias ?></strong></div>
                        </section>
                        <?php endif; ?>
                        <?php if ($eSindico): ?>
                        <section class="pf-card">
                            <h3 class="pf-card-title">Condomínios sob minha gestão</h3>
                            <div class="pf-stat-num"><?= count($condominiosGeridos) ?></div>
                            <div class="pf-stat-label">condomínios ativos</div>
                            <?php foreach ($condominiosGeridos as $cg): ?>
                            <div class="pf-row"><span>#<?= (int) $cg['idCondominio'] ?></span><strong><?= htmlspecialchars($cg['nome']) ?></strong></div>
                            <?php endforeach; ?>
                        </section>
                        <?php else: ?>
                        <section class="pf-card">
                            <h3 class="pf-card-title">Minhas Ocorrências</h3>
                            <div class="pf-stat-num"><?= (int) $ocorrenciasAtivas ?></div>
                            <div class="pf-stat-label">Ocorrências ativas</div>
                        </section>
                        <section class="pf-card">
                            <h3 class="pf-card-title">Onde atuo</h3>
                            <?php if (empty($condominiosAtuacao)): ?>
                            <div class="pf-stat-label">Sem vínculo ativo</div>
                            <?php else: ?>
                            <?php foreach ($condominiosAtuacao as $ca): ?>
                            <div class="pf-row"><span>#<?= (int) $ca['idCondominio'] ?></span><strong><?= htmlspecialchars($ca['nome']) ?></strong></div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </section>
                        <?php endif; ?>
                    </div>

                    <div class="pf-extra">
                        <section class="pf-card">
                            <h3 class="pf-card-title">Informações adicionais</h3>
                            <div class="pf-row"><span>Função</span><strong><?= htmlspecialchars($rotuloPapel) ?></strong></div>
                            <?php if ($eSindico && $tempoTxt !== '—'): ?>
                            <div class="pf-row"><span>Tempo de Gestão</span><strong><?= htmlspecialchars($tempoTxt) ?></strong></div>
                            <div class="pf-row"><span>Início</span><strong><?= htmlspecialchars($tempoDesde) ?></strong></div>
                            <?php endif; ?>
                        </section>
                        <div class="pf-quote">
                            <div class="pf-quote-dot"></div>
                            <p>Trabalhando para um condomínio mais seguro, organizado e valorizado</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="fd-perfil">
    <div class="pf-modal-overlay" id="pfEditModal" onclick="fdFecharClicandoFora(event, 'pfEditModal')">
        <div class="pf-modal">
            <div class="pf-modal-header">
                <h2>Editar perfil</h2>
                <button type="button" class="pf-modal-close" onclick="fecharModal('pfEditModal')"><i data-lucide="x"></i></button>
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
                    <button type="button" class="btn btn-green-ghost" onclick="fecharModal('pfEditModal')">Cancelar</button>
                    <button type="submit" class="btn btn-green">Salvar Informações</button>
                </div>
            </form>
        </div>
    </div>
    </div>

    <script src="<?= assetUrl('./js/app.js') ?>"></script>
    <script>
        lucide.createIcons();
        function pfPreviewFoto(inp) {
            if (inp.files && inp.files[0]) {
                document.getElementById('pfPhotoPreview').style.backgroundImage =
                    "url('" + URL.createObjectURL(inp.files[0]) + "')";
            }
        }
    </script>
</body>
</html>
