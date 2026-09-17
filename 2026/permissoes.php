<?php
session_start();
include './config/conexao.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ./auth/login.php');
    exit;
}

$idUsuario = $_SESSION['id_usuario'];
$msg = '';
$msg_erro = '';

// Condomínio de referência (último criado)
$idCondominio = null;
try {
    $idCondominio = $conexao->query("SELECT idCondominio FROM condominio ORDER BY idCondominio DESC LIMIT 1")->fetchColumn();
} catch (PDOException $e) {
    $idCondominio = null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    try {
        if ($acao === 'novo') {
            $nome = trim($_POST['nome'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $cpf = trim($_POST['cpf'] ?? '');
            $papel = trim($_POST['papel'] ?? '');
            $ativo = ($_POST['status'] ?? 'Ativo') === 'Ativo' ? 1 : 0;
            if ($nome !== '' && $email !== '' && $cpf !== '' && $papel !== '') {
                $stmt = $conexao->prepare(
                    "INSERT INTO usuario (email, senha, CPF, nome, ativo) VALUES (:email, :senha, :cpf, :nome, :ativo)"
                );
                $stmt->execute([
                    'email' => $email,
                    'senha' => password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT),
                    'cpf' => $cpf,
                    'nome' => $nome,
                    'ativo' => $ativo,
                ]);
                $idNovoUsuario = (int) $conexao->lastInsertId();
                $stmt = $conexao->prepare(
                    "INSERT INTO funcionario (idUsuario, funcao, tipoVinculo) VALUES (:u, :f, 'CLT')"
                );
                $stmt->execute(['u' => $idNovoUsuario, 'f' => $papel]);
                $idFunc = (int) $conexao->lastInsertId();
                if ($idCondominio) {
                    $stmt = $conexao->prepare(
                        "INSERT INTO funcionariocondominio (Funcionario_idFuncionario, Condominio_idCondominio, dataAdmissao)
                         VALUES (:f, :c, CURDATE())"
                    );
                    $stmt->execute(['f' => $idFunc, 'c' => $idCondominio]);
                }
                $msg = 'Funcionário registrado com sucesso.';
            }
        } elseif ($acao === 'editar') {
            $idFunc = (int) ($_POST['id'] ?? 0);
            if ($idFunc > 0) {
                $stmt = $conexao->prepare("SELECT idUsuario FROM funcionario WHERE idFuncionario = :id");
                $stmt->execute(['id' => $idFunc]);
                $idFuncUsuario = $stmt->fetchColumn();
                if ($idFuncUsuario) {
                    $stmt = $conexao->prepare(
                        "UPDATE usuario SET nome = :nome, email = :email, ativo = :ativo WHERE idUsuario = :id"
                    );
                    $stmt->execute([
                        'nome' => trim($_POST['nome'] ?? ''),
                        'email' => trim($_POST['email'] ?? ''),
                        'ativo' => ($_POST['status'] ?? 'Ativo') === 'Ativo' ? 1 : 0,
                        'id' => $idFuncUsuario,
                    ]);
                    $stmt = $conexao->prepare("UPDATE funcionario SET funcao = :f WHERE idFuncionario = :id");
                    $stmt->execute(['f' => trim($_POST['papel'] ?? ''), 'id' => $idFunc]);
                    $msg = 'Funcionário atualizado com sucesso.';
                }
            }
        }
    } catch (PDOException $e) {
        $msg_erro = 'Não foi possível salvar. Verifique e-mail/CPF duplicados e tente novamente.';
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
$pageTitle = 'Permissões';
$menuAtivo = 'permissoes';

// Lista de funcionários
$funcionarios = [];
try {
    $funcionarios = $conexao->query(
        "SELECT f.idFuncionario, u.nome, u.email, u.ativo, f.funcao
         FROM funcionario f
         JOIN usuario u ON u.idUsuario = f.idUsuario
         ORDER BY u.nome"
    )->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $funcionarios = [];
}

function classe_papel($papel) {
    return [
        'Síndico' => 'role-sindico',
        'Administrador' => 'role-administrador',
        'Porteiro' => 'role-porteiro',
        'Manutenção' => 'role-manutencao',
    ][$papel] ?? '';
}

function iniciais($nome) {
    $partes = preg_split('/\s+/', trim($nome));
    if (count($partes) === 1) return mb_strtoupper(mb_substr($partes[0], 0, 2));
    return mb_strtoupper(mb_substr($partes[0], 0, 1) . mb_substr(end($partes), 0, 1));
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permissões - Eden Systems</title>
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
                <div class="fd-permissoes">
                    <section class="page-title">
                        <h1>Permissões</h1>
                        <p>Gestão de funcionários e controle de acesso</p>
                    </section>

                    <section class="card users-card">
                        <div class="section-header">
                            <h2>Funcionários do Sistema</h2>
                            <button class="new-user-button" type="button" onclick="fdAbrirModal('employeeModal')">
                                <i data-lucide="plus"></i>
                                Novo Funcionário
                            </button>
                        </div>
                        <div class="table-container">
                            <table class="issues-table">
                                <thead>
                                    <tr>
                                        <th>FUNCIONÁRIO</th>
                                        <th>PAPEL</th>
                                        <th>ÚLTIMO ACESSO</th>
                                        <th>STATUS</th>
                                        <th>AÇÕES</th>
                                    </tr>
                                </thead>
                                <tbody id="usersTable">
                                    <?php if (empty($funcionarios)): ?>
                                    <tr><td colspan="5" style="text-align:center;padding:24px;color:#819087">Nenhum funcionário cadastrado.</td></tr>
                                    <?php else: ?>
                                    <?php foreach ($funcionarios as $f): ?>
                                    <?php $ativo = ((int) $f['ativo']) === 1; ?>
                                    <tr>
                                        <td>
                                            <div class="employee">
                                                <div class="employee-avatar"><?= htmlspecialchars(iniciais($f['nome'])) ?></div>
                                                <div class="employee-data">
                                                    <strong><?= htmlspecialchars($f['nome']) ?></strong>
                                                    <small><?= htmlspecialchars($f['email']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="role <?= classe_papel($f['funcao']) ?>"><?= htmlspecialchars($f['funcao']) ?></span></td>
                                        <td>—</td>
                                        <td><span class="badge <?= $ativo ? 'ativo' : 'inativo' ?>"><?= $ativo ? 'Ativo' : 'Inativo' ?></span></td>
                                        <td>
                                            <button type="button" class="tbl-action" title="Editar" onclick='fdEditarFuncionario(<?= json_encode($f, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'><i data-lucide="pencil"></i></button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="card permissions-card">
                        <div class="permissions-title">
                            <h2>Matriz de Permissões</h2>
                            <p>Acesso por módulo e papel do funcionário</p>
                        </div>
                        <div class="table-container">
                            <table class="permissions-table">
                                <thead>
                                    <tr>
                                        <th>MÓDULO</th>
                                        <th>SÍNDICO</th>
                                        <th>ADMINISTRADOR</th>
                                        <th>PORTEIRO</th>
                                        <th>MANUTENÇÃO</th>
                                    </tr>
                                </thead>
                                <tbody id="permissionsTable"></tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Novo Funcionário -->
    <div class="fd-permissoes">
        <div class="modal-overlay" id="employeeModal" onclick="fdFecharClicandoFora(event, 'employeeModal')">
            <div class="modal">
                <div class="modal-header">
                    <h2>Novo Funcionário</h2>
                    <button type="button" onclick="fdFecharModal('employeeModal')"><i data-lucide="x"></i></button>
                </div>
                <form method="post" class="employee-form">
                    <input type="hidden" name="acao" value="novo">
                    <div class="form-group">
                        <label>Nome do Funcionário</label>
                        <input type="text" name="nome" placeholder="Digite o nome completo" required>
                    </div>
                    <div class="form-group">
                        <label>E-mail</label>
                        <input type="email" name="email" placeholder="Digite o e-mail" required>
                    </div>
                    <div class="form-group">
                        <label>CPF</label>
                        <input type="text" name="cpf" class="cpf-mask" placeholder="000.000.000-00" maxlength="14" inputmode="numeric" required>
                    </div>
                    <div class="form-group">
                        <label>Papel</label>
                        <select name="papel" required>
                            <option value="">Selecione</option>
                            <option value="Síndico">Síndico</option>
                            <option value="Administrador">Administrador</option>
                            <option value="Porteiro">Porteiro</option>
                            <option value="Manutenção">Manutenção</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" required>
                            <option value="Ativo">Ativo</option>
                            <option value="Inativo">Inativo</option>
                        </select>
                    </div>
                    <div class="form-buttons">
                        <button type="button" class="cancel-button" onclick="fdFecharModal('employeeModal')">Cancelar</button>
                        <button type="submit" class="register-button">Registrar Funcionário</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Funcionário -->
    <div class="fd-permissoes">
        <div class="modal-overlay" id="editModal" onclick="fdFecharClicandoFora(event, 'editModal')">
            <div class="modal edit-modal">
                <div class="modal-header">
                    <h2>Editar Funcionário</h2>
                    <button type="button" onclick="fdFecharModal('editModal')"><i data-lucide="x"></i></button>
                </div>
                <form method="post" class="employee-form">
                    <input type="hidden" name="acao" value="editar">
                    <input type="hidden" name="id" id="editEmployeeId">
                    <div class="form-group">
                        <label>Nome do Funcionário</label>
                        <input type="text" name="nome" id="editEmployeeName" required>
                    </div>
                    <div class="form-group">
                        <label>E-mail</label>
                        <input type="email" name="email" id="editEmployeeEmail" required>
                    </div>
                    <div class="form-group">
                        <label>Papel</label>
                        <select name="papel" id="editEmployeeRole" required>
                            <option value="Síndico">Síndico</option>
                            <option value="Administrador">Administrador</option>
                            <option value="Porteiro">Porteiro</option>
                            <option value="Manutenção">Manutenção</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" id="editEmployeeStatus" required>
                            <option value="Ativo">Ativo</option>
                            <option value="Inativo">Inativo</option>
                        </select>
                    </div>
                    <div class="form-buttons">
                        <button type="button" class="cancel-button" onclick="fdFecharModal('editModal')">Cancelar</button>
                        <button type="submit" class="register-button">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="fdToast" class="fd-toast" role="status"></div>

    <script src="./js/FrontDev.js"></script>
    <script src="./js/mascaras.js"></script>
    <?php if ($msg): ?>
    <script>document.addEventListener('DOMContentLoaded', () => fdToast(<?= json_encode($msg, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>));</script>
    <?php elseif ($msg_erro): ?>
    <script>document.addEventListener('DOMContentLoaded', () => fdToast(<?= json_encode($msg_erro, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>));</script>
    <?php endif; ?>
</body>
</html>
