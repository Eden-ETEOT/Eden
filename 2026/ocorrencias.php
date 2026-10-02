<?php
include './Elements/auth.php';
include './Elements/ui.php';
$msg = $_SESSION['flash_msg'] ?? '';
unset($_SESSION['flash_msg']);
$erro = '';
$reabrirModalOcorrencia = 0;

function mapaPrioridade($nome) {
    if (preg_match('/sem prioridade/i', $nome)) return ['none', 'Indefinida', 'badge-prioridade-sem'];
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
            $moradorInformado = trim((string) ($_POST['morador'] ?? ''));
            $morador = $moradorInformado === '' ? null : (int) $moradorInformado;
            if ($prioridade <= 0) {
                $prioridade = (int) $conexao->query("SELECT idPrioridade FROM prioridade WHERE nome = 'Indefinida' LIMIT 1")->fetchColumn();
                if ($prioridade <= 0) {
                    $stmtSemPrioridade = $conexao->prepare("INSERT INTO prioridade (ordem, nome, descricao) VALUES (0, 'Indefinida', 'Prioridade ainda não definida')");
                    $stmtSemPrioridade->execute();
                    $prioridade = (int) $conexao->lastInsertId();
                }
            }
            if ($titulo === '' || $descricao === '' || $categoria <= 0 || $prioridade <= 0
                || ($moradorInformado !== '' && $morador <= 0)) {
                throw new Exception('Preencha todos os campos obrigatórios.');
            }
            if (!$podeGerenciar || !$idCondominio) {
                throw new Exception('Sua conta precisa estar associada a um condomínio como síndico ou funcionário.');
            }
            if ($morador !== null && !moradorDoCondominio($conexao, $morador, $idCondominio)) {
                throw new Exception('Selecione um morador vinculado a uma unidade deste condomínio.');
            }
            $stmt = $conexao->prepare(
                "INSERT INTO chamados (titulo, descricao, dataPedida, status, prioridade_idPrioridade, categoria_idCategoria, morador_idMorador, Condominio_idCondominio)
                 VALUES (:titulo, :descricao, NOW(), 'analise', :prioridade, :categoria, :morador, :condominio)"
            );
            $stmt->execute(['titulo' => $titulo, 'descricao' => $descricao, 'prioridade' => $prioridade, 'categoria' => $categoria, 'morador' => $morador, 'condominio' => $idCondominio]);
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
            if (!$podeGerenciar) {
                throw new Exception('Seu perfil não tem permissão para alterar prioridades.');
            }
            if (!chamadoDoCondominio($conexao, $id, $filtroCondominio)) {
                throw new Exception('Esta ocorrência não pertence ao seu condomínio ou não possui vínculo ativo.');
            }
            $stmt = $conexao->prepare("UPDATE chamados SET prioridade_idPrioridade = :prioridade WHERE idChamados = :id AND status = 'analise'");
            $stmt->execute(['prioridade' => $prioridade, 'id' => $id]);
            if ($stmt->rowCount() === 0) {
                throw new Exception('A prioridade não foi alterada. Confirme se a ocorrência ainda está em análise e tente novamente.');
            }
            $msg = 'Prioridade definida com sucesso.';
            $reabrirModalOcorrencia = $id;
        } elseif ($acao === 'status') {
            $id = (int) ($_POST['id'] ?? 0);
            $status = $_POST['status'] ?? '';
            if ($id <= 0 || !in_array($status, ['analise', 'andamento', 'resolvida', 'cancelada'], true)) {
                throw new Exception('Dados inválidos.');
            }
            if (!$podeGerenciar) {
                throw new Exception('Seu perfil não tem permissão para alterar status.');
            }
            if (!chamadoDoCondominio($conexao, $id, $filtroCondominio)) {
                throw new Exception('Esta ocorrência não pertence ao seu condomínio ou não possui vínculo ativo.');
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
            if (!$podeGerenciar) {
                throw new Exception('Seu perfil não tem permissão para cancelar ocorrências.');
            }
            if (!chamadoDoCondominio($conexao, $id, $filtroCondominio)) {
                throw new Exception('Esta ocorrência não pertence ao seu condomínio ou não possui vínculo ativo.');
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
            $sql = "SELECT c.idChamados, c.titulo, c.descricao, c.status, c.prioridade_idPrioridade, c.categoria_idCategoria, c.dataPedida,
                   DATE_FORMAT(c.dataPedida, '%d/%m/%Y') AS dataFmt,
                   cat.nome AS categoria, p.nome AS prioridade,
                   u.nome AS morador_nome, un.numResid
            FROM chamados c
            JOIN categoria cat ON cat.idCategoria = c.categoria_idCategoria
            JOIN prioridade p ON p.idPrioridade = c.prioridade_idPrioridade
            LEFT JOIN morador m ON m.idMorador = c.morador_idMorador
            LEFT JOIN usuario u ON u.idUsuario = m.idUsuario
            LEFT JOIN moradorunidade mu ON mu.Morador_idMorador = m.idMorador AND mu.dataFim IS NULL
            LEFT JOIN unidade un ON un.idUnidade = mu.Unidade_idUnidade
                WHERE c.Condominio_idCondominio = :condominio
            ORDER BY c.dataPedida DESC";
            $stmt = $conexao->prepare($sql);
            $stmt->execute(['condominio' => $filtroCondominio]);
            $ocorrencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $ocorrencias = [];
}
$anexosPorChamado = [];
if ($ocorrencias) {
    $idsChamados = array_map('intval', array_column($ocorrencias, 'idChamados'));
    $placeholders = implode(',', array_fill(0, count($idsChamados), '?'));
    $stmt = $conexao->prepare(
        "SELECT chamados_idChamados, caminho, nomeArquivo
         FROM chamadoAnexo
         WHERE chamados_idChamados IN ($placeholders)
         ORDER BY idChamadoAnexo"
    );
    $stmt->execute($idsChamados);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $anexo) {
        $caminho = str_replace('\\', '/', (string) $anexo['caminho']);
        if (!preg_match('#^uploads/chamados/[a-f0-9]{32}\.(jpg|png|gif|webp)$#i', $caminho)) {
            continue;
        }
        $arquivo = __DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $caminho);
        if (!is_file($arquivo)) {
            continue;
        }
        $anexosPorChamado[(int) $anexo['chamados_idChamados']][] = [
            'url' => './' . $caminho,
            'nomeArquivo' => (string) $anexo['nomeArquivo'],
        ];
    }
}
foreach ($ocorrencias as &$ocorrencia) {
    $ocorrencia['anexos'] = $anexosPorChamado[(int) $ocorrencia['idChamados']] ?? [];
}
unset($ocorrencia);
$categorias = $conexao->query("SELECT idCategoria, nome FROM categoria ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
$semPrioridade = $conexao->query("SELECT idPrioridade FROM prioridade WHERE nome = 'Indefinida' LIMIT 1")->fetchColumn();
if (!$semPrioridade) {
    $stmtSemPrioridade = $conexao->prepare("INSERT INTO prioridade (ordem, nome, descricao) VALUES (0, 'Indefinida', 'Prioridade ainda não definida')");
    $stmtSemPrioridade->execute();
}
$prioridades = $conexao->query("SELECT idPrioridade, nome FROM prioridade ORDER BY idPrioridade")->fetchAll(PDO::FETCH_ASSOC);
$prioridadesPermitidas = ['Indefinida', 'Baixa', 'Média', 'Alta', 'Urgente'];
$stmt = $conexao->prepare(
    "SELECT DISTINCT m.idMorador, u.nome
     FROM morador m
     JOIN usuario u ON u.idUsuario = m.idUsuario
     JOIN moradorunidade mu ON mu.Morador_idMorador = m.idMorador AND mu.dataFim IS NULL
     JOIN unidade un ON un.idUnidade = mu.Unidade_idUnidade AND un.Condominio_idCondominio = :condominio
     WHERE u.ativo = 1
     ORDER BY u.nome"
);
$stmt->execute(['condominio' => $filtroCondominio]);
$moradoresSel = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                        <button class="new-occurrence-button" type="button" onclick="abrirModal('newModal')"><i data-lucide="plus"></i>Nova Ocorrência</button>
                    </section>
<?php banner($msg, $erro); ?>
                    <?php if (!$idCondominio): ?>
                    <p style="width:100%;padding:12px;border:1px solid #e7c77b;background:#fff8e6;color:#684d12;margin:0 0 16px">
                        Sua conta ainda não gerencia um condomínio. <a href="./configurar-condominio.php">Criar um condomínio e vincular esta conta como síndico</a>.
                    </p>
                    <?php endif; ?>
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
                                <div class="filter-control">
                                    <button class="filter-trigger" type="button" aria-label="Abrir filtros de ocorrências" aria-expanded="false" aria-controls="occurrenceFilterPanel" onclick="alternarPainelFiltro(this)"><i data-lucide="list-filter"></i></button>
                                    <div class="filter-panel" id="occurrenceFilterPanel" hidden>
                                        <div class="filter-panel-header"><strong>Filtros</strong><button type="button" class="filter-reset" onclick="limparFiltrosPainel(this)">Limpar filtros</button></div>
                                        <label><span>Status</span>
                                            <select id="occurrenceStatusFilter" onchange="aplicarFiltrosOcorrencias()">
                                                <option value="">Todos os status</option>
                                                <option value="analise">Em análise</option>
                                                <option value="andamento">Em andamento</option>
                                                <option value="resolvida">Resolvida</option>
                                                <option value="cancelada">Cancelada</option>
                                            </select>
                                        </label>
                                        <label><span>Prioridade</span>
                                            <select id="occurrencePriorityFilter" onchange="aplicarFiltrosOcorrencias()">
                                                <option value="">Todas as prioridades</option>
                                                <?php foreach ($prioridades as $prioridade): ?>
                                                <option value="<?= (int) $prioridade['idPrioridade'] ?>"><?= htmlspecialchars($prioridade['nome']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </label>
                                        <label><span>Categoria</span>
                                            <select id="occurrenceCategoryFilter" onchange="aplicarFiltrosOcorrencias()">
                                                <option value="">Todas as categorias</option>
                                                <?php foreach ($categorias as $categoria): ?>
                                                <option value="<?= (int) $categoria['idCategoria'] ?>"><?= htmlspecialchars($categoria['nome']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="table-container table-scroll">
                            <table class="issues-table">
                                <thead>
                                    <tr>
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
                                        <tr><td colspan="7"><div class="empty-state">Nenhuma ocorrência registrada.</div></td></tr>
                                    <?php else: ?>
                                        <?php foreach ($ocorrencias as $o): ?>
                                        <?php [$pc, $pl, $pcBadge] = mapaPrioridade($o['prioridade']); [$sc, $sl] = mapaStatus($o['status']); ?>
                                        <tr data-prioridade="<?= $pc ?>" data-prioridade-id="<?= (int) $o['prioridade_idPrioridade'] ?>" data-categoria-id="<?= (int) $o['categoria_idCategoria'] ?>" data-status-valor="<?= $o['status'] ?>" data-status="<?= $o['status'] ?>">
                                            <td><?= htmlspecialchars($o['titulo']) ?></td>
                                            <td><?= htmlspecialchars($o['categoria']) ?></td>
                                            <td><?= htmlspecialchars($o['numResid'] ?? '—') ?></td>
                                            <td><span class="badge <?= $pcBadge ?>"><?= $pl ?></span></td>
                                            <td><span class="badge <?= $sc ?>"><?= $sl ?></span></td>
                                            <td><?= htmlspecialchars($o['dataFmt']) ?></td>
                                            <td>
                                                <div class="tbl-actions">
                                                    <button type="button" class="tbl-action" data-chamado-id="<?= (int) $o['idChamados'] ?>" onclick='visualizar(<?= json_encode(array_merge($o, ['pc' => $pc, 'pcBadge' => $pcBadge]), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) ?>)' title="Visualizar"><i data-lucide="eye"></i></button>
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
                <div class="description-title">Imagens anexadas</div>
                <div class="occurrence-attachments" id="viewAttachments"></div>
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
                </div>
                <?php else: ?>
                <div class="update-title">Ações indisponíveis</div>
                <p>Sua conta ainda não está vinculada como síndico ou funcionário de um condomínio. <a href="./configurar-condominio.php">Configurar um condomínio</a> para habilitar o gerenciamento.</p>
                <?php endif; ?>
            </div>
            <div class="details-footer">
                <?php if ($podeGerenciar): ?>
                <form method="post" id="cancelForm" style="display:inline">
                    <input type="hidden" name="acao" value="cancelar">
                    <input type="hidden" name="id" id="cancelId" value="">
                    <button type="submit" onclick="return confirm('Deseja realmente cancelar esta ocorrência?')">Excluir Ocorrência</button>
                </form>
                <?php endif; ?>
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
                    <label>Morador (opcional)</label>
                    <select name="morador" class="select-medium-iconR">
                        <option value="">Sem morador</option>
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
            filtrarLinhas('occurrenceTable', document.getElementById('searchInput').value);
            atualizarContagemOcc();
        }
        window.__statusFiltro = <?= json_encode($filtroStatus, JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        function aplicarFiltrosOcorrencias() {
            atualizarFiltroLinhas('occurrenceTable', {
                status: document.getElementById('occurrenceStatusFilter').value,
                prioridadeId: document.getElementById('occurrencePriorityFilter').value,
                categoriaId: document.getElementById('occurrenceCategoryFilter').value
            });
            atualizarContagemOcc();
        }
        function atualizarContagemOcc() {
            const visiveis = document.querySelectorAll('#occurrenceTable tr:not(.f-hide)').length;
            document.getElementById('totalCount').textContent = visiveis;
        }
        let atualId = null;
        function visualizar(o) {
            atualId = o.idChamados;
            document.getElementById('viewTitle').textContent = o.titulo;
            document.getElementById('viewId').textContent = '#' + String(o.idChamados).padStart(3, '0');
            const vp = document.getElementById('viewPriority');
            vp.textContent = o.prioridade;
            vp.className = 'badge ' + (o.pcBadge || 'badge-prioridade-media');
            const priorityId = document.getElementById('priorityId');
            const prioritySelect = document.getElementById('prioritySelect');
            if (priorityId) priorityId.value = o.idChamados;
            if (prioritySelect) prioritySelect.value = o.prioridade_idPrioridade;
            document.getElementById('viewResident').textContent = o.morador_nome || 'Não informado';
            document.getElementById('viewApartment').textContent = o.numResid || '—';
            document.getElementById('viewCategory').textContent = o.categoria;
            document.getElementById('viewDate').textContent = o.dataFmt;
            document.getElementById('viewDescription').textContent = o.descricao;
            const attachments = document.getElementById('viewAttachments');
            attachments.replaceChildren();
            if (Array.isArray(o.anexos) && o.anexos.length) {
                o.anexos.forEach(anexo => {
                    const figure = document.createElement('figure');
                    const link = document.createElement('a');
                    link.href = anexo.url;
                    link.target = '_blank';
                    link.rel = 'noopener noreferrer';
                    link.setAttribute('aria-label', 'Abrir imagem ' + anexo.nomeArquivo + ' em nova aba');
                    const image = document.createElement('img');
                    image.src = anexo.url;
                    image.alt = anexo.nomeArquivo || 'Imagem anexada à ocorrência';
                    image.loading = 'lazy';
                    link.appendChild(image);
                    figure.appendChild(link);
                    const caption = document.createElement('figcaption');
                    caption.textContent = anexo.nomeArquivo || 'Imagem anexada';
                    figure.appendChild(caption);
                    attachments.appendChild(figure);
                });
            } else {
                const empty = document.createElement('p');
                empty.className = 'attachments-empty';
                empty.textContent = 'Nenhuma imagem anexada.';
                attachments.appendChild(empty);
            }
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
            document.getElementById('occurrenceStatusFilter').value = window.__statusFiltro;
            atualizarFiltroLinhas('occurrenceTable', { status: window.__statusFiltro });
            document.getElementById('clearStatusFilter').hidden = false;
            atualizarContagemOcc();
        }
        <?php if ($reabrirModalOcorrencia > 0): ?>
        document.querySelector('.tbl-action[data-chamado-id="<?= $reabrirModalOcorrencia ?>"]')?.click();
        <?php endif; ?>
    </script>
</body>
</html>
