<?php
require_once __DIR__ . '/_guard.php';

$pageTitle = 'Ocorrências';
$menuAtivo = 'ocorrencias';
$msg = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    try {
        if ($acao === 'criar') {
            $titulo = trim($_POST['titulo'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');
            $categoria = (int) ($_POST['categoria'] ?? 0);
            if ($titulo === '' || $descricao === '' || $categoria <= 0) {
                throw new Exception('Preencha todos os campos obrigatórios.');
            }
            $prioridade = (int) $conexao->query("SELECT idPrioridade FROM prioridade WHERE nome = 'Sem prioridade' LIMIT 1")->fetchColumn();
            if ($prioridade <= 0) {
                $stmt = $conexao->prepare("INSERT INTO prioridade (ordem, nome, descricao) VALUES (0, 'Sem prioridade', 'Prioridade ainda não definida')");
                $stmt->execute();
                $prioridade = (int) $conexao->lastInsertId();
            }
            $stmt = $conexao->prepare(
                "INSERT INTO chamados (titulo, descricao, dataPedida, status, prioridade_idPrioridade, categoria_idCategoria, morador_idMorador)
                 VALUES (:titulo, :descricao, NOW(), 'analise', :prioridade, :categoria, :morador)"
            );
            $stmt->execute(['titulo' => $titulo, 'descricao' => $descricao, 'prioridade' => $prioridade, 'categoria' => $categoria, 'morador' => $idMorador]);
            $novoId = (int) $conexao->lastInsertId();
            if (isset($_FILES['anexo']) && $_FILES['anexo']['error'] !== UPLOAD_ERR_NO_FILE) {
                $arq = $_FILES['anexo'];
                $perm = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
                $img = $arq['error'] === UPLOAD_ERR_OK ? getimagesize($arq['tmp_name']) : false;
                if ($img !== false && isset($perm[$img['mime']]) && $arq['size'] <= 5 * 1024 * 1024) {
                    $dir = __DIR__ . '/../uploads/chamados';
                    if (!is_dir($dir)) mkdir($dir, 0755, true);
                    $nomeArq = bin2hex(random_bytes(16)) . '.' . $perm[$img['mime']];
                    if (move_uploaded_file($arq['tmp_name'], $dir . DIRECTORY_SEPARATOR . $nomeArq)) {
                        $stmt = $conexao->prepare("INSERT INTO chamadoAnexo (caminho, nomeArquivo, chamados_idChamados) VALUES (:c, :n, :id)");
                        $stmt->execute(['c' => 'uploads/chamados/' . $nomeArq, 'n' => $arq['name'], 'id' => $novoId]);
                    }
                }
            }
            $msg = 'Ocorrência registrada com sucesso.';
        } elseif ($acao === 'cancelar') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) throw new Exception('Ocorrência inválida.');
            $stmt = $conexao->prepare(
                "UPDATE chamados SET status = 'cancelada'
                 WHERE idChamados = :id AND morador_idMorador = :m AND status IN ('analise', 'andamento')"
            );
            $stmt->execute(['id' => $id, 'm' => $idMorador]);
            $msg = $stmt->rowCount() > 0 ? 'Ocorrência cancelada.' : 'Não foi possível cancelar.';
        }
    } catch (PDOException $e) {
        $erro = 'Erro no banco de dados.';
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

$stmt = $conexao->prepare(
    "SELECT c.idChamados, c.titulo, c.descricao, c.status, c.dataPedida,
            DATE_FORMAT(c.dataPedida, '%d/%m/%Y') AS dataFmt,
            cat.nome AS categoria, p.nome AS prioridade,
            GROUP_CONCAT(ca.caminho ORDER BY ca.idChamadoAnexo SEPARATOR '|') AS anexos
     FROM chamados c
     JOIN categoria cat ON cat.idCategoria = c.categoria_idCategoria
     JOIN prioridade p ON p.idPrioridade = c.prioridade_idPrioridade
     LEFT JOIN chamadoAnexo ca ON ca.chamados_idChamados = c.idChamados
     WHERE c.morador_idMorador = :m
     GROUP BY c.idChamados
     ORDER BY c.dataPedida DESC"
);
$stmt->execute(['m' => $idMorador]);
$ocorrencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categorias = $conexao->query("SELECT idCategoria, nome FROM categoria ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

function morStatusBadge($status) {
    return [
        'analise' => 'badge-status-analise',
        'andamento' => 'badge-status-andamento',
        'resolvida' => 'badge-status-finalizado',
        'cancelada' => 'badge-status-cancelado',
    ][$status] ?? 'badge-status-analise';
}
function morStatusRotulo($status) {
    return [
        'analise' => 'Em análise',
        'andamento' => 'Em andamento',
        'resolvida' => 'Finalizada',
        'cancelada' => 'Cancelada',
    ][$status] ?? $status;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<?php pageHead('Ocorrências - Eden Systems', ['../CSS/tabelas.css', '../CSS/morador.css'], ['https://unpkg.com/lucide@latest'], '..'); ?>
<body>
<div class="dashboard-wrapper">
<?php include __DIR__ . '/sidebar.php'; ?>
<div class="main-content">
<?php include __DIR__ . '/header.php'; ?>
<div class="dashboard-content">
                <h1 class="page-title">Suas Ocorrências</h1>
                <?php banner($msg, $erro); ?>

                <div class="mor-panel">
                    <div class="mor-panel-head">
                        <div>
                            <h2 class="mor-panel-title">Histórico</h2>
                            <p class="mor-panel-sub"><?= count($ocorrencias) ?> ocorrência(s) registrada(s)</p>
                        </div>
                        <button class="btn btn-orange btn-sm" type="button" onclick="morAbrirModal('morNewModal')"><i data-lucide="plus"></i>Ocorrência</button>
                    </div>
                    <?php if (empty($ocorrencias)): ?>
                    <div class="mor-empty">Nenhuma ocorrência registrada ainda.</div>
                    <?php else: ?>
                    <?php foreach ($ocorrencias as $o): ?>
                    <div class="mor-occ<?= in_array($o['status'], ['resolvida', 'cancelada'], true) ? ' done' : '' ?>">
                        <div class="mor-occ-bar"></div>
                        <div class="mor-occ-body">
                            <div class="mor-occ-top">
                                <div>
                                    <h3><?= htmlspecialchars($o['titulo']) ?></h3>
                                    <small>#<?= str_pad((int) $o['idChamados'], 3, '0', STR_PAD_LEFT) ?> · <?= htmlspecialchars($o['categoria']) ?> · <?= htmlspecialchars($o['dataFmt']) ?></small>
                                </div>
                                <div style="display:flex;gap:8px;align-items:center">
                                    <span class="badge <?= morStatusBadge($o['status']) ?>"><?= morStatusRotulo($o['status']) ?></span>
                                    <button type="button" class="tbl-action" title="Visualizar" onclick='morVer(<?= json_encode($o, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'><i data-lucide="eye"></i></button>
                                </div>
                            </div>
                            <p><?= htmlspecialchars(mb_strimwidth($o['descricao'], 0, 160, '...')) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="mor-modal-overlay" id="morNewModal">
        <div class="mor-modal">
            <div class="mor-modal-header">
                <h2>Nova Ocorrência</h2>
                <button type="button" class="mor-modal-close" onclick="morFecharModal('morNewModal')"><i data-lucide="x"></i></button>
            </div>
            <form class="mor-modal-body" method="post" enctype="multipart/form-data">
                <input type="hidden" name="acao" value="criar">
                <div class="mor-field">
                    <label>Título</label>
                    <input type="text" name="titulo" placeholder="Descreva o problema brevemente" required>
                </div>
                <div class="mor-field">
                    <label>Categoria</label>
                    <select name="categoria" required>
                        <option value="">Selecione</option>
                        <?php foreach ($categorias as $cat): ?>
                        <option value="<?= (int) $cat['idCategoria'] ?>"><?= htmlspecialchars($cat['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mor-field">
                    <label>Descrição</label>
                    <textarea name="descricao" placeholder="Descreva detalhadamente a ocorrência..." required></textarea>
                </div>
                <div class="mor-field">
                    <label>Anexo (opcional)</label>
                    <input type="file" name="anexo" accept="image/*">
                </div>
                <div class="mor-form-actions">
                    <button type="button" class="btn btn-green-ghost" onclick="morFecharModal('morNewModal')">Cancelar</button>
                    <button type="submit" class="btn btn-green">Registrar Ocorrência</button>
                </div>
            </form>
        </div>
    </div>

    <div class="mor-modal-overlay" id="morViewModal">
        <div class="mor-modal">
            <div class="mor-modal-header">
                <h2 id="mvTitle"></h2>
                <button type="button" class="mor-modal-close" onclick="morFecharModal('morViewModal')"><i data-lucide="x"></i></button>
            </div>
            <div class="mor-modal-body">
                <p class="mor-panel-sub" id="mvSub"></p>
                <p style="font-size:14px;line-height:1.6;margin:12px 0" id="mvDesc"></p>
                <div id="mvAnexo"></div>
                <form method="post" id="mvCancelForm" style="margin-top:16px">
                    <input type="hidden" name="acao" value="cancelar">
                    <input type="hidden" name="id" id="mvCancelId" value="">
                    <button type="submit" class="btn btn-green-ghost btn-sm" onclick="return confirm('Deseja realmente cancelar esta ocorrência?')">Cancelar ocorrência</button>
                </form>
            </div>
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
        document.addEventListener('keydown', e => { if (e.key === 'Escape') document.querySelectorAll('.mor-modal-overlay.active').forEach(m => m.classList.remove('active')); });
        document.querySelectorAll('.mor-modal-overlay').forEach(m => m.addEventListener('click', e => { if (e.target === m) m.classList.remove('active'); }));
        function morVer(o) {
            document.getElementById('mvTitle').textContent = o.titulo;
            document.getElementById('mvSub').textContent = '#' + String(o.idChamados).padStart(3, '0') + ' · ' + o.categoria + ' · ' + o.dataFmt;
            document.getElementById('mvDesc').textContent = o.descricao;
            const anexos = (o.anexos || '').split('|').filter(Boolean);
            document.getElementById('mvAnexo').innerHTML = anexos.map(a => '<img class="mor-anexo" src="../' + a + '" alt="Anexo">').join('');
            document.getElementById('mvCancelId').value = o.idChamados;
            document.getElementById('mvCancelForm').style.display = (o.status === 'analise' || o.status === 'andamento') ? '' : 'none';
            morAbrirModal('morViewModal');
        }
    </script>
</body>
</html>
