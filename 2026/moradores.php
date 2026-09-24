<?php
include './Elements/auth.php';
include './Elements/ui.php';
include './Elements/convites.php';

// Desativar morador (soft delete via usuario.ativo) / Cadastrar morador via modal
$msg = '';
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    if ($acao === 'desativar') {
        $idMorador = (int) ($_POST['id'] ?? 0);
        if ($idMorador > 0) {
            if (!moradorDoCondominio($conexao, $idMorador, $filtroCondominio)) {
                $erro = 'Sem permissão para este morador.';
            } else {
                $stmt = $conexao->prepare(
                    "UPDATE usuario u JOIN morador m ON m.idUsuario = u.idUsuario
                     SET u.ativo = 0 WHERE m.idMorador = :id"
                );
                $stmt->execute(['id' => $idMorador]);
                $msg = 'Morador desativado com sucesso.';
            }
        }
    } elseif ($acao === 'convidar') {
        try {
            $idUnidade = (int) ($_POST['unidade'] ?? 0);
            $tipo = $_POST['tipo'] ?? '';
            $emailEsp = trim($_POST['email_esperado'] ?? '');
            $dias = (int) ($_POST['dias'] ?? CONVITE_VALIDADE_DIAS);
            $condUnidade = conviteCondominio($conexao, $idUnidade);
            if ($condUnidade === null || ($condUnidade !== $filtroCondominio && !eAdmin($conexao, $idUsuario))) {
                throw new Exception('Unidade fora do seu condomínio.');
            }
            [$okC, $retC] = gerarConvite($conexao, $idUsuario, $idUnidade, $tipo,
                $emailEsp !== '' ? $emailEsp : null, $dias > 0 ? $dias : CONVITE_VALIDADE_DIAS);
            if (!$okC) throw new Exception($retC);
            $linkConvite = './convite/aceitar.php?token=' . $retC;
            $msg = 'Convite gerado. Link: ' . $linkConvite;

        } catch (Exception $e) {
            $erro = $e->getMessage();
        }
    } elseif ($acao === 'cancelar-convite') {
        [$okX, $msgX] = cancelarConvite($conexao, (int) ($_POST['id'] ?? 0), $idUsuario);
        if ($okX) $msg = $msgX; else $erro = $msgX;
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
            JOIN moradorunidade mu ON mu.Morador_idMorador = m.idMorador AND mu.dataFim IS NULL
            JOIN unidade un ON un.idUnidade = mu.Unidade_idUnidade
            WHERE un.Condominio_idCondominio = :cond
            ORDER BY m.idMorador DESC";
    $stmt = $conexao->prepare($sql);
    $stmt->execute(['cond' => $filtroCondominio]);
    $moradores = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $moradores = [];
}

// Unidades ativas para o modal de convite
$unidades = [];
try {
    $stmt = $conexao->prepare(
        "SELECT idUnidade, numResid, bloco FROM unidade WHERE ativo = 1 AND Condominio_idCondominio = :cond ORDER BY bloco, numResid"
    );
    $stmt->execute(['cond' => $filtroCondominio]);
    $unidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $unidades = [];
}
$blocos = array_values(array_unique(array_column($unidades, 'bloco')));

