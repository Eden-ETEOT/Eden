<?php
include './Elements/auth.php';
include './Elements/ui.php';

$pageTitle = 'Relatórios';
$menuAtivo = 'relatorios';

$meses_pt = [1 => 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
$meses_short = [1 => 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];

// Últimos 6 meses (chave Y-m)
$meses = [];
for ($i = 5; $i >= 0; $i--) {
    $ts = strtotime("first day of -$i month");
    $ym = date('Y-m', $ts);
    $meses[$ym] = [
        'ym' => $ym,
        'rotulo' => $meses_pt[(int) date('n', $ts)] . ' ' . date('Y', $ts),
        'short' => $meses_short[(int) date('n', $ts)],
        'total' => 0,
        'resolvidas' => 0,
        'tempo_medio' => null,
    ];
}

try {
    $stmt = $conexao->prepare(
        "SELECT DATE_FORMAT(c.dataPedida, '%Y-%m') AS ym, COUNT(*) AS total,
            SUM(c.status = 'resolvida') AS resolvidas,
            AVG(IF(c.status = 'resolvida' AND c.dataRealizada IS NOT NULL,
                   TIMESTAMPDIFF(DAY, c.dataPedida, c.dataRealizada), NULL)) AS tempo_medio
         FROM chamados c
         WHERE c.dataPedida >= DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-01'), INTERVAL 5 MONTH)
           AND c.Condominio_idCondominio = :cond
         GROUP BY ym"
    );
    $stmt->execute(['cond' => $filtroCondominio]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if (isset($meses[$row['ym']])) {
            $meses[$row['ym']]['total'] = (int) $row['total'];
            $meses[$row['ym']]['resolvidas'] = (int) $row['resolvidas'];
            $meses[$row['ym']]['tempo_medio'] = $row['tempo_medio'] !== null
                ? round((float) $row['tempo_medio'], 1)
                : null;
        }
    }
} catch (PDOException $e) {
    // sem dados
}

$ym_atual = array_key_last($meses);
$m = $meses[$ym_atual];
$taxa = $m['total'] > 0 ? round(($m['resolvidas'] / $m['total']) * 100) : 0;
$tempo_medio = $m['tempo_medio'];

// Moradores ativos
$moradores_ativos = 0;
try {
    $stmt = $conexao->prepare(
        "SELECT COUNT(*) FROM morador m JOIN usuario u ON u.idUsuario = m.idUsuario
         JOIN moradorunidade mu ON mu.Morador_idMorador = m.idMorador AND mu.dataFim IS NULL
         JOIN unidade un ON un.idUnidade = mu.Unidade_idUnidade
         WHERE u.ativo = 1 AND un.Condominio_idCondominio = :cond"
    );
    $stmt->execute(['cond' => $filtroCondominio]);
    $moradores_ativos = (int) $stmt->fetchColumn();
} catch (PDOException $e) {
    $moradores_ativos = 0;
}

// Datasets para exportação CSV
$csv_ocorrencias = [];
$csv_moradores = [];
$csv_infra = [];
try {
    $stmt = $conexao->prepare(
        "SELECT c.idChamados, c.titulo, cat.nome AS categoria, p.nome AS prioridade,
                c.status, DATE_FORMAT(c.dataPedida, '%d/%m/%Y') AS data
         FROM chamados c
         JOIN categoria cat ON cat.idCategoria = c.categoria_idCategoria
         JOIN prioridade p ON p.idPrioridade = c.prioridade_idPrioridade
         WHERE c.Condominio_idCondominio = :cond
         ORDER BY c.idChamados DESC"
    );
    $stmt->execute(['cond' => $filtroCondominio]);
    $csv_ocorrencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $conexao->prepare(
        "SELECT u.nome, u.email, u.telefone, un.bloco, un.numResid,
                IF(u.ativo = 1, 'Ativo', 'Inativo') AS status
         FROM morador m
         JOIN usuario u ON u.idUsuario = m.idUsuario
         JOIN moradorunidade mu ON mu.Morador_idMorador = m.idMorador AND mu.dataFim IS NULL
         JOIN unidade un ON un.idUnidade = mu.Unidade_idUnidade
         WHERE un.Condominio_idCondominio = :cond
         ORDER BY u.nome"
    );
    $stmt->execute(['cond' => $filtroCondominio]);
    $csv_moradores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $conexao->prepare(
        "SELECT numResid AS numero, bloco, andar, metragem,
                IF(ativo = 1, 'Ativo', 'Inativo') AS status
         FROM unidade WHERE Condominio_idCondominio = :cond ORDER BY bloco, numResid"
    );
    $stmt->execute(['cond' => $filtroCondominio]);
    $csv_infra = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // exportação parcial
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<?php pageHead('Relatórios - Eden Systems', ['./CSS/FrontDev.css', './CSS/tabelas.css'], ['./js/lucide.min.js', './js/chart.umd.min.js']); ?>
<body>
    <?php layoutOpen(); ?>
                <div class="fd-relatorios">
                    <section class="page-title">
                        <h1>Relatórios</h1>
                        <p>Análise e exportação de dados condominiais</p>
                    </section>
                    <?php if (!$idCondominio): ?>
                    <p style="width:100%;padding:12px;border:1px solid #e7c77b;background:#fff8e6;color:#684d12;margin:0 0 16px">
                        Sua conta não está associada a um condomínio. Os gráficos e indicadores aparecerão após <a href="./configurar-condominio.php">configurar o condomínio</a>.
                    </p>
                    <?php endif; ?>

                    <section class="card chart-card">
                        <div class="chart-header">
                            <div>
                                <h2>Ocorrências por Mês</h2>
                                <p id="chartPeriod"><?= htmlspecialchars(reset($meses)['rotulo'] . ' — ' . end($meses)['rotulo']) ?></p>
                            </div>
                            <div class="legend">
                                <span><i class="legend-box total"></i>Total</span>
                                <span><i class="legend-box solved"></i>Resolvidas</span>
                            </div>
                        </div>
                        <div class="chart-container">
                            <canvas id="occurrencesChart"></canvas>
                        </div>
                    </section>

                    <section class="stats-grid">
                        <article class="stat-card">
                            <span>Total de Ocorrências</span>
                            <strong id="totalOccurrences"><?= (int) $m['total'] ?></strong>
                            <small><?= htmlspecialchars($m['rotulo']) ?></small>
                        </article>
                        <article class="stat-card">
                            <span>Taxa de Resolução</span>
                            <strong id="resolutionRate"><?= $taxa ?>%</strong>
                            <small><?= htmlspecialchars($m['rotulo']) ?></small>
                        </article>
                        <article class="stat-card">
                            <span>Tempo Médio de Resposta</span>
                            <strong id="responseTime"><?= $tempo_medio !== null ? number_format((float) $tempo_medio, 1, ',', '.') . ' dias' : '—' ?></strong>
                            <small><?= htmlspecialchars($m['rotulo']) ?></small>
                        </article>
                        <article class="stat-card">
                            <span>Moradores Ativos</span>
                            <strong id="activeResidents"><?= (int) $moradores_ativos ?></strong>
                            <small>Cadastros ativos</small>
                        </article>
                    </section>

                    <section class="card available-card">
                        <h2>Relatórios Disponíveis</h2>
                        <div class="reports-grid">
                            <button class="report-item" type="button" onclick="fdBaixarRelatorio('ocorrencias')">
                                <span class="report-icon"><i data-lucide="triangle-alert"></i></span>
                                <span class="report-info"><strong>Relatório de Ocorrências</strong><small>Listagem completa com status e prioridade</small></span>
                                <i data-lucide="download" class="download-icon"></i>
                            </button>
                            <button class="report-item" type="button" onclick="fdBaixarRelatorio('financeiro')">
                                <span class="report-icon"><i data-lucide="chart-no-axes-column"></i></span>
                                <span class="report-info"><strong>Relatório Financeiro</strong><small>Receitas, despesas e balancete mensal</small></span>
                                <i data-lucide="download" class="download-icon"></i>
                            </button>
                            <button class="report-item" type="button" onclick="fdBaixarRelatorio('moradores')">
                                <span class="report-icon"><i data-lucide="users"></i></span>
                                <span class="report-info"><strong>Relatório de Moradores</strong><small>Cadastro completo e histórico de ocorrências</small></span>
                                <i data-lucide="download" class="download-icon"></i>
                            </button>
                            <button class="report-item" type="button" onclick="fdBaixarRelatorio('infraestrutura')">
                                <span class="report-icon"><i data-lucide="building-2"></i></span>
                                <span class="report-info"><strong>Relatório de Infraestrutura</strong><small>Status dos apartamentos e áreas comuns</small></span>
                                <i data-lucide="download" class="download-icon"></i>
                            </button>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>


    <script>
        const FD_MESES = <?= json_encode(array_values($meses), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
        const FD_CSV = {
            ocorrencias: <?= json_encode($csv_ocorrencias, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>,
            moradores: <?= json_encode($csv_moradores, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>,
            infraestrutura: <?= json_encode($csv_infra, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>
        };
    </script>
    <script src="<?= assetUrl('./js/app.js') ?>"></script>
</body>
</html>
