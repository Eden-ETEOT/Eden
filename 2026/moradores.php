<?php
include './Elements/auth.php';
include './Elements/ui.php';

// Desativar morador (soft delete via usuario.ativo) / Cadastrar morador via modal
$msg = '';
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    if ($acao === 'desativar') {
        $idMorador = (int) ($_POST['id'] ?? 0);
        if ($idMorador > 0) {
            $stmt = $conexao->prepare(
                "UPDATE usuario u JOIN morador m ON m.idUsuario = u.idUsuario
                 SET u.ativo = 0 WHERE m.idMorador = :id"
            );
            $stmt->execute(['id' => $idMorador]);
            $msg = 'Morador desativado com sucesso.';
        }
    } elseif ($acao === 'novo') {
        try {
            $nome = trim($_POST['nome'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $cpf = trim($_POST['cpf'] ?? '');
            $telefone = trim($_POST['telefone'] ?? '');
            $idUnidade = (int) ($_POST['unidade'] ?? 0);
            $dataEntrada = trim($_POST['data_entrada'] ?? '');
            $ativo = ($_POST['status'] ?? '1') === '1' ? 1 : 0;
            $tipoMorador = $_POST['tipo'] ?? '';
            $dataNascimento = trim($_POST['data_nascimento'] ?? '');

            if ($nome === '' || $email === '' || $cpf === '' || $idUnidade <= 0
                || $dataEntrada === '' || $dataNascimento === '') {
                throw new Exception('Preencha todos os campos obrigatórios.');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('E-mail inválido.');
            }
            if (!in_array($tipoMorador, ['proprietario', 'inquilino', 'dependente'], true)) {
                throw new Exception('Tipo de morador inválido.');
            }
            $stmt = $conexao->prepare("SELECT idUnidade FROM unidade WHERE idUnidade = :id AND ativo = 1");
            $stmt->execute(['id' => $idUnidade]);
            if (!$stmt->fetchColumn()) {
                throw new Exception('Apartamento inválido ou inativo.');
            }

            $senhaTemp = bin2hex(random_bytes(4));
            $conexao->beginTransaction();
            $stmt = $conexao->prepare(
                "INSERT INTO usuario (email, senha, CPF, telefone, nome, foto, ativo)
                 VALUES (:email, :senha, :cpf, :telefone, :nome, NULL, :ativo)"
            );
            $stmt->execute([
                'email' => $email,
                'senha' => password_hash($senhaTemp, PASSWORD_DEFAULT),
                'cpf' => $cpf,
                'telefone' => $telefone !== '' ? $telefone : null,
                'nome' => $nome,
                'ativo' => $ativo,
            ]);
            $idUsuarioNovo = (int) $conexao->lastInsertId();
            $stmt = $conexao->prepare(
                "INSERT INTO morador (idUsuario, tipoMorador, dataNascimento)
                 VALUES (:u, :t, :n)"
            );
            $stmt->execute(['u' => $idUsuarioNovo, 't' => $tipoMorador, 'n' => $dataNascimento]);
            $idMoradorNovo = (int) $conexao->lastInsertId();
            $stmt = $conexao->prepare(
                "INSERT INTO moradorunidade (Morador_idMorador, Unidade_idUnidade, dataInicio, dataFim)
                 VALUES (:m, :un, :d, NULL)"
            );
            $stmt->execute(['m' => $idMoradorNovo, 'un' => $idUnidade, 'd' => $dataEntrada]);
            $conexao->commit();
            $msg = 'Morador cadastrado com sucesso. Senha temporária: ' . $senhaTemp;
        } catch (PDOException $e) {
            if ($conexao->inTransaction()) $conexao->rollBack();
            $erro = ($e->getCode() == 23000) ? 'E-mail ou CPF já cadastrado.' : 'Erro no banco de dados.';
        } catch (Exception $e) {
            if ($conexao->inTransaction()) $conexao->rollBack();
            $erro = $e->getMessage();
        }
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

// Unidades ativas para o modal "Novo Morador"
$unidades = [];
try {
    $unidades = $conexao->query(
        "SELECT idUnidade, numResid, bloco FROM unidade WHERE ativo = 1 ORDER BY bloco, numResid"
    )->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $unidades = [];
}
$blocos = array_values(array_unique(array_column($unidades, 'bloco')));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<?php pageHead('Moradores - Eden Systems', ['./CSS/FrontDev.css', './CSS/tabelas.css'], ['https://unpkg.com/lucide@latest']); ?>
<body>
    <?php layoutOpen(); ?>
                <div class="fd-moradores">
                    <section class="top-content">
                        <div>
                            <h1>Tela de Moradores Condominiais</h1>
                            <p>Centraliza todos os moradores cadastrados no condomínio,
                                organizados em uma tabela com suas respectivas informações.
                                Permite buscar moradores, visualizar seus dados e desativar
                                suas contas.</p>
                        </div>
                        <button class="new-btn" type="button" onclick="abrirModal('newResidentModal')"><i data-lucide="plus"></i>Novo Morador</button>
                    </section>
<?php banner($msg, $erro); ?>
                    <section class="residents-card">
                        <div class="card-header">
                            <div>
                                <h2>Moradores Cadastrados</h2>
                                <div class="count"><?= count($moradores) ?> morador(es) cadastrado(s)</div>
                            </div>
                            <div class="actions">
                                <label class="search"><i data-lucide="search"></i><input id="searchInput" oninput="pesquisar()" placeholder="Pesquisar morador..."></label>
                                <button class="filter-btn" onclick="filtrar(this)" title="Mostrar somente inativos"><i data-lucide="list-filter"></i></button>
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

    <!-- Modal Novo Morador -->
    <div class="fd-moradores">
    <div class="modal-overlay" id="newResidentModal" onclick="fdFecharClicandoFora(event, 'newResidentModal')">
        <div class="modal resident-modal">
            <div class="modal-top">
                <div class="modal-title">
                    <h2>Novo Morador</h2>
                    <button class="close-modal" type="button" onclick="fecharModal('newResidentModal')"><i data-lucide="x"></i></button>
                </div>
            </div>
            <form method="post" class="resident-form">
                <input type="hidden" name="acao" value="novo">
                <div class="form-group">
                    <label>Nome do Morador</label>
                    <input type="text" name="nome" placeholder="Digite o nome completo" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Apartamento</label>
                        <select name="unidade" id="newUnidade" onchange="syncBloco()" required>
                            <option value="">Ex: 204</option>
                            <?php if (empty($unidades)): ?>
                            <option value="" disabled>Nenhum apartamento cadastrado</option>
                            <?php endif; ?>
                            <?php foreach ($unidades as $u): ?>
                            <option value="<?= (int) $u['idUnidade'] ?>" data-bloco="<?= htmlspecialchars($u['bloco']) ?>"><?= htmlspecialchars($u['numResid']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Bloco</label>
                        <select id="newBloco" onchange="filtrarApts()" required>
                            <option value="">Ex: 2</option>
                            <?php if (empty($blocos)): ?>
                            <option value="" disabled>Nenhum bloco cadastrado</option>
                            <?php endif; ?>
                            <?php foreach ($blocos as $b): ?>
                            <option value="<?= htmlspecialchars($b) ?>"><?= htmlspecialchars($b) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>E-mail</label>
                    <input type="email" name="email" placeholder="email@exemplo.com" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>CPF</label>
                        <input type="text" name="cpf" id="cpf" placeholder="000.000.000-00" maxlength="14" inputmode="numeric" required>
                    </div>
                    <div class="form-group">
                        <label>Telefone</label>
                        <input type="text" name="telefone" placeholder="(21)99999-9999">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Data de entrada</label>
                        <input type="date" name="data_entrada" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" required>
                            <option value="1">Ativo</option>
                            <option value="0">Inativo</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Tipo de morador</label>
                        <select name="tipo" required>
                            <option value="proprietario">Proprietário</option>
                            <option value="inquilino">Inquilino</option>
                            <option value="dependente">Dependente</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Data de nascimento</label>
                        <input type="date" name="data_nascimento" required>
                    </div>
                </div>
                <div class="form-buttons">
                    <button type="button" class="cancel-button" onclick="fecharModal('newResidentModal')">Cancelar</button>
                    <button type="submit" class="register-button">Cadastrar Morador</button>
                </div>
            </form>
        </div>
    </div>
    </div>

    <script src="<?= assetUrl('./js/app.js') ?>"></script>
    <script src="<?= assetUrl('./js/mascaras.js') ?>"></script>
    <script>
        lucide.createIcons();
        function pesquisar() {
            filtrarLinhas('residentTable', document.getElementById('searchInput').value);
        }
        let soInativos = false;
        function filtrar(btn) {
            soInativos = !soInativos;
            document.getElementById('searchInput').value = '';
            filtrarLinhas('residentTable', '', soInativos ? 'Inativo' : '');
            retornoFiltro('residentTable', btn, soInativos);
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
        function filtrarApts() {
            const b = document.getElementById('newBloco').value;
            const sel = document.getElementById('newUnidade');
            let first = '';
            [...sel.options].forEach(o => {
                const show = o.value === '' || !b || o.dataset.bloco === b;
                o.hidden = !show;
                if (show && o.value !== '' && first === '') first = o.value;
            });
            sel.value = first;
        }
        function syncBloco() {
            const sel = document.getElementById('newUnidade');
            const opt = sel.options[sel.selectedIndex];
            if (opt && opt.dataset.bloco) document.getElementById('newBloco').value = opt.dataset.bloco;
        }
        paginar('residentTable', 'pager', 10);
    </script>
</body>
</html>
