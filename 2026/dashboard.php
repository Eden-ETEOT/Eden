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
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Eden Systems</title>
    <link rel="stylesheet" href="./CSS/dashboard.css">
    <link rel="stylesheet" href="./CSS/reset.css">
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
                <h1 class="page-title">Visão geral</h1>

                <!-- Cards de Estatísticas -->
                <div class="stats-container">
                    <!-- Total de Ocorrências -->
                    <div class="stat-card">
                        <div class="stat-icon yellow">⚠️</div>
                        <div class="stat-value"><?php echo number_format($stats['total_occurrences']); ?></div>
                        <div class="stat-label">Total de Ocorrências</div>
                        <div class="stat-description">Total registrado nesse período</div>
                        <a href="#" class="stat-link">Ver mais</a>
                    </div>

                    <!-- Resolvidas -->
                    <div class="stat-card">
                        <div class="stat-icon green">✓</div>
                        <div class="stat-value"><?php echo number_format($stats['resolved']); ?></div>
                        <div class="stat-label">Resolvidas</div>
                        <div class="stat-description">Ocorrências concluídas com sucesso</div>
                        <a href="#" class="stat-link">Ver mais</a>
                    </div>

                    <!-- Pendentes -->
                    <div class="stat-card">
                        <div class="stat-icon orange">⏱️</div>
                        <div class="stat-value"><?php echo number_format($stats['pending']); ?></div>
                        <div class="stat-label">Pendentes</div>
                        <div class="stat-description">Ocorrências esperando análise</div>
                        <a href="#" class="stat-link">Ver mais</a>
                    </div>

                    <!-- Em Análise -->
                    <div class="stat-card">
                        <div class="stat-icon blue">👁️</div>
                        <div class="stat-value"><?php echo number_format($stats['analyzing']); ?></div>
                        <div class="stat-label">Em análise</div>
                        <div class="stat-description">Aguardando triagem e análise</div>
                        <a href="#" class="stat-link">Ver mais</a>
                    </div>
                </div>

                <!-- Visão geral (breakdown + tempo médio de resolução) -->
                <div class="overview-container">
                    <div class="breakdown-card">
                        <h3 class="pending-title">Ocorrências Pendentes</h3>
                        <p class="breakdown-subtitle">Distribuição por categoria</p>
                        <div class="breakdown-list">
                            <?php if (empty($categorias_pendentes)): ?>
                                <p style="font-size: 13px; color: var(--green1-300);">Nenhuma ocorrência pendente.</p>
                            <?php else: ?>
                                <?php foreach ($categorias_pendentes as $cat): ?>
                                <?php
                                    $largura = $max_categoria > 0
                                        ? round(((int) $cat['total'] / $max_categoria) * 100)
                                        : 0;
                                ?>
                                <div class="breakdown-item">
                                    <div class="breakdown-label-row">
                                        <span class="breakdown-label"><?= htmlspecialchars($cat['nome']) ?></span>
                                        <span class="breakdown-value"><?= (int) $cat['total'] ?></span>
                                    </div>
                                    <div class="breakdown-track">
                                        <div class="breakdown-fill" style="width: <?= $largura ?>%;"></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="resolution-card">
                        <h3 class="resolution-title">Tempo Médio de Resolução</h3>
                        <p class="resolution-period">Ocorrências resolvidas no período</p>
                        <?php if ($media_resolucao_horas !== null): ?>
                            <div class="resolution-value">
                                <?= $media_resolucao_horas ?><span class="resolution-unit">horas</span>
                            </div>
                            <span class="resolution-trend up">▲ tempo apurado no período</span>
                        <?php else: ?>
                            <div class="resolution-value">—<span class="resolution-unit">horas</span></div>
                            <span class="resolution-trend">Sem ocorrências resolvidas ainda</span>
                        <?php endif; ?>
                        <div class="resolution-divider"></div>
                        <p class="resolution-subheading">Detalhes do período</p>
                        <div class="resolution-block-info">
                            <span class="resolution-block-badge"><?= $total_resolvidas ?></span>
                            ocorrências resolvidas
                        </div>
                    </div>
                </div>

                <!-- Seção de Ocorrências Pendentes -->
                <div class="pending-issues">
                    <div class="pending-header">
                        <div>
                            <h2 class="pending-title">Ocorrências pendentes</h2>
                            <p style="font-size: 12px; color: #999; margin-top: 4px;"><?php echo count($pending_issues); ?> ocorrências encontradas</p>
                        </div>
                    </div>

                    <div style="overflow-x: auto;">
                        <table class="issues-table">
                            <thead>
                                <tr>
                                    <th>Bloco</th>
                                    <th>Unidade</th>
                                    <th>Tipo</th>
                                    <th>Data</th>
                                    <th>Prioridade</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pending_issues)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 24px; color: #999;">
                                        Nenhuma ocorrência registrada ainda.
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($pending_issues as $issue): ?>
                                <?php
                                $mapa_prioridade = [1 => 'low', 2 => 'medium', 3 => 'high'];
                                $classe_prio = $mapa_prioridade[$issue['prioridade_ordem']] ?? 'low';
                                $classe_status = $issue['status'];
                                $rotulo_status = [
                                    'analise' => 'Em análise',
                                    'andamento' => 'Em andamento',
                                    'cancelada' => 'Cancelada',
                                    'resolvida' => 'Resolvida'
                                ][$issue['status']] ?? $issue['status'];
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($issue['bloco']) ?></td>
                                    <td><?= htmlspecialchars($issue['numResid']) ?></td>
                                    <td><?= htmlspecialchars($issue['categoria_nome']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($issue['dataPedida'])) ?></td>
                                    <td>
                                        <span class="badge <?= $classe_prio ?>">
                                            <?= htmlspecialchars($issue['prioridade_nome']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $classe_status ?>">
                                            <?= $rotulo_status ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="actions-dropdown">Ações</span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="./js/dashboard.js"></script>
</body>
</html>
