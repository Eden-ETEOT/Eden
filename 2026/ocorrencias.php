<?php
include './Elements/auth.php';
include './Elements/ui.php';
$msg = '';
$erro = '';

function mapaPrioridade($nome) {
    if (preg_match('/sem prioridade/i', $nome)) return ['none', 'Sem prioridade', 'badge-prioridade-sem'];
    if (preg_match('/urgente|cr[ií]tica/i', $nome)) return ['urgent', 'Urgente', 'badge-prioridade-urgente'];
    if (preg_match('/baixa/i', $nome)) return ['low', 'Baixa', 'badge-prioridade-baixa'];
    if (preg_match('/alta/i', $nome)) return ['high', 'Alta', 'badge-prioridade-alta'];
    return ['medium', 'Média', 'badge-prioridade-media'];
}
function mapaStatus($status) {
    return [
        'analise' => ['badge-status-analise', 'Em análise'],
        'andamento' => ['badge-status-andamento', 'Em andamento'],
        'resolvida' => ['badge-status-finalizado', 'Finalizado'],
        'cancelada' => ['badge-status-cancelado', 'Cancelado'],
    ][$status] ?? ['analysis', $status];
}

// ---- Ações (criar / status / cancelar) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    try {
        if ($acao === 'criar') {
            $titulo = trim($_POST['titulo'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');
            $categoria = (int) ($_POST['categoria'] ?? 0);
            $prioridade = (int) ($_POST['prioridade'] ?? 0);
            $morador = (int) ($_POST['morador'] ?? 0);
            if ($prioridade <= 0) {
                $prioridade = (int) $conexao->query("SELECT idPrioridade FROM prioridade WHERE nome = 'Sem prioridade' LIMIT 1")->fetchColumn();
                if ($prioridade <= 0) {
                    $stmtSemPrioridade = $conexao->prepare("INSERT INTO prioridade (ordem, nome, descricao) VALUES (0, 'Sem prioridade', 'Prioridade ainda não definida')");
                    $stmtSemPrioridade->execute();
                    $prioridade = (int) $conexao->lastInsertId();
                }
            }
            if ($titulo === '' || $descricao === '' || $categoria <= 0 || $prioridade <= 0 || $morador <= 0) {
                throw new Exception('Preencha todos os campos obrigatórios.');
            }
            $stmt = $conexao->prepare(
                "INSERT INTO chamados (titulo, descricao, dataPedida, status, prioridade_idPrioridade, categoria_idCategoria, morador_idMorador)
                 VALUES (:titulo, :descricao, NOW(), 'analise', :prioridade, :categoria, :morador)"
            );
            $stmt->execute(['titulo' => $titulo, 'descricao' => $descricao, 'prioridade' => $prioridade, 'categoria' => $categoria, 'morador' => $morador]);
            $novoId = (int) $conexao->lastInsertId();
            if (isset($_FILES['anexo']) && $_FILES['anexo']['error'] !== UPLOAD_ERR_NO_FILE) {
                $arq = $_FILES['anexo'];
                $perm = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
                $img = $arq['error'] === UPLOAD_ERR_OK ? getimagesize($arq['tmp_name']) : false;
                if ($img !== false && isset($perm[$img['mime']]) && $arq['size'] <= 5 * 1024 * 1024) {
                    $dir = __DIR__ . '/uploads/chamados';
                    if (!is_dir($dir)) mkdir($dir, 0755, true);
                    $nomeArq = bin2hex(random_bytes(16)) . '.' . $perm[$img['mime']];
                    if (move_uploaded_file($arq['tmp_name'], $dir . DIRECTORY_SEPARATOR . $nomeArq)) {
                        $stmt = $conexao->prepare("INSERT INTO chamadoAnexo (caminho, nomeArquivo, chamados_idChamados) VALUES (:c, :n, :id)");
                        $stmt->execute(['c' => 'uploads/chamados/' . $nomeArq, 'n' => $arq['name'], 'id' => $novoId]);
                    }
                }
            }
            $msg = 'Ocorrência registrada com sucesso.';
        } elseif ($acao === 'definir_prioridade') {
            $id = (int) ($_POST['id'] ?? 0);
            $prioridade = (int) ($_POST['prioridade'] ?? 0);
            $prioridadeStmt = $conexao->prepare("SELECT nome FROM prioridade WHERE idPrioridade = :id");
            $prioridadeStmt->execute(['id' => $prioridade]);
            $nomePrioridade = $prioridadeStmt->fetchColumn();
            if ($id <= 0 || !in_array($nomePrioridade, ['Baixa', 'Média', 'Alta', 'Urgente'], true)) {
                throw new Exception('Defina uma prioridade válida.');
            }
            $stmt = $conexao->prepare("UPDATE chamados SET prioridade_idPrioridade = :prioridade WHERE idChamados = :id AND status = 'analise'");
            $stmt->execute(['prioridade' => $prioridade, 'id' => $id]);
            $msg = 'Prioridade definida com sucesso.';
        } elseif ($acao === 'status') {
            $id = (int) ($_POST['id'] ?? 0);
            $status = $_POST['status'] ?? '';
            if ($id <= 0 || !in_array($status, ['analise', 'andamento', 'resolvida', 'cancelada'], true)) {
                throw new Exception('Dados inválidos.');
            }
            if (!$podeGerenciar || !chamadoDoCondominio($conexao, $id, $filtroCondominio)) {
                throw new Exception('Sem permissão para esta ocorrência.');
            }
            if ($status !== 'analise') {
                $prioridadeAtual = $conexao->prepare(
                    "SELECT p.nome FROM chamados c JOIN prioridade p ON p.idPrioridade = c.prioridade_idPrioridade WHERE c.idChamados = :id"
                );
                $prioridadeAtual->execute(['id' => $id]);
                if (stripos((string) $prioridadeAtual->fetchColumn(), 'sem prioridade') !== false) {
                    throw new Exception('Defina uma prioridade antes de avançar o status.');
                }
            }
            if ($status === 'resolvida') {
                $stmt = $conexao->prepare("UPDATE chamados SET status = :s, dataRealizada = COALESCE(dataRealizada, NOW()) WHERE idChamados = :id");
            } else {
                $stmt = $conexao->prepare("UPDATE chamados SET status = :s, dataRealizada = NULL WHERE idChamados = :id");
            }
            $stmt->execute(['s' => $status, 'id' => $id]);
            $msg = 'Status atualizado com sucesso.';
        } elseif ($acao === 'cancelar') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) throw new Exception('Ocorrência inválida.');
            if (!$podeGerenciar || !chamadoDoCondominio($conexao, $id, $filtroCondominio)) {
                throw new Exception('Sem permissão para esta ocorrência.');
            }
            $stmt = $conexao->prepare("UPDATE chamados SET status = 'cancelada' WHERE idChamados = :id");
            $stmt->execute(['id' => $id]);
            $msg = 'Ocorrência cancelada.';
        }
    } catch (PDOException $e) {
        $erro = 'Erro no banco de dados.';
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

$pageTitle = 'Ocorrências';
$menuAtivo = 'ocorrencias';
$filtroStatus = $_GET['status'] ?? '';
if (!in_array($filtroStatus, ['analise', 'andamento', 'resolvida', 'cancelada'], true)) {
    $filtroStatus = '';
}
$rotuloFiltro = ['analise' => 'Em análise', 'andamento' => 'Em andamento', 'resolvida' => 'Resolvidas', 'cancelada' => 'Canceladas'][$filtroStatus] ?? '';

// Lista de ocorrências
$ocorrencias = [];
try {
            $sql = "SELECT c.idChamados, c.titulo, c.descricao, c.status, c.prioridade_idPrioridade, c.dataPedida,
                   DATE_FORMAT(c.dataPedida, '%d/%m/%Y') AS dataFmt,
                   cat.nome AS categoria, p.nome AS prioridade,
                   u.nome AS morador_nome, un.numResid
            FROM chamados c
            JOIN categoria cat ON cat.idCategoria = c.categoria_idCategoria
            JOIN prioridade p ON p.idPrioridade = c.prioridade_idPrioridade
            JOIN morador m ON m.idMorador = c.morador_idMorador
            JOIN usuario u ON u.idUsuario = m.idUsuario
            LEFT JOIN moradorunidade mu ON mu.Morador_idMorador = m.idMorador AND mu.dataFim IS NULL
            LEFT JOIN unidade un ON un.idUnidade = mu.Unidade_idUnidade
            ORDER BY c.dataPedida DESC";
    $ocorrencias = $conexao->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $ocorrencias = [];
}
$nUrgentes = 0;
foreach ($ocorrencias as $o) {
    if (in_array($o['status'], ['analise', 'andamento'], true) && mapaPrioridade($o['prioridade'])[0] === 'urgent') {
        $nUrgentes++;
    }
}
$categorias = $conexao->query("SELECT idCategoria, nome FROM categoria ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
$semPrioridade = $conexao->query("SELECT idPrioridade FROM prioridade WHERE nome = 'Sem prioridade' LIMIT 1")->fetchColumn();
if (!$semPrioridade) {
    $stmtSemPrioridade = $conexao->prepare("INSERT INTO prioridade (ordem, nome, descricao) VALUES (0, 'Sem prioridade', 'Prioridade ainda não definida')");
    $stmtSemPrioridade->execute();
}
$prioridades = $conexao->query("SELECT idPrioridade, nome FROM prioridade ORDER BY idPrioridade")->fetchAll(PDO::FETCH_ASSOC);
$prioridadesPermitidas = ['Sem prioridade', 'Baixa', 'Média', 'Alta', 'Urgente'];
$moradoresSel = $conexao->query("SELECT m.idMorador, u.nome FROM morador m JOIN usuario u ON u.idUsuario = m.idUsuario WHERE u.ativo = 1 ORDER BY u.nome")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<?php pageHead('Ocorrências - Eden Systems', ['', './CSS/FrontDev.css', './CSS/tabelas.css', './CSS/variaveis.css'], ['https://unpkg.com/lucide@latest']); ?>
<body>
    <?php layoutOpen(); ?>
                <div class="fd-ocorrencias">
                    <section class="intro">
                        <div>
                            <h1>Tela de Ocorrências Condominiais</h1>
                            <p>Permite consultar, visualizar e excluir as ocorrências, além de registrar novas ocorrências.</p>
                        </div>
                        <div class="urgent-box">
                            <img src="./assets/icones/alerta-vermelho.svg" alt="Urgência">
                            <strong><span id="urgentCount"><?= $nUrgentes ?></span> ocorrências com Urgência</strong>
                            <div class="urgent-divider"></div>
                            <button type="button" onclick="mostrarUrgentes()">Ver todas</button>
                        </div>
                    </section>
<?php banner($msg, $erro); ?>
                    <section class="occurrence-card">
                        <div class="card-header">
                            <div>
                                <h2>Histórico de Ocorrências</h2>
                                <p><span id="totalCount"><?= count($ocorrencias) ?></span> ocorrências encontradas
                                <a id="clearStatusFilter" href="./ocorrencias.php" hidden style="font-size:12px;color:var(--orange1-default);font-weight:600"> · limpar filtro</a></p>
                            </div>
                            <div class="card-actions">
                                <div class="search-box">
                                    <i data-lucide="search"></i>
                                    <input id="searchInput" class="input-field-default-m" type="text" placeholder="Pesquisar ocorrência..." oninput="pesquisarOcorrencias()">
                                </div>
                                <button class="filter-button" type="button" onclick="filtrarUrgentes(this)" title="Mostrar somente urgentes"><i data-lucide="list-filter"></i></button>
                                <button class="new-occurrence-button" type="button" onclick="abrirModal('newModal')"><i data-lucide="plus"></i>Ocorrência</button>
                            </div>
                        </div>
                        <div class="table-container table-scroll">
                            <table class="issues-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Título</th>
                                        <th>Categoria</th>
                                        <th>Apartamento</th>
                                        <th>Prioridade</th>
                                        <th>Status</th>
                                        <th>Data</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="occurrenceTable">
                                    <?php if (empty($ocorrencias)): ?>
                                        <tr><td colspan="8"><div class="empty-state">Nenhuma ocorrência registrada.</div></td></tr>
                                    <?php else: ?>
                                        <?php foreach ($ocorrencias as $o): ?>
                                        <?php [$pc, $pl, $pcBadge] = mapaPrioridade($o['prioridade']); [$sc, $sl] = mapaStatus($o['status']); ?>
                                        <tr data-prioridade="<?= $pc ?>" data-status-valor="<?= $o['status'] ?>" data-status="<?= $o['status'] ?>">
                                            <td class="resident-id">#<?= str_pad((int) $o['idChamados'], 3, '0', STR_PAD_LEFT) ?></td>
                                            <td><?= htmlspecialchars($o['titulo']) ?></td>
                                            <td><?= htmlspecialchars($o['categoria']) ?></td>
                                            <td><?= htmlspecialchars($o['numResid'] ?? '—') ?></td>
                                            <td><span class="badge <?= $pcBadge ?>"><?= $pl ?></span></td>
                                            <td><span class="badge <?= $sc ?>"><?= $sl ?></span></td>
                                            <td><?= htmlspecialchars($o['dataFmt']) ?></td>
                                            <td>
                                                <div class="tbl-actions">
                                                    <button type="button" class="tbl-action" onclick='visualizar(<?= json_encode(array_merge($o, ['pc' => $pc, 'pcBadge' => $pcBadge]), JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' title="Visualizar"><i data-lucide="eye"></i></button>
                                                    <?php if ($o['status'] !== 'cancelada' && $podeGerenciar): ?>
                                                    <button type="button" class="tbl-action danger" onclick="cancelarOcorrencia(<?= (int) $o['idChamados'] ?>)" title="Cancelar"><i data-lucide="trash-2"></i></button>
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

    <div class="fd-ocorrencias">
    <div class="modal-overlay" id="viewModal" onclick="fdFecharClicandoFora(event, 'viewModal')">
        <div class="modal details-modal">
            <div class="modal-header">
                <h2 id="viewTitle"></h2>
                <button type="button" onclick="fecharModal('viewModal')"><i data-lucide="x"></i></button>
            </div>
            <div class="details-content">
                <div class="details-top">
                    <h3>Ocorrência <span id="viewId"></span></h3>
                    <span id="viewPriority" class="badge badge-prioridade-media"></span>
                </div>
                <div class="detail-grid">
                    <div class="detail-box"><span>Morador</span><strong id="viewResident"></strong></div>
                    <div class="detail-box"><span>Apartamento</span><strong id="viewApartment"></strong></div>
                    <div class="detail-box"><span>Categoria</span><strong id="viewCategory"></strong></div>
                    <div class="detail-box"><span>Data da Ocorrência</span><strong id="viewDate"></strong></div>
                </div>
                <div class="description-title">Descrição</div>
                <div class="description-box" id="viewDescription"></div>
                <?php if ($podeGerenciar): ?><div class="update-title">Atualizar prioridade</div>
                <form method="post" class="priority-form">
                    <input type="hidden" name="acao" value="definir_prioridade">
                    <input type="hidden" name="id" id="priorityId" value="">
                    <select name="prioridade" id="prioritySelect" class="select-medium-iconR" onchange="this.form.submit()" required>
                        <option value="">Definir prioridade</option>
                        <?php foreach ($prioridades as $pp): ?>
                        <?php if (!in_array($pp['nome'], ['Baixa', 'Média', 'Alta', 'Urgente'], true)) continue; ?>
                        <option value="<?= (int) $pp['idPrioridade'] ?>"><?= htmlspecialchars($pp['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
                <div class="update-title">Atualizar Status</div>
                <div class="status-buttons" id="statusButtons">
                    <button type="button" data-status="resolvida" onclick="alterarStatus(this)">Finalizado</button>
                    <button type="button" data-status="andamento" onclick="alterarStatus(this)">Em andamento</button>
                    <button type="button" data-status="analise" onclick="alterarStatus(this)">Em análise</button>
                    <button type="button" data-status="cancelada" onclick="alterarStatus(this)">Cancelado</button>
                </div><?php endif; ?>
            </div>
            <div class="details-footer">
                <form method="post" id="cancelForm" style="display:inline">
                    <input type="hidden" name="acao" value="cancelar">
                    <input type="hidden" name="id" id="cancelId" value="">
                    <button type="submit" onclick="return confirm('Deseja realmente cancelar esta ocorrência?')">Excluir Ocorrência</button>
                </form>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="newModal" onclick="fdFecharClicandoFora(event, 'newModal')">
        <div class="modal new-modal">
            <div class="modal-header">
                <h2>Nova Ocorrência</h2>
                <button type="button" onclick="fecharModal('newModal')"><i data-lucide="x"></i></button>
            </div>
            <form class="new-occurrence-form" method="post" enctype="multipart/form-data">
                <input type="hidden" name="acao" value="criar">
                <div class="form-group">
                    <label>Título</label>
                            <input class="input-field-default-m" type="text" name="titulo" placeholder="Descreva o problema brevemente" required>
                </div>
                <div class="form-group">
                    <label>Categoria</label>
                    <select name="categoria" class="select-medium-iconR" required>
                        <?php foreach ($categorias as $cat): ?>
                        <option value="<?= (int) $cat['idCategoria'] ?>"><?= htmlspecialchars($cat['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Morador</label>
                    <select name="morador" class="select-medium-iconR" required>
                        <?php foreach ($moradoresSel as $mm): ?>
                        <option value="<?= (int) $mm['idMorador'] ?>"><?= htmlspecialchars($mm['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="description-label">Descrição</label>
                    <textarea class="input-field-default-l" name="descricao" placeholder="Descreva detalhadamente a ocorrência..." required></textarea>
                </div>
                <div class="image-area">
                    <input type="file" id="imageInput" name="anexo" accept="image/*" hidden onchange="mostrarArquivo()">
                    <button type="button" class="attach-button" onclick="document.getElementById('imageInput').click()">Anexar imagem</button>
                    <span id="fileName"></span>
                </div>
                <div class="modal-form-buttons">
                    <button type="button" class="cancel-button" onclick="fecharModal('newModal')">Cancelar</button>
                    <button type="submit" class="register-button">Registrar Ocorrência</button>
                </div>
            </form>
        </div>
    </div>

    <form method="post" id="statusForm" style="display:none">
        <input type="hidden" name="acao" value="status">
        <input type="hidden" name="id" id="statusId" value="">
        <input type="hidden" name="status" id="statusValor" value="">
    </form>
    </div>

    <script src="<?= assetUrl('./js/app.js') ?>"></script>
    <script>
        lucide.createIcons();
        function pesquisarOcorrencias() {
            filtrarLinhas('occurrenceTable', document.getElementById('searchInput').value, window.__statusFiltro || '');
            atualizarContagemOcc();
        }
        window.__statusFiltro = <?= json_encode($filtroStatus, JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        function atualizarContagemOcc() {
            const visiveis = document.querySelectorAll('#occurrenceTable tr:not(.f-hide)').length;
            document.getElementById('totalCount').textContent = visiveis;
        }
        let urgentes = false;
        function filtrarUrgentes(btn) {
            urgentes = !urgentes;
            document.getElementById('searchInput').value = '';
            filtrarLinhas('occurrenceTable', '', window.__statusFiltro || '');
            if (urgentes) {
                document.querySelectorAll('#occurrenceTable tr').forEach(tr => {
                    if (tr.dataset.prioridade !== 'urgent') tr.classList.add('f-hide');
                });
            }
            if (pagEstado['occurrenceTable']) { pagEstado['occurrenceTable'].pagina = 1; desenharPaginacao('occurrenceTable'); }
            atualizarContagemOcc();
            retornoFiltro('occurrenceTable', btn, urgentes);
        }
        function mostrarUrgentes() { if (!urgentes) filtrarUrgentes(); }
        let atualId = null;
        function visualizar(o) {
            atualId = o.idChamados;
            document.getElementById('viewTitle').textContent = o.titulo;
            document.getElementById('viewId').textContent = '#' + String(o.idChamados).padStart(3, '0');
            const vp = document.getElementById('viewPriority');
            vp.textContent = o.prioridade;
            vp.className = 'badge ' + (o.pcBadge || 'badge-prioridade-media');
            document.getElementById('priorityId').value = o.idChamados;
            document.getElementById('prioritySelect').value = o.prioridade_idPrioridade;
            document.getElementById('viewResident').textContent = o.morador_nome;
            document.getElementById('viewApartment').textContent = o.numResid || '—';
            document.getElementById('viewCategory').textContent = o.categoria;
            document.getElementById('viewDate').textContent = o.dataFmt;
            document.getElementById('viewDescription').textContent = o.descricao;
            document.getElementById('cancelId').value = o.idChamados;
            document.querySelectorAll('#statusButtons button').forEach(b => {
                b.classList.toggle('selected', b.dataset.status === o.status);
            });
            abrirModal('viewModal');
        }
        function alterarStatus(btn) {
            document.getElementById('statusId').value = atualId;
            document.getElementById('statusValor').value = btn.dataset.status;
            document.getElementById('statusForm').submit();
        }
        function cancelarOcorrencia(id) {
            document.getElementById('cancelId').value = id;
            if (confirm('Deseja realmente cancelar a ocorrência #' + id + '?')) {
                document.getElementById('cancelForm').submit();
            }
        }
        function mostrarArquivo() {
            const inp = document.getElementById('imageInput');
            document.getElementById('fileName').textContent = inp.files.length ? inp.files[0].name : '';
        }
        paginar('occurrenceTable', 'pager', 10);
        if (window.__statusFiltro) {
            document.getElementById('searchInput').value = '';
            filtrarLinhas('occurrenceTable', '', window.__statusFiltro);
            document.getElementById('clearStatusFilter').hidden = false;
            atualizarContagemOcc();
        }
    </script>
</body>
</html>
