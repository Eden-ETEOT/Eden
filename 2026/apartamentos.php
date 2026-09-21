<?php
include './Elements/auth.php';
include './Elements/ui.php';

// Condomínio de referência (último criado)
$idCondominio = null;
try {
    $idCondominio = $conexao->query("SELECT idCondominio FROM condominio ORDER BY idCondominio DESC LIMIT 1")->fetchColumn();
} catch (PDOException $e) {
    $idCondominio = null;
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    if ($acao === 'novo' && $idCondominio) {
        $num = trim($_POST['num'] ?? '');
        $bloco = trim($_POST['bloco'] ?? '');
        $andar = (int) ($_POST['andar'] ?? 0);
        $area = trim($_POST['area'] ?? '');
        if ($num !== '' && $bloco !== '') {
            $stmt = $conexao->prepare(
                "INSERT INTO unidade (numResid, andar, bloco, metragem, ativo, Condominio_idCondominio)
                 VALUES (:num, :andar, :bloco, :area, 1, :cond)"
            );
            $stmt->execute([
                'num' => $num,
                'andar' => $andar,
                'bloco' => $bloco,
                'area' => $area !== '' ? $area : null,
                'cond' => $idCondominio,
            ]);
            $msg = 'Apartamento registrado com sucesso.';
        }
    } elseif ($acao === 'status') {
        $id = (int) ($_POST['id'] ?? 0);
        $ativo = (int) ($_POST['ativo'] ?? 0) === 1 ? 1 : 0;
        if ($id > 0) {
            $stmt = $conexao->prepare("UPDATE unidade SET ativo = :a WHERE idUnidade = :id");
            $stmt->execute(['a' => $ativo, 'id' => $id]);
            $msg = $ativo ? 'Apartamento reativado com sucesso.' : 'Apartamento desativado com sucesso.';
        }
    }
}

$pageTitle = 'Apartamentos';
$menuAtivo = 'apartamentos';

