<?php
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/_layout.php';

function pillStatus(string $st): array {
    return [
        'analise' => ['analysis', 'Em análise', ''],
        'andamento' => ['progress', 'Em andamento', 'progress'],
        'resolvida' => ['done', 'Finalizada', 'done'],
        'cancelada' => ['cancelled', 'Cancelada', 'cancelled'],
    ][$st] ?? ['indef', $st, ''];
}
function pillPrioridade(string $nome): array {
    if (preg_match('/urgente/i', $nome)) return ['urgent', 'Urgente'];
    if (preg_match('/alta/i', $nome)) return ['high', 'Alta'];
    if (preg_match('/m[eé]dia/i', $nome)) return ['medium', 'Média'];
    if (preg_match('/baixa/i', $nome)) return ['low', 'Baixa'];
    return ['indef', 'Indefinida'];
}

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
                throw new Exception('Preencha título, categoria e descrição.');
            }
            $cat = $conexao->prepare("SELECT 1 FROM categoria WHERE idCategoria = :id");
            $cat->execute(['id' => $categoria]);
            if (!$cat->fetchColumn()) throw new Exception('Categoria inválida.');
            $prioridade = (int) $conexao->query("SELECT idPrioridade FROM prioridade WHERE nome = 'Indefinida' LIMIT 1")->fetchColumn();
            if ($prioridade <= 0) throw new Exception('Prioridade padrão indisponível. Tente novamente.');
            $stmt = $conexao->prepare(
                "INSERT INTO chamados (titulo, descricao, dataPedida, status, prioridade_idPrioridade, categoria_idCategoria, morador_idMorador, Condominio_idCondominio)
                 VALUES (:t, :d, NOW(), 'analise', :p, :c, :m, :cond)"
            );
            $stmt->execute(['t' => $titulo, 'd' => $descricao, 'p' => $prioridade, 'c' => $categoria, 'm' => $idMorador, 'condominio' => $filtroCondominio]);
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
            $stmt = $conexao->prepare(
                "UPDATE chamados SET status = 'cancelada'
                 WHERE idChamados = :id AND morador_idMorador = :m AND Condominio_idCondominio = :c AND status IN ('analise','andamento')"
            );
            $stmt->execute(['id' => $id, 'm' => $idMorador, 'c' => $filtroCondominio]);
            if ($stmt->rowCount() === 0) throw new Exception('Não foi possível cancelar esta ocorrência.');
            $msg = 'Ocorrência cancelada.';
        }
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

$tab = $_GET['tab'] ?? 'todos';
$permitidas = ['todos', 'andamento', 'finalizado', 'cancelada'];
if (!in_array($tab, $permitidas, true)) $tab = 'todos';
$filtroStatus = [
    'todos' => '',
    'andamento' => "AND c.status IN ('analise','andamento')",
    'finalizado' => "AND c.status = 'resolvida'",
    'cancelada' => "AND c.status = 'cancelada'",
][$tab];

$stmt = $conexao->prepare(
    "SELECT COUNT(*) FROM chamados WHERE morador_idMorador = :m AND Condominio_idCondominio = :c AND status IN ('analise','andamento')"
);
$stmt->execute(['m' => $idMorador, 'c' => $filtroCondominio]);
$abertas = (int) $stmt->fetchColumn();

