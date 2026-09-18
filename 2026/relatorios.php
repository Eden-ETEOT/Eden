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
    ];
}

try {
    $stmt = $conexao->query(
        "SELECT DATE_FORMAT(dataPedida, '%Y-%m') AS ym, COUNT(*) AS total,
                SUM(status = 'resolvida') AS resolvidas
         FROM chamados
         WHERE dataPedida >= DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-01'), INTERVAL 5 MONTH)
         GROUP BY ym"
    );
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if (isset($meses[$row['ym']])) {
            $meses[$row['ym']]['total'] = (int) $row['total'];
            $meses[$row['ym']]['resolvidas'] = (int) $row['resolvidas'];
        }
    }
} catch (PDOException $e) {
    // sem dados
}

$ym_atual = array_key_last($meses);
$m = $meses[$ym_atual];
$taxa = $m['total'] > 0 ? round(($m['resolvidas'] / $m['total']) * 100) : 0;

// Tempo médio de resposta (dias) no mês atual
$tempo_medio = null;
try {
    $stmt = $conexao->prepare(
        "SELECT AVG(TIMESTAMPDIFF(DAY, dataPedida, dataRealizada))
         FROM chamados
         WHERE status = 'resolvida' AND dataRealizada IS NOT NULL
           AND DATE_FORMAT(dataPedida, '%Y-%m') = :ym"
    );
    $stmt->execute(['ym' => $ym_atual]);
    $tempo_medio = $stmt->fetchColumn();
} catch (PDOException $e) {
    $tempo_medio = null;
}

// Moradores ativos
$moradores_ativos = 0;
try {
    $moradores_ativos = (int) $conexao->query(
        "SELECT COUNT(*) FROM morador m JOIN usuario u ON u.idUsuario = m.idUsuario WHERE u.ativo = 1"
    )->fetchColumn();
} catch (PDOException $e) {
    $moradores_ativos = 0;
}

// Datasets para exportação CSV
$csv_ocorrencias = [];
$csv_moradores = [];
$csv_infra = [];
try {
    $csv_ocorrencias = $conexao->query(
        "SELECT c.idChamados, c.titulo, cat.nome AS categoria, p.nome AS prioridade,
                c.status, DATE_FORMAT(c.dataPedida, '%d/%m/%Y') AS data
         FROM chamados c
         JOIN categoria cat ON cat.idCategoria = c.categoria_idCategoria
         JOIN prioridade p ON p.idPrioridade = c.prioridade_idPrioridade
         ORDER BY c.idChamados DESC"
    )->fetchAll(PDO::FETCH_ASSOC);
    $csv_moradores = $conexao->query(
        "SELECT u.nome, u.email, u.telefone, un.bloco, un.numResid,
                IF(u.ativo = 1, 'Ativo', 'Inativo') AS status
         FROM morador m
         JOIN usuario u ON u.idUsuario = m.idUsuario
         LEFT JOIN moradorunidade mu ON mu.Morador_idMorador = m.idMorador AND mu.dataFim IS NULL
         LEFT JOIN unidade un ON un.idUnidade = mu.Unidade_idUnidade
         ORDER BY u.nome"
    )->fetchAll(PDO::FETCH_ASSOC);
    $csv_infra = $conexao->query(
        "SELECT numResid AS numero, bloco, andar, metragem,
                IF(ativo = 1, 'Ativo', 'Inativo') AS status
         FROM unidade ORDER BY bloco, numResid"
    )->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // exportação parcial
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<?php pageHead('Relatórios - Eden Systems', ['./CSS/FrontDev.css', './CSS/tabelas.css'], ['https://unpkg.com/lucide@latest', 'https://cdn.jsdelivr.net/npm/chart.js']); ?>
<body>
    <?php layoutOpen(); ?>
                <div class="fd-relatorios">
                    <section class="page-title">
                        <h1>Relatórios</h1>
                        <p>Análise e exportação de dados condominiais</p>
                    </section>

                    <section class="card generator-card">
                        <h2>Gerar Relatório</h2>
                        <div class="generator-fields">
                            <div class="field">
                                <label>Tipo</label>
                                <select id="reportType">
                                    <option value="ocorrencias">Ocorrências por Período</option>
                                    <option value="moradores">Relatório de Moradores</option>
                                    <option value="infraestrutura">Relatório de Infraestrutura</option>
                                    <option value="financeiro">Relatório Financeiro</option>
                                </select>
                            </div>
                            <div class="field">
                                <label>Período</label>
                                <select id="reportPeriod">
                                    <?php foreach (array_reverse($meses, true) as $ym => $info): ?>
                                    <option value="<?= $ym ?>"><?= htmlspecialchars($info['rotulo']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button class="generate-btn" type="button" onclick="fdGerarRelatorio()">
                                <i data-lucide="chart-no-axes-column"></i>
                                Gerar
                            </button>
                        </div>
                    </section>

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
