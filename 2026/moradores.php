<?php
session_start();
include './config/conexao.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ./auth/login.php');
    exit;
}

$idUsuario = $_SESSION['id_usuario'];

// Desativar morador (soft delete via usuario.ativo)
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'desativar') {
    $idMorador = (int) ($_POST['id'] ?? 0);
    if ($idMorador > 0) {
        $stmt = $conexao->prepare(
            "UPDATE usuario u JOIN morador m ON m.idUsuario = u.idUsuario
             SET u.ativo = 0 WHERE m.idMorador = :id"
        );
        $stmt->execute(['id' => $idMorador]);
        $msg = 'Morador desativado com sucesso.';
    }
}

// Dados do usuário logado (header)
$stmt = $conexao->prepare("SELECT nome, foto FROM usuario WHERE idUsuario = :id");
$stmt->execute(['id' => $idUsuario]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$user_name = $user ? $user['nome'] : 'Usuário';
$user_type = 'Síndico';
$user_avatar = mb_substr($user_name, 0, 1);
$user_foto = ($user && !empty($user['foto'])) ? $user['foto'] : null;
$pageTitle = 'Moradores';
$menuAtivo = 'moradores';

// Lista de moradores com unidade atual
$moradores = [];
try {
    $sql = "SELECT m.idMorador, u.nome, u.email, u.telefone, u.ativo,
                   DATE_FORMAT(u.dataCriacao, '%d/%m/%Y') AS entrada,
                   un.bloco, un.numResid
            FROM morador m
            JOIN usuario u ON u.idUsuario = m.idUsuario
            LEFT JOIN moradorunidade mu ON mu.Morador_idMorador = m.idMorador AND mu.dataFim IS NULL
            LEFT JOIN unidade un ON un.idUnidade = mu.Unidade_idUnidade
            ORDER BY m.idMorador DESC";
    $moradores = $conexao->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $moradores = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moradores - Eden Systems</title>
    <link rel="stylesheet" href="./CSS/dashboard.css">
    <link rel="stylesheet" href="./CSS/reset.css">
    <link rel="stylesheet" href="./CSS/SindicoPages.css">
    <script src="https://unpkg.com/lucide@latest"></script>
<?php include './Elements/favicon.php'; ?>
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include './Elements/sidebar.php'; ?>

        <div class="main-content">
            <?php include './Elements/header.php'; ?>

            <div class="dashboard-content">
                <div class="sind-page">
                    <section class="intro">
                        <h1>Tela de Moradores Condominiais</h1>
                        <p>Centraliza todos os moradores cadastrados no condomínio, organizados em uma tabela com suas respectivas
                            informações. Permite buscar moradores, visualizar seus dados e desativar suas contas.</p>
                        <a class="new-btn" href="./cadastro/morador/passo-1.php" style="text-decoration:none"><i data-lucide="plus"></i>Novo Morador</a>
                    </section>
                    <?php if ($msg): ?>
                        <p style="width:100%;padding:8px 12px;border-radius:8px;background:#e9f7ee;color:#1e5c34;border:1px solid #bfe3cb;text-align:center;margin-bottom:16px"><?= htmlspecialchars($msg) ?></p>
                    <?php endif; ?>
                    <section class="table-card">
                        <div class="table-header">
                            <div class="table-title">
                                <h2>Moradores Cadastrados</h2>
                                <div class="count"><?= count($moradores) ?> morador(es) cadastrado(s)</div>
                            </div>
                            <div class="actions">
                                <label class="search"><i data-lucide="search"></i><input id="searchInput" oninput="pesquisar()" placeholder="Pesquisar morador..."></label>
                                <button class="filter-btn" onclick="filtrar()" title="Mostrar inativos"><i data-lucide="list-filter"></i></button>
                            </div>
                        </div>
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th>Bloco</th>
                                        <th>Apartamento</th>
                                        <th>Data de entrada</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="residentTable">
                                    <?php if (empty($moradores)): ?>
                                        <tr class="f-hide-none"><td colspan="7"><div class="empty-state">Nenhum morador cadastrado.</div></td></tr>
                                    <?php else: ?>
                                        <?php foreach ($moradores as $m): ?>
                                        <?php $ativo = ((int) $m['ativo']) === 1; ?>
                                        <tr data-status="<?= $ativo ? 'Ativo' : 'Inativo' ?>">
                                            <td class="resident-id">#<?= (int) $m['idMorador'] ?></td>
                                            <td><?= htmlspecialchars($m['nome']) ?></td>
                                            <td><?= htmlspecialchars($m['bloco'] ?? '—') ?></td>
                                            <td><?= htmlspecialchars($m['numResid'] ?? '—') ?></td>
                                            <td><?= htmlspecialchars($m['entrada'] ?? '—') ?></td>
                                            <td><span class="status <?= $ativo ? 'active' : 'inactive' ?>"><?= $ativo ? 'Ativo' : 'Inativo' ?></span></td>
                                            <td>
                                                <div class="row-actions">
                                                    <button type="button" onclick='visualizar(<?= json_encode($m, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' title="Visualizar"><i data-lucide="eye"></i></button>
                                                    <?php if ($ativo): ?>
                                                    <button type="button" onclick="excluir(<?= (int) $m['idMorador'] ?>)" title="Desativar"><i data-lucide="trash-2"></i></button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="pagination" id="pager"></div>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <div class="sind-page">
    <div class="modal-overlay" id="residentModal">
        <section class="modal">
            <header class="modal-header">
                <h2>Detalhes do Morador</h2>
                <button class="close-modal" onclick="fecharModal()"><i data-lucide="x"></i></button>
            </header>
            <div class="modal-content">
                <h3 class="resident-title" id="detailId"></h3>
                <div class="detail-box"><span class="detail-label">Morador</span><strong class="detail-value" id="detailName"></strong></div>
                <div class="detail-grid">
                    <div class="detail-box"><span class="detail-label">Apartamento</span><strong class="detail-value" id="detailApartment"></strong></div>
                    <div class="detail-box"><span class="detail-label">Bloco</span><strong class="detail-value" id="detailBlock"></strong></div>
                </div>
                <div class="detail-box"><span class="detail-label">E-mail</span><strong class="detail-value" id="detailEmail"></strong></div>
                <div class="detail-box"><span class="detail-label">Telefone</span><strong class="detail-value" id="detailPhone"></strong></div>
            </div>
            <footer class="modal-footer">
                <form method="post" id="deleteForm" style="display:inline">
                    <input type="hidden" name="acao" value="desativar">
                    <input type="hidden" name="id" id="deleteId" value="">
                    <button type="submit" class="delete-button" onclick="return confirm('Deseja realmente desativar este morador?')">Excluir Morador</button>
                </form>
            </footer>
        </section>
    </div>
    </div>

    <script src="./js/sindicoPages.js"></script>
    <script>
        lucide.createIcons();
        function pesquisar() {
            filtrarLinhas('residentTable', document.getElementById('searchInput').value);
        }
        let soInativos = false;
        function filtrar() {
            soInativos = !soInativos;
            document.getElementById('searchInput').value = '';
            filtrarLinhas('residentTable', '', soInativos ? 'Inativo' : '');
        }
        function visualizar(m) {
            document.getElementById('detailId').textContent = 'Morador#' + m.idMorador;
            document.getElementById('detailName').textContent = m.nome;
            document.getElementById('detailApartment').textContent = m.numResid || '—';
            document.getElementById('detailBlock').textContent = m.bloco || '—';
            document.getElementById('detailEmail').textContent = m.email;
            document.getElementById('detailPhone').textContent = m.telefone || '—';
            document.getElementById('deleteId').value = m.idMorador;
            abrirModal('residentModal');
        }
        function excluir(id) {
            document.getElementById('deleteId').value = id;
            if (confirm('Deseja realmente desativar o morador #' + id + '?')) {
                document.getElementById('deleteForm').submit();
            }
        }
        paginar('residentTable', 'pager', 10);
    </script>
</body>
</html>
