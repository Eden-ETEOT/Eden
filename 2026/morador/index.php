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

$scope = ['m' => $idMorador, 'c' => $filtroCondominio];
$stmt = $conexao->prepare(
    "SELECT COUNT(*) FROM chamados WHERE morador_idMorador = :m AND Condominio_idCondominio = :c AND status <> 'cancelada'"
);
$stmt->execute($scope);
$total = (int) $stmt->fetchColumn();
$stmt = $conexao->prepare(
    "SELECT COUNT(*) FROM chamados WHERE morador_idMorador = :m AND Condominio_idCondominio = :c AND status IN ('analise','andamento')"
);
$stmt->execute($scope);
$abertas = (int) $stmt->fetchColumn();
$stmt = $conexao->prepare(
    "SELECT COUNT(*) FROM chamados WHERE morador_idMorador = :m AND Condominio_idCondominio = :c AND status = 'resolvida'"
);
$stmt->execute($scope);
$finalizadas = (int) $stmt->fetchColumn();

$stmt = $conexao->prepare(
    "SELECT c.idChamados, c.titulo, c.descricao, c.status, DATE_FORMAT(c.dataPedida, '%d/%m/%Y') AS dataFmt,
            cat.nome AS categoria, p.nome AS prioridade
     FROM chamados c
     JOIN categoria cat ON cat.idCategoria = c.categoria_idCategoria
     JOIN prioridade p ON p.idPrioridade = c.prioridade_idPrioridade
     WHERE c.morador_idMorador = :m AND c.Condominio_idCondominio = :c
     ORDER BY c.dataPedida DESC, c.idChamados DESC LIMIT 3"
);
$stmt->execute($scope);
$recentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$rotuloTipo = ['proprietario' => 'Proprietário', 'inquilino' => 'Inquilino', 'dependente' => 'Dependente'];
$sub = ($rotuloTipo[$moradia['tipoMorador']] ?? 'Morador') . ' • Apto ' . $moradia['numResid'];

moradorHead('Painel de Controle');
moradorSidebar('index');
moradorHeader($user['nome'] ?? 'Morador', $sub, 'Painel de Controle');
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
                    <p class="sub">Você ainda não registrou ocorrências.</p>
                    <?php else: ?>
                    <?php foreach ($recentes as $o): [$pc, $pl] = pillStatus($o['status']); ?>
                    <div class="occ <?= $pc === 'done' ? 'done' : ($pc === 'progress' ? 'progress' : '') ?>"><i></i>
                        <div class="occ-body">
                            <div class="occ-top">
                                <div>
                                    <h3><?= htmlspecialchars($o['titulo']) ?></h3><small>#<?= (int) $o['idChamados'] ?> • Registrada em <?= htmlspecialchars($o['dataFmt']) ?></small></div><em class="pill <?= $pc ?>"><?= $pl ?></em></div>
                            <p><?= htmlspecialchars(mb_strimwidth($o['descricao'], 0, 120, '…')) ?></p>
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
                        </div><a class="more" href="./apartamento.php">Ver informações →</a></div>
                    <div class="panel quick">
                        <h2 class="panel-title" style="margin-bottom:10px">Acesso rápido</h2><a href="./ocorrencias.php">Registrar ocorrência <b>›</b></a><a href="./suporte.php">Preciso de ajuda <b>›</b></a></div>
                </div>
            </section>
<?php moradorFoot(); ?>
