<?php
$menuAtivo = 'ocorrencias';
$tituloPagina = 'Ocorrências';
include __DIR__ . '/_top.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'criar') {
    try {
        $titulo = trim($_POST['titulo'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $categoria = (int) ($_POST['categoria'] ?? 0);
        if ($titulo === '' || $descricao === '' || $categoria <= 0) {
            throw new Exception('Preencha título, categoria e descrição.');
        }
        $stmt = $conexao->prepare("SELECT idPrioridade FROM prioridade WHERE nome = 'Média' LIMIT 1");
        $stmt->execute();
        $priMed = $stmt->fetchColumn();
        if ($priMed === false) {
            $priMed = $conexao->query("SELECT MIN(idPrioridade) FROM prioridade")->fetchColumn();
        }
        $stmt = $conexao->prepare(
            "INSERT INTO chamados (titulo, descricao, dataPedida, status, prioridade_idPrioridade, categoria_idCategoria, morador_idMorador)
             VALUES (:t, :d, NOW(), 'analise', :p, :c, :m)"
        );
        $stmt->execute(['t' => $titulo, 'd' => $descricao, 'p' => $priMed, 'c' => $categoria, 'm' => $moradia['idMorador']]);
        $msg = 'Ocorrência registrada com sucesso.';
    } catch (Exception $e) {
        $msg = $e->getMessage();
    }
}

$stmt = $conexao->prepare(
    "SELECT c.idChamados, c.titulo, c.descricao, c.status, c.dataPedida,
            cat.nome AS categoria, p.nome AS prioridade
     FROM chamados c
     JOIN categoria cat ON cat.idCategoria = c.categoria_idCategoria
     JOIN prioridade p ON p.idPrioridade = c.prioridade_idPrioridade
     JOIN morador m ON m.idMorador = c.morador_idMorador
     WHERE m.idUsuario = :u ORDER BY c.dataPedida DESC"
);
$stmt->execute(['u' => $idUsuario]);
$minhas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conexao->query("SELECT idCategoria, nome FROM categoria ORDER BY nome");
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

$abertas = 0;
foreach ($minhas as $o) {
    if (in_array($o['status'], ['analise', 'andamento'], true)) $abertas++;
}
$pill = ['analise' => 'analysis', 'andamento' => 'analysis', 'resolvida' => 'done', 'cancelada' => 'cancelled'];
$rotulo = ['analise' => 'Em análise', 'andamento' => 'Em andamento', 'resolvida' => 'Finalizada', 'cancelada' => 'Cancelada'];
?>
<section class="page-title">
    <h1>Suas Ocorrências</h1>
    <p>Consulte e registre ocorrências do seu apartamento.</p>
</section>
<?php if ($msg !== ''): ?><div class="panel" style="margin-bottom:14px"><p class="sub"><?= htmlspecialchars($msg) ?></p></div><?php endif; ?>
<div class="toolbar">
    <div class="alert"><i data-lucide="triangle-alert"></i><?= $abertas ?> ocorrência(s) em aberto</div><button class="btn" data-open="nova"><i data-lucide="plus"></i>Ocorrência</button></div>
<div class="tabs"><button class="tab active" data-filtro="">Todos</button><button class="tab" data-filtro="andamento">Em andamento</button><button class="tab" data-filtro="resolvida">Finalizado</button><button class="tab" data-filtro="cancelada">Cancelada</button></div>
<div class="panel" style="margin-top:14px" id="listaOcorrencias">
    <?php if (empty($minhas)): ?>
        <p class="sub">Nenhuma ocorrência registrada ainda.</p>
    <?php else: ?>
        <?php foreach ($minhas as $o): ?>
        <div class="occ" data-status="<?= $o['status'] ?>"><i></i>
            <div class="occ-body">
                <div class="occ-top">
                    <div>
                        <h3><?= htmlspecialchars($o['titulo']) ?></h3><small>OC<?= str_pad((int) $o['idChamados'], 3, '0', STR_PAD_LEFT) ?> · <?= htmlspecialchars($o['categoria']) ?> · Registrada em <?= date('d/m/Y', strtotime($o['dataPedida'])) ?></small></div>
                    <div><em class="pill <?= $pill[$o['status']] ?? 'analysis' ?>"><?= $rotulo[$o['status']] ?? $o['status'] ?></em></div>
                </div>
                <p><?= htmlspecialchars($o['descricao']) ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<div class="modal-overlay" id="nova">
    <div class="modal">
        <div class="modal-title">
            <h2>Nova Ocorrência</h2><i data-lucide="x" data-close style="cursor:pointer"></i></div>
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="acao" value="criar">
                <div class="field"><label>Título</label><input class="input" name="titulo" placeholder="Descreva o problema brevemente" required></div>
                <div class="field"><label>Categoria</label><select name="categoria" required>
                    <option value="">Selecione</option>
                    <?php foreach ($categorias as $cat): ?>
                    <option value="<?= (int) $cat['idCategoria'] ?>"><?= htmlspecialchars($cat['nome']) ?></option>
                    <?php endforeach; ?>
                </select></div>
                <div class="field"><label>Descrição</label><textarea name="descricao" placeholder="Descreva detalhadamente a ocorrência..." required></textarea></div>
                <div class="modal-actions"><button type="button" class="btn outline" data-close>Cancelar</button><button type="submit" class="btn green">Registrar Ocorrência</button></div>
            </form>
        </div>
    </div>
</div>
<script>
document.querySelectorAll('#listaOcorrencias').forEach(() => {});
document.querySelectorAll('.tabs .tab').forEach(t => t.onclick = () => {
    document.querySelectorAll('.tabs .tab').forEach(x => x.classList.remove('active'));
    t.classList.add('active');
    const f = t.dataset.filtro;
    document.querySelectorAll('#listaOcorrencias .occ').forEach(o => {
        const st = o.dataset.status;
        const show = !f || (f === 'andamento' ? (st === 'analise' || st === 'andamento') : st === f);
        o.style.display = show ? '' : 'none';
    });
});
</script>
<?php include __DIR__ . '/_bottom.php'; ?>