// Convites pendentes: criados por mim ou de condomínios onde sou síndico
$convitesPend = [];
try {
    $stmt = $conexao->prepare(
        "SELECT c.idConvite, c.token, c.tipoMorador, c.emailEsperado, c.dataExpiracao,
                u.numResid, u.bloco, us.nome AS criador
         FROM convite c
         JOIN unidade u ON u.idUnidade = c.Unidade_idUnidade
         JOIN usuario us ON us.idUsuario = c.criadoPor
         WHERE c.status = 'pendente'
           AND (c.criadoPor = :u OR EXISTS (
                SELECT 1 FROM sindico s
                WHERE s.idUsuario = :u AND s.Condominio_idCondominio = u.Condominio_idCondominio))
         ORDER BY c.dataCriacao DESC"
    );
    $stmt->execute(['u' => $idUsuario]);
    $convitesPend = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $convitesPend = [];
}
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
                        <button class="new-btn" type="button" onclick="abrirModal('inviteModal')"><i data-lucide="mail-plus"></i>Gerar convite</button>
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
                    <section class="residents-card">
                        <div class="card-header">
                            <div>
                                <h2>Convites pendentes</h2>
                                <div class="count"><?= count($convitesPend) ?> convite(s) aguardando aceite</div>
                            </div>
                        </div>
                        <div class="table-wrapper table-scroll">
                            <table class="issues-table">
                                <thead>
                                    <tr>
                                        <th>Apartamento</th>
                                        <th>Tipo</th>
                                        <th>E-mail esperado</th>
                                        <th>Expira em</th>
                                        <th>Link</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($convitesPend)): ?>
                                        <tr><td colspan="6" style="text-align:center">Nenhum convite pendente.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($convitesPend as $cv): ?>
                                        <tr>
                                            <td>Bloco <?= htmlspecialchars($cv['bloco']) ?> — <?= htmlspecialchars($cv['numResid']) ?></td>
                                            <td><span class="badge medium"><?= htmlspecialchars($cv['tipoMorador']) ?></span></td>
                                            <td><?= htmlspecialchars($cv['emailEsperado'] ?? 'link aberto') ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime($cv['dataExpiracao'])) ?></td>
                                            <td>
                                                <button type="button" class="tbl-action" title="Copiar link do convite" onclick="copiarConvite(this, './convite/aceitar.php?token=<?= htmlspecialchars($cv['token']) ?>')"><i data-lucide="link"></i></button>
                                            </td>
                                            <td>
                                                <div class="tbl-actions">
                                                    <form method="post" style="display:inline" onsubmit="return confirm('Cancelar este convite?')">
                                                        <input type="hidden" name="acao" value="cancelar-convite">
                                                        <input type="hidden" name="id" value="<?= (int) $cv['idConvite'] ?>">
                                                        <button type="submit" class="tbl-action danger" title="Cancelar convite"><i data-lucide="x"></i></button>
                                                    </form>
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

    <!-- Modal Gerar convite -->
    <div class="fd-moradores">
    <div class="modal-overlay" id="inviteModal" onclick="fdFecharClicandoFora(event, 'inviteModal')">
        <div class="modal resident-modal">
            <div class="modal-top">
                <div class="modal-title">
                    <h2>Gerar convite</h2>
                    <button class="close-modal" type="button" onclick="fecharModal('inviteModal')"><i data-lucide="x"></i></button>
                </div>
            </div>
            <form method="post" class="resident-form">
                <input type="hidden" name="acao" value="convidar">
                <div class="form-group">
                    <label>Apartamento</label>
                    <select name="unidade" required>
                        <option value="">Selecione</option>
                        <?php foreach ($unidades as $u): ?>
                        <option value="<?= (int) $u['idUnidade'] ?>">Bloco <?= htmlspecialchars($u['bloco']) ?> — <?= htmlspecialchars($u['numResid']) ?></option>
                        <?php endforeach; ?>
                    </select>
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
                        <label>Validade (dias)</label>
                        <input type="number" name="dias" value="7" min="1" max="90">
                    </div>
                </div>
                <div class="form-group">
                    <label>E-mail esperado (opcional, recomendado)</label>
                    <input type="email" name="email_esperado" placeholder="pessoa@email.com">
                </div>
                <div class="form-buttons">
                    <button type="button" class="cancel-button" onclick="fecharModal('inviteModal')">Cancelar</button>
                    <button type="submit" class="register-button">Gerar convite</button>
                </div>
            </form>
        </div>
    </div>

    <script src="<?= assetUrl('./js/app.js') ?>"></script>
    <script src="<?= assetUrl('./js/mascaras.js') ?>"></script>
    <script>
        lucide.createIcons();
        function copiarConvite(btn, path) {
            const url = new URL(path, window.location.href).href;
            const done = () => toast('Link do convite copiado.', 'success');
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(done).catch(() => toast('Não foi possível copiar.', 'error'));
            } else {
                const ta = document.createElement('textarea');
                ta.value = url;
                document.body.appendChild(ta);
                ta.select();
                try { document.execCommand('copy'); done(); } catch (e) { toast('Não foi possível copiar.', 'error'); }
                ta.remove();
            }
        }
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
        paginar('residentTable', 'pager', 10);
    </script>
</body>
</html>