// Lista de unidades com proprietário atual
$apartamentos = [];
$blocos = [];
try {
    $sql = "SELECT u.idUnidade, u.numResid, u.bloco, u.andar, u.metragem, u.ativo,
                (SELECT us.nome
                 FROM moradorunidade mu
                 JOIN morador m ON m.idMorador = mu.Morador_idMorador
                 JOIN usuario us ON us.idUsuario = m.idUsuario
                 WHERE mu.Unidade_idUnidade = u.idUnidade
                   AND mu.dataFim IS NULL AND m.tipoMorador = 'proprietario'
                 ORDER BY mu.dataInicio DESC LIMIT 1) AS proprietario
            FROM unidade u
            ORDER BY u.idUnidade DESC";
    $apartamentos = $conexao->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    $blocos = $conexao->query("SELECT DISTINCT bloco FROM unidade WHERE bloco IS NOT NULL AND bloco <> '' ORDER BY bloco")->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $apartamentos = [];
    $blocos = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<?php pageHead('Apartamentos - Eden Systems', ['./CSS/FrontDev.css', './CSS/tabelas.css'], ['https://unpkg.com/lucide@latest']); ?>
<body>
    <?php layoutOpen(); ?>
                <div class="fd-apartamentos">
                    <section class="top-content">
                        <div>
                            <h1>Tela de Apartamentos</h1>
                            <p>
                                Centraliza todos os apartamentos cadastrados do condomínio,
                                organizados em uma tabela com suas respectivas informações.
                                Permite buscar os apartamentos, visualizar seus dados e
                                atualizar o status do mesmo.
                            </p>
                        </div>
                        <div class="top-buttons">
                            <button class="new-btn" type="button" onclick="fdAbrirModal('newBlockModal')">
                                <i data-lucide="plus"></i>
                                Novo Bloco
                            </button>
                            <button class="new-btn" type="button" onclick="fdAbrirModal('newApartmentModal')">
                                <i data-lucide="plus"></i>
                                Novo Apartamento
                            </button>
                        </div>
                    </section>

<?php banner($msg); ?>

                    <section class="apartments-card">
                        <div class="card-header">
                            <div>
                                <h2>Apartamentos Cadastrados</h2>
                                <div class="count">
                                    <span id="apartmentCount"><?= count($apartamentos) ?></span>
                                    apartamento(s) cadastrado(s)
                                </div>
                            </div>
                            <div class="actions">
                                <div class="search">
                                    <i data-lucide="search"></i>
                                    <input type="text" id="aptSearchInput" placeholder="Pesquisar apartamento..." oninput="fdPesquisarApartamento()">
                                </div>
                                <button class="filter-btn" type="button" onclick="fdFiltrarApartamentos(this)" title="Mostrar somente inativos"><i data-lucide="list-filter"></i></button>
                            </div>
                        </div>

                        <div class="table-wrapper table-scroll">
                            <table class="issues-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Numeração</th>
                                        <th>Bloco</th>
                                        <th>Proprietário</th>
                                        <th>Área</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="apartmentTable">
                                    <?php if (empty($apartamentos)): ?>
                                        <tr><td colspan="7"><div class="empty-state" style="text-align:center;padding:24px;color:#819087">Nenhum apartamento cadastrado.</div></td></tr>
                                    <?php else: ?>
                                        <?php foreach ($apartamentos as $a): ?>
                                        <?php $ativo = ((int) $a['ativo']) === 1; ?>
                                        <tr data-status="<?= $ativo ? 'Ativo' : 'Inativo' ?>">
                                            <td>#<?= (int) $a['idUnidade'] ?></td>
                                            <td><?= htmlspecialchars($a['numResid']) ?></td>
                                            <td><?= htmlspecialchars($a['bloco']) ?></td>
                                            <td><?= htmlspecialchars($a['proprietario'] ?? 'Sem morador') ?></td>
                                            <td><?= $a['metragem'] !== null ? htmlspecialchars(number_format((float) $a['metragem'], 0, ',', '.') . ' m²') : '—' ?></td>
                                            <td><span class="badge <?= $ativo ? 'ativo' : 'inativo' ?>"><?= $ativo ? 'Ativo' : 'Inativo' ?></span></td>
                                            <td>
                                                <div class="tbl-actions">
                                                    <button type="button" class="tbl-action" onclick='fdVerApartamento(<?= json_encode($a, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' title="Visualizar"><i data-lucide="eye"></i></button>
                                                    <button type="button" class="tbl-action danger" onclick="fdStatusApartamento(<?= (int) $a['idUnidade'] ?>, <?= $ativo ? 0 : 1 ?>, '<?= $ativo ? 'desativar' : 'reativar' ?>')" title="<?= $ativo ? 'Desativar' : 'Reativar' ?>"><i data-lucide="trash-2"></i></button>
                                                </div>
                                            </td>
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

    <!-- Modal Novo Apartamento -->
    <div class="fd-apartamentos">
        <div class="modal-overlay" id="newApartmentModal" onclick="fdFecharClicandoFora(event, 'newApartmentModal')">
            <div class="modal apartment-register-modal">
                <div class="modal-header">
                    <h2>Novo apartamento</h2>
                    <button class="close-modal" type="button" onclick="fdFecharModal('newApartmentModal')"><i data-lucide="x"></i></button>
                </div>
                <form method="post" class="modal-form">
                    <input type="hidden" name="acao" value="novo">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Numeração do apartamento</label>
                            <input type="text" name="num" required>
                        </div>
                        <div class="form-group">
                            <label>Bloco</label>
                            <select name="bloco" id="newBlockSelect" required>
                                <option value="">Selecione</option>
                                <?php foreach ($blocos as $b): ?>
                                <option value="<?= htmlspecialchars($b) ?>"><?= htmlspecialchars($b) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Andar</label>
                            <input type="number" name="andar" min="0" value="0" required>
                        </div>
                        <div class="form-group">
                            <label>Área do apartamento (m²)</label>
                            <input type="number" name="area" min="1" step="0.01">
                        </div>
                    </div>
                    <div class="form-buttons">
                        <button type="button" class="cancel-button" onclick="fdFecharModal('newApartmentModal')">Cancelar</button>
                        <button type="submit" class="register-button">Registrar Apartamento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Novo Bloco -->
    <div class="fd-apartamentos">
        <div class="modal-overlay" id="newBlockModal" onclick="fdFecharClicandoFora(event, 'newBlockModal')">
            <div class="modal block-modal">
                <div class="modal-header">
                    <h2>Novo bloco</h2>
                    <button class="close-modal" type="button" onclick="fdFecharModal('newBlockModal')"><i data-lucide="x"></i></button>
                </div>
                <form class="modal-form" onsubmit="return fdCadastrarBloco(event)">
                    <div class="form-group">
                        <label>Nome do bloco</label>
                        <input type="text" id="blockName" placeholder="Digite aqui..." required>
                    </div>
                    <div class="form-buttons block-buttons">
                        <button type="button" class="cancel-button" onclick="fdFecharModal('newBlockModal')">Cancelar</button>
                        <button type="submit" class="register-button">Adicionar Bloco</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Detalhes -->
    <div class="fd-apartamentos">
        <div class="modal-overlay" id="apartmentDetailsModal" onclick="fdFecharClicandoFora(event, 'apartmentDetailsModal')">
            <div class="modal details-modal">
                <div class="modal-header">
                    <h2>Detalhes do Apartamento</h2>
                    <button class="close-modal" type="button" onclick="fdFecharModal('apartmentDetailsModal')"><i data-lucide="x"></i></button>
                </div>
                <div class="details-content">
                    <div class="apartment-title" id="viewApartmentTitle">—</div>
                    <div class="detail-box full">
                        <span>Morador Atual</span>
                        <strong id="viewResident">—</strong>
                    </div>
                    <div class="details-grid">
                        <div class="detail-box"><span>Apartamento</span><strong id="viewApartment">—</strong></div>
                        <div class="detail-box"><span>Bloco</span><strong id="viewBlock">—</strong></div>
                    </div>
                    <div class="details-grid">
                        <div class="detail-box"><span>Andar</span><strong id="viewFloor">—</strong></div>
                        <div class="detail-box"><span>Área</span><strong id="viewArea">—</strong></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <form method="post" id="aptStatusForm">
                        <input type="hidden" name="acao" value="status">
                        <input type="hidden" name="id" id="aptStatusId" value="">
                        <input type="hidden" name="ativo" id="aptStatusAtivo" value="0">
                        <button class="delete-button" type="submit" id="aptStatusBtn">Desativar Apartamento</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= assetUrl('./js/app.js') ?>"></script>
</body>
</html>