$stmt = $conexao->prepare(
    "SELECT c.idChamados, c.titulo, c.descricao, c.status, DATE_FORMAT(c.dataPedida, '%Y-%m-%d') AS dataIso,
            cat.nome AS categoria, p.nome AS prioridade
     FROM chamados c
     JOIN categoria cat ON cat.idCategoria = c.categoria_idCategoria
     JOIN prioridade p ON p.idPrioridade = c.prioridade_idPrioridade
     WHERE c.morador_idMorador = :m AND c.Condominio_idCondominio = :c $filtroStatus
     ORDER BY c.dataPedida DESC, c.idChamados DESC"
);
$stmt->execute(['m' => $idMorador, 'c' => $filtroCondominio]);
$lista = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categorias = $conexao->query("SELECT idCategoria, nome FROM categoria ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

$rotuloTipo = ['proprietario' => 'Proprietário', 'inquilino' => 'Inquilino', 'dependente' => 'Dependente'];
$sub = ($rotuloTipo[$moradia['tipoMorador']] ?? 'Morador') . ' • Apto ' . $moradia['numResid'];
$abas = ['todos' => 'Todos', 'andamento' => 'Em andamento', 'finalizado' => 'Finalizado', 'cancelada' => 'Cancelada'];

moradorHead('Ocorrências');
moradorSidebar('ocorrencias');
moradorHeader($user['nome'] ?? 'Morador', $sub, 'Ocorrências');
moradorFlash($msg, $erro);
?>
            <section class="page-title">
                <h1>Suas Ocorrências</h1>
                <p>Permite consultar, visualizar e excluir as ocorrências, além de registrar novas ocorrências.</p>
            </section>
            <div class="toolbar">
                <div class="alert"><i data-lucide="triangle-alert"></i><?= $abertas ?> ocorrência(s) em aberto</div><button class="btn" data-open="nova"><i data-lucide="plus"></i>Ocorrência</button></div>
            <div class="tabs"><?php foreach ($abas as $k => $rot): ?><a class="tab<?= $tab === $k ? ' active' : '' ?>" href="./ocorrencias.php?tab=<?= $k ?>"><?= $rot ?></a><?php endforeach; ?></div>
            <div class="panel" style="margin-top:14px">
                <?php if (empty($lista)): ?>
                <p class="sub">Nenhuma ocorrência nesta aba.</p>
                <?php else: ?>
                <?php foreach ($lista as $o): [$pc, $pl, $occCls] = pillStatus($o['status']); [$prc, $prl] = pillPrioridade($o['prioridade']); ?>
                <div class="occ <?= $occCls ?>"><i></i>
                    <div class="occ-body">
                        <div class="occ-top">
                            <div>
                                <h3><?= htmlspecialchars($o['titulo']) ?></h3><small>#<?= (int) $o['idChamados'] ?> · <?= htmlspecialchars($o['categoria']) ?> · Registrada em <?= htmlspecialchars($o['dataIso']) ?></small></div>
                            <div><em class="pill <?= $pc ?>"><?= $pl ?></em> <em class="pill <?= $prc ?>"><?= $prl ?></em></div>
                        </div>
                        <p><?= htmlspecialchars($o['descricao']) ?></p>
                        <?php if (in_array($o['status'], ['analise', 'andamento'], true)): ?>
                        <form method="post" style="margin-top:10px" onsubmit="return confirm('Deseja cancelar esta ocorrência?')">
                            <input type="hidden" name="acao" value="cancelar">
                            <input type="hidden" name="id" value="<?= (int) $o['idChamados'] ?>">
                            <button type="submit" class="btn outline" style="font-size:11px;padding:6px 14px">Cancelar ocorrência</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="modal-overlay" id="nova">
                <div class="modal">
                    <div class="modal-title">
                        <h2>Nova Ocorrência</h2><i data-lucide="x" data-close style="cursor:pointer"></i></div>
                    <form method="post" enctype="multipart/form-data" class="modal-content">
                        <input type="hidden" name="acao" value="criar">
                        <div class="field"><label>Título</label><input class="input" name="titulo" placeholder="Descreva o problema brevemente" required></div>
                        <div class="field"><label>Categoria</label><select name="categoria" class="input" required><option value="">Selecione</option><?php foreach ($categorias as $cat): ?><option value="<?= (int) $cat['idCategoria'] ?>"><?= htmlspecialchars($cat['nome']) ?></option><?php endforeach; ?></select></div>
                        <div class="field"><label>Descrição</label><textarea name="descricao" class="input" placeholder="Descreva detalhadamente a ocorrência..." required></textarea></div>
                        <div class="field"><label>Anexo (opcional)</label><input type="file" name="anexo" accept="image/*" class="input"></div>
                        <div class="modal-actions"><button type="button" class="btn outline" data-close>Cancelar</button><button type="submit" class="btn green">Registrar Ocorrência</button></div>
                    </form>
                </div>
            </div>
<?php moradorFoot(); ?>
