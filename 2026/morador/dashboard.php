<?php
$menuAtivo = 'dashboard';
$tituloPagina = 'Painel de Controle';
include __DIR__ . '/_top.php';

$stmt = $conexao->prepare(
    "SELECT COUNT(*) FROM chamados c JOIN morador m ON m.idMorador = c.morador_idMorador
     WHERE m.idUsuario = :u"
);
$stmt->execute(['u' => $idUsuario]);
$total = (int) $stmt->fetchColumn();

$stmt = $conexao->prepare(
    "SELECT COUNT(*) FROM chamados c JOIN morador m ON m.idMorador = c.morador_idMorador
     WHERE m.idUsuario = :u AND c.status IN ('analise','andamento')"
);
$stmt->execute(['u' => $idUsuario]);
$abertas = (int) $stmt->fetchColumn();

$stmt = $conexao->prepare(
    "SELECT COUNT(*) FROM chamados c JOIN morador m ON m.idMorador = c.morador_idMorador
     WHERE m.idUsuario = :u AND c.status = 'resolvida'"
);
$stmt->execute(['u' => $idUsuario]);
$finalizadas = (int) $stmt->fetchColumn();

$stmt = $conexao->prepare(
    "SELECT c.idChamados, c.titulo, c.descricao, c.status, c.dataPedida
     FROM chamados c JOIN morador m ON m.idMorador = c.morador_idMorador
     WHERE m.idUsuario = :u ORDER BY c.dataPedida DESC LIMIT 2"
);
$stmt->execute(['u' => $idUsuario]);
$recentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pill = ['analise' => 'analysis', 'andamento' => 'analysis', 'resolvida' => 'done', 'cancelada' => 'cancelled'];
$rotulo = ['analise' => 'Em análise', 'andamento' => 'Em andamento', 'resolvida' => 'Finalizada', 'cancelada' => 'Cancelada'];
?>
<section class="page-title">
    <h1>Painel de Controle</h1>
    <p>Visão rápida das suas ocorrências e informações vinculadas ao seu apartamento.</p>
</section>
<section class="cards">
    <article class="stat hot">
        <div class="ico orange"><i data-lucide="clipboard-list"></i></div><strong><?= $total ?></strong>
        <h3>Total de Ocorrências</h3>
        <p>Registradas por você</p>
    </article>
    <article class="stat">
        <div class="ico blue"><i data-lucide="clock-3"></i></div><strong><?= $abertas ?></strong>
        <h3>Em andamento</h3>
        <p>Aguardando ou em análise</p>
    </article>
    <article class="stat">
        <div class="ico green"><i data-lucide="circle-check-big"></i></div><strong><?= $finalizadas ?></strong>
        <h3>Finalizadas</h3>
        <p>Ocorrências concluídas</p>
    </article>
</section>
<section class="grid">
    <div class="panel">
        <div class="panel-head">
            <div>
                <h2 class="panel-title">Ocorrências recentes</h2>
                <p class="sub">Acompanhe suas solicitações mais recentes</p>
            </div><a class="btn" href="./ocorrencias.php"><i data-lucide="plus"></i>Ocorrência</a></div>
        <?php if (empty($recentes)): ?>
            <p class="sub">Nenhuma ocorrência registrada ainda.</p>
        <?php else: ?>
            <?php foreach ($recentes as $o): ?>
            <div class="occ"><i></i>
                <div class="occ-body">
                    <div class="occ-top">
                        <div>
                            <h3><?= htmlspecialchars($o['titulo']) ?></h3><small>OC<?= str_pad((int) $o['idChamados'], 3, '0', STR_PAD_LEFT) ?> • Registrada em <?= date('d/m/Y', strtotime($o['dataPedida'])) ?></small></div><em class="pill <?= $pill[$o['status']] ?? 'analysis' ?>"><?= $rotulo[$o['status']] ?? $o['status'] ?></em></div>
                    <p><?= htmlspecialchars(mb_strimwidth($o['descricao'], 0, 140, '…')) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <a class="more" href="./ocorrencias.php">Ver todas as ocorrências →</a></div>
    <div class="side">
        <div class="panel">
            <h2 class="panel-title">Meu apartamento</h2>
            <p class="sub">Dados vinculados à sua conta</p>
            <div class="apt">
                <div><small>Apartamento</small><strong><?= htmlspecialchars($moradia['numResid']) ?></strong></div>
                <div><small>Bloco</small><strong><?= htmlspecialchars($moradia['bloco']) ?></strong></div>
            </div><a class="more" href="./apartamentos.php">Ver informações →</a></div>
        <div class="panel quick">
            <h2 class="panel-title" style="margin-bottom:10px">Acesso rápido</h2><a href="./ocorrencias.php">Registrar ocorrência <b>›</b></a><a href="./suporte.php">Preciso de ajuda <b>›</b></a></div>
    </div>
</section>
<?php include __DIR__ . '/_bottom.php'; ?>
