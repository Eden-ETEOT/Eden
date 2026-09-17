<?php
session_start();
include './config/conexao.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ./auth/login.php');
    exit;
}

$idUsuario = $_SESSION['id_usuario'];
$msg = '';
$erro = '';

function tamanhoHumano($bytes) {
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1048576) return number_format($bytes / 1024, 1, ',', '.') . ' KB';
    return number_format($bytes / 1048576, 1, ',', '.') . ' MB';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    try {
        if ($acao === 'criar') {
            $nome = trim($_POST['nome'] ?? '');
            $tipo = trim($_POST['tipo'] ?? '');
            $condominio = (int) ($_POST['condominio'] ?? 0);
            if ($nome === '' || $tipo === '' || $condominio <= 0) {
                throw new Exception('Preencha todos os campos obrigatórios.');
            }
            if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] === UPLOAD_ERR_NO_FILE) {
                throw new Exception('Anexe o arquivo do documento.');
            }
            $arq = $_FILES['arquivo'];
            if ($arq['error'] !== UPLOAD_ERR_OK || $arq['size'] > 10 * 1024 * 1024) {
                throw new Exception('Arquivo inválido ou maior que 10 MB.');
            }
            $dir = __DIR__ . '/uploads/documentos';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $nomeArq = bin2hex(random_bytes(16)) . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', basename($arq['name']));
            if (!move_uploaded_file($arq['tmp_name'], $dir . DIRECTORY_SEPARATOR . $nomeArq)) {
                throw new Exception('Não foi possível salvar o arquivo.');
            }
            $stmt = $conexao->prepare(
                "INSERT INTO documentos (nome, tipo, caminho, Condominio_idCondominio) VALUES (:n, :t, :c, :cond)"
            );
            $stmt->execute(['n' => $nome, 't' => $tipo, 'c' => 'uploads/documentos/' . $nomeArq, 'cond' => $condominio]);
            $msg = 'Documento cadastrado com sucesso.';
        } elseif ($acao === 'excluir') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) throw new Exception('Documento inválido.');
            $stmt = $conexao->prepare("SELECT caminho FROM documentos WHERE idDocumento = :id");
            $stmt->execute(['id' => $id]);
            $doc = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($doc) {
                $stmt = $conexao->prepare("DELETE FROM documentos WHERE idDocumento = :id");
                $stmt->execute(['id' => $id]);
                if (!empty($doc['caminho']) && file_exists(__DIR__ . '/' . $doc['caminho'])) {
                    unlink(__DIR__ . '/' . $doc['caminho']);
                }
                $msg = 'Documento excluído.';
            }
        }
    } catch (Exception $e) {
        $erro = $e->getMessage();
    } catch (PDOException $e) {
        $erro = 'Erro no banco de dados.';
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
$pageTitle = 'Documentação';
$menuAtivo = 'documentacao';

// Lista de documentos
$documentos = [];
try {
    $sql = "SELECT d.idDocumento, d.nome, d.tipo, d.caminho,
                   DATE_FORMAT(d.dataUpload, '%d/%m/%Y') AS dataFmt,
                   c.nome AS condominio
            FROM documentos d
            JOIN condominio c ON c.idCondominio = d.Condominio_idCondominio
            ORDER BY d.dataUpload DESC";
    $documentos = $conexao->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $documentos = [];
}
foreach ($documentos as &$d) {
    $fs = (!empty($d['caminho']) && file_exists(__DIR__ . '/' . $d['caminho'])) ? filesize(__DIR__ . '/' . $d['caminho']) : null;
    $d['tamanho'] = $fs === null ? '—' : tamanhoHumano($fs);
    $d['temArquivo'] = $fs !== null;
}
unset($d);
$tipos = $conexao->query("SELECT DISTINCT tipo FROM documentos ORDER BY tipo")->fetchAll(PDO::FETCH_COLUMN);
$condominios = $conexao->query("SELECT idCondominio, nome FROM condominio ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentação - Eden Systems</title>
    <link rel="stylesheet" href="./CSS/dashboard.css">
    <link rel="stylesheet" href="./CSS/reset.css">
    <link rel="stylesheet" href="./CSS/FrontDev.css">
    <link rel="stylesheet" href="./CSS/tabelas.css">
    <script src="https://unpkg.com/lucide@latest"></script>
<?php include './Elements/favicon.php'; ?>
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include './Elements/sidebar.php'; ?>

        <div class="main-content">
            <?php include './Elements/header.php'; ?>

            <div class="dashboard-content">
                <div class="fd-documentacao">
                    <section class="intro">
                        <div class="intro-text">
                            <h1>Tela de Documentações</h1>
                        <p>Centraliza todos os documentos cadastrados no condomínio, organizados em cards para facilitar a consulta. Permite
                            buscar documentos, realizar o download dos arquivos e cadastrar novos documentos.</p>
                        </div>
                        <button class="new-document-button" onclick="abrirModal('newModal')"><i data-lucide="plus"></i>Novo Documento</button>
                    </section>
                    <?php if ($msg): ?>
                        <p style="width:100%;padding:8px 12px;border-radius:8px;background:#e9f7ee;color:#1e5c34;border:1px solid #bfe3cb;text-align:center;margin-bottom:16px"><?= htmlspecialchars($msg) ?></p>
                    <?php elseif ($erro): ?>
                        <p style="width:100%;padding:8px 12px;border-radius:8px;background:#fdecea;color:#8f1d1d;border:1px solid #f5c6c2;text-align:center;margin-bottom:16px"><?= htmlspecialchars($erro) ?></p>
                    <?php endif; ?>
                    <section class="documents-card">
                        <div class="card-header">
                            <div>
                                <h2>Documentos Cadastrados</h2>
                                <p><span id="totalDocuments"><?= count($documentos) ?></span> documento(s) cadastrado(s)</p>
                            </div>
                            <div class="card-actions">
                                <div class="search-box">
                                    <i data-lucide="search"></i>
                                    <input id="searchInput" type="text" placeholder="Pesquisar documento..." oninput="pesquisarDocumentos()">
                                </div>
                                <button class="filter-button" type="button" onclick="filtrarComArquivo()" title="Somente com arquivo"><i data-lucide="list-filter"></i></button>
                            </div>
                        </div>
                        <div class="table-container table-scroll">
                            <table class="issues-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th>Categoria</th>
                                        <th>Condomínio</th>
                                        <th>Tamanho</th>
                                        <th>Data</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="documentTable">
                                    <?php if (empty($documentos)): ?>
                                        <tr><td colspan="7"><div class="empty-state">Nenhum documento cadastrado.</div></td></tr>
                                    <?php else: ?>
                                        <?php foreach ($documentos as $d): ?>
                                        <tr data-arquivo="<?= $d['temArquivo'] ? '1' : '0' ?>">
                                            <td class="resident-id">#<?= (int) $d['idDocumento'] ?></td>
                                            <td><?= htmlspecialchars($d['nome']) ?></td>
                                            <td><?= htmlspecialchars($d['tipo']) ?></td>
                                            <td><?= htmlspecialchars($d['condominio']) ?></td>
                                            <td><?= htmlspecialchars($d['tamanho']) ?></td>
                                            <td><?= htmlspecialchars($d['dataFmt'] ?? '—') ?></td>
                                            <td>
                                                <div class="tbl-actions">
                                                    <?php if ($d['temArquivo']): ?>
                                                    <a class="tbl-action" href="./<?= htmlspecialchars($d['caminho']) ?>" download title="Baixar"><i data-lucide="download"></i></a>
                                                    <?php endif; ?>
                                                    <button type="button" class="tbl-action danger" onclick="excluirDocumento(<?= (int) $d['idDocumento'] ?>)" title="Excluir"><i data-lucide="trash-2"></i></button>
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

    <div class="fd-documentacao">
    <div class="modal-overlay" id="newModal" onclick="fdFecharClicandoFora(event, 'newModal')">
        <div class="modal">
            <div class="modal-header">
                <h2>Novo Documento</h2>
                <button type="button" onclick="fecharModal('newModal')"><i data-lucide="x"></i></button>
            </div>
            <form id="documentForm" class="document-form" method="post" enctype="multipart/form-data">
                <input type="hidden" name="acao" value="criar">
                <div class="form-group">
                    <label>Nome</label>
                    <input type="text" name="nome" placeholder="Ex.: Ata da Assembleia - Agosto" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Categoria</label>
                        <input type="text" name="tipo" list="tiposExistentes" placeholder="Ex.: Atas" required>
                        <datalist id="tiposExistentes">
                            <?php foreach ($tipos as $t): ?>
                            <option value="<?= htmlspecialchars($t) ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    <div class="form-group">
                        <label>Condomínio</label>
                        <select name="condominio" required>
                            <?php foreach ($condominios as $cc): ?>
                            <option value="<?= (int) $cc['idCondominio'] ?>"><?= htmlspecialchars($cc['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="file-section">
                    <input type="file" id="docInput" name="arquivo" hidden onchange="mostrarArquivo()">
                    <button type="button" class="attach-document" onclick="document.getElementById('docInput').click()">Anexar arquivo</button>
                    <span id="fileName"></span>
                </div>
                <div class="form-buttons">
                    <button type="button" class="cancel-button" onclick="fecharModal('newModal')">Cancelar</button>
                    <button type="submit" class="register-button">Cadastrar Documento</button>
                </div>
            </form>
        </div>
    </div>

    <form method="post" id="deleteForm" style="display:none">
        <input type="hidden" name="acao" value="excluir">
        <input type="hidden" name="id" id="deleteId" value="">
    </form>
    </div>

    <script src="./js/sindicoPages.js"></script>
    <script src="./js/FrontDev.js"></script>
    <script>
        lucide.createIcons();
        function pesquisarDocumentos() {
            filtrarLinhas('documentTable', document.getElementById('searchInput').value);
        }
        let soComArquivo = false;
        function filtrarComArquivo() {
            soComArquivo = !soComArquivo;
            document.getElementById('searchInput').value = '';
            document.querySelectorAll('#documentTable tr').forEach(tr => {
                const ok = !soComArquivo || tr.dataset.arquivo === '1';
                tr.classList.toggle('f-hide', !ok);
            });
            if (pagEstado['documentTable']) { pagEstado['documentTable'].pagina = 1; desenharPaginacao('documentTable'); }
        }
        function excluirDocumento(id) {
            document.getElementById('deleteId').value = id;
            if (confirm('Deseja realmente excluir o documento #' + id + '?')) {
                document.getElementById('deleteForm').submit();
            }
        }
        function mostrarArquivo() {
            const inp = document.getElementById('docInput');
            document.getElementById('fileName').textContent = inp.files.length ? inp.files[0].name : '';
        }
        paginar('documentTable', 'pager', 10);
    </script>
</body>
</html>
