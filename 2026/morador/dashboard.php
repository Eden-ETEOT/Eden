<?php
require_once __DIR__ . '/_guard.php';

$pageTitle = 'Painel de Controle';
$menuAtivo = 'dashboard';

$stmt = $conexao->prepare("SELECT COUNT(*) FROM chamados WHERE morador_idMorador = :m");
$stmt->execute(['m' => $idMorador]);
$total = (int) $stmt->fetchColumn();

$stmt = $conexao->prepare("SELECT COUNT(*) FROM chamados WHERE morador_idMorador = :m AND status = 'andamento'");
$stmt->execute(['m' => $idMorador]);
$emAndamento = (int) $stmt->fetchColumn();

$stmt = $conexao->prepare("SELECT COUNT(*) FROM chamados WHERE morador_idMorador = :m AND status IN ('resolvida', 'cancelada')");
$stmt->execute(['m' => $idMorador]);
$finalizadas = (int) $stmt->fetchColumn();

$stmt = $conexao->prepare(
    "SELECT c.idChamados, c.titulo, c.descricao, c.status, c.dataPedida,
            DATE_FORMAT(c.dataPedida, '%d/%m/%Y') AS dataFmt,
            cat.nome AS categoria
     FROM chamados c
     JOIN categoria cat ON cat.idCategoria = c.categoria_idCategoria
     WHERE c.morador_idMorador = :m
     ORDER BY c.dataPedida DESC LIMIT 5"
);
$stmt->execute(['m' => $idMorador]);
$recentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
<?php pageHead('Painel de Controle - Eden Systems', ['../CSS/tabelas.css', '../CSS/morador.css'], ['https://unpkg.com/lucide@latest'], '..'); ?>
<body>
<div class="dashboard-wrapper">
<?php include __DIR__ . '/sidebar.php'; ?>
<div class="main-content">
<?php include __DIR__ . '/header.php'; ?>
<div class="dashboard-content">
                <h1 class="page-title">Visão geral</h1>

                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-icon yellow"><img src="../assets/icones/alerta.svg" alt="Total"></div>
                        <div class="stat-value"><?= number_format($total) ?></div>
                        <div class="stat-label">Total de Ocorrências</div>
                        <div class="stat-description">Registradas por você</div>
                        <a href="./ocorrencias.php" class="stat-link">Ver mais</a>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon orange"><img src="../assets/icones/relogio.svg" alt="Em andamento"></div>
                        <div class="stat-value"><?= number_format($emAndamento) ?></div>
                        <div class="stat-label">Em andamento</div>
                        <div class="stat-description">Aguardando ou em análise</div>
                        <a href="./ocorrencias.php" class="stat-link">Ver mais</a>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon green"><img src="../assets/icones/sucesso.svg" alt="Finalizadas"></div>
                        <div class="stat-value"><?= number_format($finalizadas) ?></div>
                        <div class="stat-label">Finalizadas</div>
                        <div class="stat-description">Ocorrências concluídas</div>
                        <a href="./ocorrencias.php" class="stat-link">Ver mais</a>
                    </div>
                </div>

                <div class="mor-grid">
                    <div class="mor-panel">
                        <div class="mor-panel-head">
                            <div>
                                <h2 class="mor-panel-title">Ocorrências recentes</h2>
                                <p class="mor-panel-sub">Acompanhe suas solicitações mais recentes</p>
                            </div>
                            <a class="btn btn-orange btn-sm" href="./ocorrencias.php"><i data-lucide="plus"></i>Ocorrência</a>
                        </div>
                        <?php if (empty($recentes)): ?>
                        <div class="mor-empty">Nenhuma ocorrência registrada ainda.</div>
                        <?php else: ?>
                        <?php foreach ($recentes as $o): ?>
                        <div class="mor-occ<?= in_array($o['status'], ['resolvida', 'cancelada'], true) ? ' done' : '' ?>">
                            <div class="mor-occ-bar"></div>
                            <div class="mor-occ-body">
                                <div class="mor-occ-top">
                                    <div>
                                        <h3><?= htmlspecialchars($o['titulo']) ?></h3>
                                        <small>#<?= str_pad((int) $o['idChamados'], 3, '0', STR_PAD_LEFT) ?> · Registrada em <?= htmlspecialchars($o['dataFmt']) ?></small>
                                    </div>
                                    <span class="badge <?= morStatusBadge($o['status']) ?>"><?= morStatusRotulo($o['status']) ?></span>
                                </div>
                                <p><?= htmlspecialchars(mb_strimwidth($o['descricao'], 0, 140, '...')) ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <a class="mor-more" href="./ocorrencias.php">Ver todas as ocorrências →</a>
                        <?php endif; ?>
                    </div>
                    <div class="mor-side">
                        <div class="mor-panel">
                            <h2 class="mor-panel-title">Meu apartamento</h2>
                            <p class="mor-panel-sub">Dados vinculados à sua conta</p>
                            <div class="mor-info-grid" style="margin-top:14px">
                                <div class="mor-info-box"><span>Apartamento</span><strong><?= htmlspecialchars($moradorCtx['numResid']) ?></strong></div>
                                <div class="mor-info-box"><span>Bloco</span><strong><?= htmlspecialchars($moradorCtx['bloco']) ?></strong></div>
                            </div>
                            <a class="mor-more" href="./apartamentos.php">Ver informações →</a>
                        </div>
                        <div class="mor-panel">
                            <h2 class="mor-panel-title" style="margin-bottom:10px">Acesso rápido</h2>
                            <p style="margin-bottom:6px"><a class="mor-more" style="margin-top:0" href="./ocorrencias.php">Registrar ocorrência →</a></p>
                            <p><a class="mor-more" style="margin-top:0" href="./suporte.php">Preciso de ajuda →</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="<?= assetUrl('../js/app.js') ?>"></script>
    <script>if (window.lucide) lucide.createIcons();</script>
</body>
</html>
