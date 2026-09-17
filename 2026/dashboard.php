<?php
session_start();
include './config/conexao.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ./auth/login.php');
    exit;
}

$idUsuario = $_SESSION['id_usuario'];

// Verificar se é síndico (admin)
$stmt = $conexao->prepare("SELECT COUNT(*) FROM sindico WHERE idUsuario = :id");
$stmt->execute(['id' => $idUsuario]);
$isAdmin = $stmt->fetchColumn() > 0;

// Dados do usuário logado
$stmt = $conexao->prepare("SELECT nome, foto FROM usuario WHERE idUsuario = :id");
$stmt->execute(['id' => $idUsuario]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$user_name = $user ? $user['nome'] : 'Usuário';
$user_type = 'Síndico';
$user_avatar = mb_substr($user_name, 0, 1);
$user_foto = ($user && !empty($user['foto'])) ? $user['foto'] : null;

// Foto do condomínio (vinculado pelo último condomínio criado)
// TODO futuro: substituir por FK sindico -> condominio quando o schema for atualizado
$cond_name = 'Condomínio';
$cond_foto = null;
try {
    $stmt = $conexao->query("SELECT nome, foto FROM condominio ORDER BY idCondominio DESC LIMIT 1");
    $cond = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($cond) {
        $cond_name = $cond['nome'];
        $cond_foto = !empty($cond['foto']) ? $cond['foto'] : null;
    }
} catch (PDOException $e) {
    // sem condomínio cadastrado ainda
}

// Estatísticas do banco
$stats = [];

$stmt = $conexao->query("SELECT COUNT(*) FROM chamados");
$stats['total_occurrences'] = $stmt->fetchColumn();

$stmt = $conexao->query("SELECT COUNT(*) FROM chamados WHERE status = 'resolvida'");
$stats['resolved'] = $stmt->fetchColumn();

$stmt = $conexao->query("SELECT COUNT(*) FROM chamados WHERE status = 'analise'");
$stats['pending'] = $stmt->fetchColumn();

$stmt = $conexao->query("SELECT COUNT(*) FROM chamados WHERE status = 'andamento'");
$stats['analyzing'] = $stmt->fetchColumn();

// Distribuição de ocorrências pendentes por categoria (para o card de breakdown)
$stmt = $conexao->query(
    "SELECT cat.nome, COUNT(*) AS total
     FROM chamados c
     JOIN categoria cat ON c.categoria_idCategoria = cat.idCategoria
     WHERE c.status IN ('analise', 'andamento')
     GROUP BY cat.idCategoria, cat.nome
     ORDER BY total DESC
     LIMIT 4"
);
$categorias_pendentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
$max_categoria = 0;
foreach ($categorias_pendentes as $cat) {
    $max_categoria = max($max_categoria, (int) $cat['total']);
}

// Tempo médio de resolução (horas) e total de resolvidas no período
$stmt = $conexao->query(
    "SELECT AVG(TIMESTAMPDIFF(HOUR, c.dataPedida, c.dataRealizada)) AS media_horas,
            COUNT(*) AS total_resolvidas
     FROM chamados c
     WHERE c.status = 'resolvida' AND c.dataRealizada IS NOT NULL"
);
$tempo_resolucao = $stmt->fetch(PDO::FETCH_ASSOC);
$media_resolucao_horas = $tempo_resolucao && $tempo_resolucao['media_horas'] !== null
    ? round((float) $tempo_resolucao['media_horas'], 1)
    : null;
$total_resolvidas = $tempo_resolucao ? (int) $tempo_resolucao['total_resolvidas'] : 0;

// Ocorrências pendentes com joins
$sql = "SELECT 
            c.idChamados,
            c.titulo,
            c.descricao,
            c.dataPedida,
            c.status,
            p.nome as prioridade_nome,
            p.ordem as prioridade_ordem,
            cat.nome as categoria_nome,
            u.numResid,
            u.bloco,
            u.andar,
            cond.nome as condominio_nome
        FROM chamados c
        JOIN prioridade p ON c.prioridade_idPrioridade = p.idPrioridade
        JOIN categoria cat ON c.categoria_idCategoria = cat.idCategoria
        JOIN morador m ON c.morador_idMorador = m.idMorador
        JOIN usuario us ON m.idUsuario = us.idUsuario
        JOIN moradorunidade mu ON m.idMorador = mu.Morador_idMorador AND mu.dataFim IS NULL
        JOIN unidade u ON mu.Unidade_idUnidade = u.idUnidade
        JOIN condominio cond ON u.Condominio_idCondominio = cond.idCondominio
        WHERE c.status IN ('analise', 'andamento')
        ORDER BY c.dataPedida DESC
        LIMIT 10";

$pending_issues = [];
try {
    $stmt = $conexao->query($sql);
    $pending_issues = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Tabelas relacionadas podem não ter dados ainda
    $pending_issues = [];
}

// Bloco com mais ocorrências pendentes
$top_bloco = null;
try {
    $stmt = $conexao->query(
        "SELECT u.bloco, COUNT(*) AS total
         FROM chamados c
         JOIN morador m ON c.morador_idMorador = m.idMorador
         JOIN moradorunidade mu ON m.idMorador = mu.Morador_idMorador AND mu.dataFim IS NULL
         JOIN unidade u ON mu.Unidade_idUnidade = u.idUnidade
         WHERE c.status IN ('analise', 'andamento') AND u.bloco IS NOT NULL AND u.bloco <> ''
         GROUP BY u.bloco ORDER BY total DESC LIMIT 1"
    );
    $top_bloco = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $top_bloco = null;
}

// Tempo médio de resolução: últimos 30 dias vs 30 dias anteriores
$media_30 = $media_ant = null;
try {
    $stmt = $conexao->query(
        "SELECT AVG(TIMESTAMPDIFF(HOUR, dataPedida, dataRealizada)) FROM chamados
         WHERE status = 'resolvida' AND dataRealizada IS NOT NULL
         AND dataRealizada >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
    );
    $media_30 = $stmt->fetchColumn();
    $stmt = $conexao->query(
        "SELECT AVG(TIMESTAMPDIFF(HOUR, dataPedida, dataRealizada)) FROM chamados
         WHERE status = 'resolvida' AND dataRealizada IS NOT NULL
         AND dataRealizada < DATE_SUB(NOW(), INTERVAL 30 DAY)
         AND dataRealizada >= DATE_SUB(NOW(), INTERVAL 60 DAY)"
    );
    $media_ant = $stmt->fetchColumn();
} catch (PDOException $e) {
    // sem dados suficientes
}
$pageTitle = 'Painel de Controle';
$menuAtivo = 'dashboard';

function classe_prioridade($nome) {
    $low = ['Muito Baixa', 'Baixa'];
    $med = ['Média', 'Normal', 'Considerável', 'Moderada'];
    $high = ['Alta', 'Muito Alta'];
    if (in_array($nome, $low, true)) return 'low';
    if (in_array($nome, $med, true)) return 'medium';
    if (in_array($nome, $high, true)) return 'high';
    return 'urgent';
}

function classe_status($status) {
    return [
        'analise' => 'analysis',
        'andamento' => 'progress',
        'resolvida' => 'finished',
        'cancelada' => 'cancelled',
    ][$status] ?? 'analysis';
}

function rotulo_status($status) {
    return [
        'analise' => 'Em análise',
        'andamento' => 'Em andamento',
        'resolvida' => 'Resolvida',
        'cancelada' => 'Cancelada',
    ][$status] ?? $status;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Eden Systems</title>
    <link rel="stylesheet" href="./CSS/dashboard.css">
    <link rel="stylesheet" href="./CSS/reset.css">
    <link rel="stylesheet" href="./CSS/FrontDev.css">
    <script src="https://unpkg.com/lucide@latest"></script>
<?php include './Elements/favicon.php'; ?>
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <?php include './Elements/sidebar.php'; ?>

        <div class="main-content">
            <!-- Header -->
            <?php include './Elements/header.php'; ?>

            <!-- Conteúdo Principal -->
            <div class="dashboard-content">
                <div class="fd-dashboard">
                    <section class="summary-grid">
                        <article class="summary-card highlighted">
                            <div class="summary-icon red"><i data-lucide="triangle-alert"></i></div>
                            <strong><?= number_format((int) $stats['total_occurrences']) ?></strong>
                            <h3>Total de Ocorrências</h3>
                            <p>Total registrado nesse período</p>
                            <a href="./ocorrencias.php">Veja mais →</a>
                        </article>
                        <article class="summary-card">
                            <div class="summary-icon green"><i data-lucide="circle-check"></i></div>
                            <strong><?= number_format((int) $stats['resolved']) ?></strong>
                            <h3>Resolvidas</h3>
                            <p>Ocorrências concluídas com sucesso</p>
                            <a href="./ocorrencias.php">Veja mais →</a>
                        </article>
                        <article class="summary-card">
                            <div class="summary-icon yellow"><i data-lucide="clock-3"></i></div>
                            <strong><?= number_format((int) $stats['pending']) ?></strong>
                            <h3>Pendentes</h3>
                            <p>Ocorrências esperando análise</p>
                            <a href="./ocorrencias.php">Veja mais →</a>
                        </article>
                        <article class="summary-card">
                            <div class="summary-icon blue"><i data-lucide="eye"></i></div>
                            <strong><?= number_format((int) $stats['analyzing']) ?></strong>
                            <h3>Em análise</h3>
                            <p>Aguardando triagem e análise</p>
                            <a href="./ocorrencias.php">Veja mais →</a>
                        </article>
                    </section>

                    <section class="middle-grid">
                        <article class="panel pending-panel">
                            <div class="panel-title">
                                <h2>Ocorrências pendentes</h2>
                                <p><?= count($pending_issues) ?> ocorrências encontradas</p>
                            </div>
                            <?php if (empty($categorias_pendentes)): ?>
                                <p style="color:#819087;font-size:14px">Nenhuma ocorrência pendente.</p>
                            <?php else: ?>
                                <?php foreach ($categorias_pendentes as $cat): ?>
                                <?php $largura = $max_categoria > 0 ? round(((int) $cat['total'] / $max_categoria) * 100) : 0; ?>
                                <div class="progress-item">
                                    <div class="progress-info">
                                        <strong><?= htmlspecialchars($cat['nome']) ?></strong>
                                        <span><?= (int) $cat['total'] ?></span>
                                    </div>
                                    <div class="progress-track">
                                        <div class="progress-bar" style="width: <?= $largura ?>%;"></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </article>

                        <article class="resolution-panel">
                            <h2>Tempo médio de resolução</h2>
                            <p class="period">Últimos 30 dias</p>
                            <div class="resolution-value">
                                <strong><?= $media_30 !== null ? number_format(((float) $media_30) / 24, 1, ',', '.') : '—' ?></strong>
                                <span>dias</span>
                            </div>
                            <?php if ($media_30 !== null && $media_ant !== null): ?>
                            <?php
                                $diff_dias = (((float) $media_ant) - ((float) $media_30)) / 24;
                                $icone = $diff_dias >= 0 ? 'arrow-down' : 'arrow-up';
                                $texto = number_format(abs($diff_dias), 1, ',', '.') . ' dias ' . ($diff_dias >= 0 ? 'mais rápido' : 'mais lento');
                            ?>
                            <div class="improvement">
                                <i data-lucide="<?= $icone ?>"></i>
                                <span><?= $texto ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if ($top_bloco): ?>
                            <div class="block-info">
                                <p>Bloco com mais ocorrências</p>
                                <div class="block-row">
                                    <span class="block-circle"><?= htmlspecialchars(mb_substr((string) $top_bloco['bloco'], 0, 1)) ?></span>
                                    <strong>Bloco <?= htmlspecialchars((string) $top_bloco['bloco']) ?> — <?= (int) $top_bloco['total'] ?> ocorrências</strong>
                                </div>
                            </div>
                            <?php endif; ?>
                        </article>
                    </section>

                    <section class="table-card">
                        <div class="table-top">
                            <div>
                                <h2>Ocorrências pendentes</h2>
                                <p><?= count($pending_issues) ?> ocorrências encontradas</p>
                            </div>
                            <div class="table-actions">
                                <div class="table-search">
                                    <i data-lucide="search"></i>
                                    <input id="searchOccurrence" type="text" placeholder="Pesquisar...">
                                </div>
                                <button class="filter-button" id="filterButton" type="button">Filtro</button>
                            </div>
                        </div>
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Bloco</th>
                                        <th>Unidade</th>
                                        <th>Tipo</th>
                                        <th>Data</th>
                                        <th>Prioridade</th>
                                        <th>Status</th>
                                        <th class="actions-column">Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="occurrencesBody">
                                    <?php if (empty($pending_issues)): ?>
                                    <tr><td colspan="7" style="text-align:center;padding:24px;color:#819087">Nenhuma ocorrência pendente.</td></tr>
                                    <?php else: ?>
                                    <?php foreach ($pending_issues as $issue): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($issue['bloco'] ?? '—') ?></td>
                                        <td><?= htmlspecialchars($issue['numResid'] ?? '—') ?></td>
                                        <td><?= htmlspecialchars($issue['categoria_nome']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($issue['dataPedida'])) ?></td>
                                        <td><span class="priority <?= classe_prioridade($issue['prioridade_nome']) ?>"><?= htmlspecialchars($issue['prioridade_nome']) ?></span></td>
                                        <td><span class="status <?= classe_status($issue['status']) ?>"><?= rotulo_status($issue['status']) ?></span></td>
                                        <td class="actions-column"><a class="action-link" href="./ocorrencias.php">Ações</a></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <script src="./js/FrontDev.js"></script>

</body>
</html>
