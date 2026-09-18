<?php
include './Elements/auth.php';
include './Elements/ui.php';

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
<?php pageHead('Moradores - Eden Systems', ['./CSS/FrontDev.css', './CSS/tabelas.css'], ['https://unpkg.com/lucide@latest']); ?>
<body>
    <div class="dashboard-wrapper">
        <?php include './Elements/sidebar.php'; ?>

        <div class="main-content">
            <?php include './Elements/header.php'; ?>

            <div class="dashboard-content">
                <div class="fd-moradores">
                    <section class="top-content">
                        <div>
                            <h1>Tela de Moradores Condominiais</h1>
                            <p>Centraliza todos os moradores cadastrados no condomínio,
                                organizados em uma tabela com suas respectivas informações.
                                Permite buscar moradores, visualizar seus dados e desativar
                                suas contas.</p>
                        </div>
                        <a class="new-btn" href="./cadastro/morador/passo-1.php" style="text-decoration:none"><i data-lucide="plus"></i>Novo Morador</a>
                    </section>
<?php banner($msg); ?>
                    <section class="residents-card">
                        <div class="card-header">
                            <div>
                                <h2>Moradores Cadastrados</h2>
                                <div class="count"><?= count($moradores) ?> morador(es) cadastrado(s)</div>
                            </div>
                            <div class="actions">
                                <label class="search"><i data-lucide="search"></i><input id="searchInput" oninput="pesquisar()" placeholder="Pesquisar morador..."></label>
                                <button class="filter-btn" onclick="filtrar()" title="Mostrar inativos"><i data-lucide="list-filter"></i></button>
                            </div>
                        </div>
                        <div class="table-wrapper table-scroll">
                            <table class="issues-table">
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
                                            <td><span class="badge <?= $ativo ? 'ativo' : 'inativo' ?>"><?= $ativo ? 'Ativo' : 'Inativo' ?></span></td>
                                            <td>
                                                <div class="tbl-actions">
                                                    <button type="button" class="tbl-action" onclick='visualizar(<?= json_encode($m, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' title="Visualizar"><i data-lucide="eye"></i></button>
                                                    <?php if ($ativo): ?>
                                                    <button type="button" class="tbl-action danger" onclick="excluir(<?= (int) $m['idMorador'] ?>)" title="Desativar"><i data-lucide="trash-2"></i></button>
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

    <div class="fd-moradores">
    <div class="modal-overlay" id="residentModal" onclick="fdFecharClicandoFora(event, 'residentModal')">
        <div class="modal resident-modal">
            <div class="modal-top">
                <div class="modal-title">
                    <h2>Detalhes do Morador</h2>
                    <button class="close-modal" type="button" onclick="fecharModal('residentModal')"><i data-lucide="x"></i></button>
                </div>
            </div>
            <div class="modal-content">
                <div class="resident-number" id="detailId"></div>
                <div class="resident-info-box full"><span class="resident-label">Morador</span><strong id="detailName"></strong></div>
                <div class="resident-info-grid">
                    <div class="resident-info-box"><span class="resident-label">Apartamento</span><strong id="detailApartment"></strong></div>
                    <div class="resident-info-box"><span class="resident-label">Bloco</span><strong id="detailBlock"></strong></div>
                </div>
                <div class="resident-info-box full"><span class="resident-label">E-mail</span><strong id="detailEmail"></strong></div>
                <div class="resident-info-box full"><span class="resident-label">Telefone</span><strong id="detailPhone"></strong></div>
            </div>
            <div class="modal-footer">
                <form method="post" id="deleteForm" style="display:inline">
                    <input type="hidden" name="acao" value="desativar">
                    <input type="hidden" name="id" id="deleteId" value="">
                    <button type="submit" class="delete-button" onclick="return confirm('Deseja realmente desativar este morador?')">Excluir Morador</button>
                </form>
            </div>
        </div>
    </div>
    </div>

    <script src="./js/sindicoPages.js"></script>
    <script src="./js/FrontDev.js"></script>
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
