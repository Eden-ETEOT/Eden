<?php
include './Elements/auth.php';
include './Elements/ui.php';

if ($idCondominio) {
    header('Location: ./ocorrencias.php');
    exit;
}

if (empty($_SESSION['csrf_condominio'])) {
    $_SESSION['csrf_condominio'] = bin2hex(random_bytes(32));
}

$erro = '';
$planos = [];
$chamadosPendentes = [];

try {
    $planos = $conexao->query("SELECT idPlano, nome FROM plano WHERE ativo = 1 ORDER BY idPlano")->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $conexao->prepare(
        "SELECT c.idChamados, c.titulo, c.dataPedida
         FROM chamados c
         JOIN morador m ON m.idMorador = c.morador_idMorador
         WHERE m.idUsuario = :usuario AND c.Condominio_idCondominio IS NULL
         ORDER BY c.dataPedida DESC"
    );
    $stmt->execute(['usuario' => $idUsuario]);
    $chamadosPendentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $erro = 'Não foi possível carregar os dados necessários para configurar o condomínio.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!hash_equals($_SESSION['csrf_condominio'], $_POST['csrf'] ?? '')) {
            throw new Exception('A sessão expirou. Atualize a página e tente novamente.');
        }
        if (resolverCondominio($conexao, (int) $idUsuario) !== null) {
            throw new Exception('Sua conta já está associada a um condomínio. Saia e entre novamente.');
        }

        $nome = trim($_POST['nome'] ?? '');
        $cnpjDigitos = preg_replace('/\D/', '', $_POST['cnpj'] ?? '');
        $cepDigitos = preg_replace('/\D/', '', $_POST['cep'] ?? '');
        $logradouro = trim($_POST['logradouro'] ?? '');
        $numero = filter_var($_POST['numero'] ?? null, FILTER_VALIDATE_INT);
        $bairro = trim($_POST['bairro'] ?? '');
        $cidade = trim($_POST['cidade'] ?? '');
        $uf = strtoupper(trim($_POST['uf'] ?? ''));
        $telefone = preg_replace('/\D/', '', $_POST['telefone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $planoId = (int) ($_POST['plano'] ?? 0);
        $chamadosEnviados = $_POST['chamados'] ?? [];

        if (!is_array($chamadosEnviados)) {
            throw new Exception('Seleção de chamados inválida.');
        }
        $chamadoIds = array_values(array_unique(array_filter(
            array_map('intval', $chamadosEnviados),
            static function ($id) { return $id > 0; }
        )));

        if ($nome === '' || strlen($cnpjDigitos) !== 14 || strlen($cepDigitos) !== 8
            || $logradouro === '' || $numero === false || $numero < 0 || $bairro === ''
            || $cidade === '' || !preg_match('/^[A-Z]{2}$/', $uf) || $planoId <= 0) {
            throw new Exception('Preencha corretamente os dados obrigatórios do condomínio.');
        }
        if ($telefone !== '' && (strlen($telefone) < 10 || strlen($telefone) > 15)) {
            throw new Exception('Informe um telefone válido com DDD.');
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Informe um e-mail válido.');
        }

        $planoStmt = $conexao->prepare("SELECT idPlano FROM plano WHERE idPlano = :id AND ativo = 1");
        $planoStmt->execute(['id' => $planoId]);
        if (!$planoStmt->fetchColumn()) {
            throw new Exception('Selecione um plano ativo.');
        }

        if ($chamadoIds) {
            $placeholders = implode(',', array_fill(0, count($chamadoIds), '?'));
            $sql = "SELECT c.idChamados
                    FROM chamados c
                    JOIN morador m ON m.idMorador = c.morador_idMorador
                    WHERE m.idUsuario = ? AND c.Condominio_idCondominio IS NULL
                      AND c.idChamados IN ($placeholders)";
            $stmt = $conexao->prepare($sql);
            $stmt->execute(array_merge([(int) $idUsuario], $chamadoIds));
            if (count($stmt->fetchAll(PDO::FETCH_COLUMN)) !== count($chamadoIds)) {
                throw new Exception('Um ou mais chamados selecionados não pertencem à sua conta ou já foram vinculados.');
            }
        }

        $cnpj = substr($cnpjDigitos, 0, 2) . '.' . substr($cnpjDigitos, 2, 3) . '.'
            . substr($cnpjDigitos, 5, 3) . '/' . substr($cnpjDigitos, 8, 4) . '-' . substr($cnpjDigitos, 12, 2);
        $cep = substr($cepDigitos, 0, 5) . '-' . substr($cepDigitos, 5, 3);

        $conexao->beginTransaction();
        $stmt = $conexao->prepare(
            "INSERT INTO condominio (CNPJ, nome, logradouro, numero, bairro, cidade, UF, CEP, telefone, email, Plano_idPlano)
             VALUES (:cnpj, :nome, :logradouro, :numero, :bairro, :cidade, :uf, :cep, :telefone, :email, :plano)"
        );
        $stmt->execute([
            'cnpj' => $cnpj,
            'nome' => $nome,
            'logradouro' => $logradouro,
            'numero' => $numero,
            'bairro' => $bairro,
            'cidade' => $cidade,
            'uf' => $uf,
            'cep' => $cep,
            'telefone' => $telefone !== '' ? $telefone : null,
            'email' => $email !== '' ? $email : null,
            'plano' => $planoId,
        ]);
        $novoCondominio = (int) $conexao->lastInsertId();

        $stmt = $conexao->prepare("INSERT INTO sindico (idUsuario, Condominio_idCondominio) VALUES (:usuario, :condominio)");
        $stmt->execute(['usuario' => $idUsuario, 'condominio' => $novoCondominio]);

        if ($chamadoIds) {
            $idPlaceholders = [];
            $params = ['condominio' => $novoCondominio, 'usuario' => $idUsuario];
            foreach ($chamadoIds as $index => $chamadoId) {
                $key = 'chamado' . $index;
                $idPlaceholders[] = ':' . $key;
                $params[$key] = $chamadoId;
            }
            $stmt = $conexao->prepare(
                "UPDATE chamados c
                 JOIN morador m ON m.idMorador = c.morador_idMorador
                 SET c.Condominio_idCondominio = :condominio
                 WHERE m.idUsuario = :usuario AND c.Condominio_idCondominio IS NULL
                   AND c.idChamados IN (" . implode(',', $idPlaceholders) . ')'
            );
            $stmt->execute($params);
            if ($stmt->rowCount() !== count($chamadoIds)) {
                throw new Exception('Não foi possível vincular todos os chamados selecionados.');
            }
        }

        $conexao->commit();
        $_SESSION['id_condominio'] = $novoCondominio;
        $_SESSION['flash_msg'] = 'Condomínio criado e conta vinculada como síndico.';
        header('Location: ./ocorrencias.php');
        exit;
    } catch (Throwable $e) {
        if ($conexao->inTransaction()) {
            $conexao->rollBack();
        }
        $erro = $e instanceof PDOException
            ? 'Não foi possível criar o condomínio. Verifique se o CNPJ já está cadastrado.'
            : $e->getMessage();
    }
}

$pageTitle = 'Configurar condomínio';
$menuAtivo = 'apartamentos';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<?php pageHead('Configurar condomínio - Eden Systems', ['./CSS/FrontDev.css', './CSS/variaveis.css'], ['https://unpkg.com/lucide@latest']); ?>
<body>
    <?php layoutOpen(); ?>
    <section class="fd-apartamentos">
        <div class="top-content">
            <div>
                <h1>Configurar condomínio</h1>
                <p>Cadastre o condomínio e vincule sua conta existente como síndico.</p>
            </div>
        </div>
        <?php banner('', $erro); ?>
        <?php if (!$planos): ?>
            <?php banner('', 'Não há plano ativo para vincular ao condomínio.'); ?>
        <?php else: ?>
        <form method="post" class="modal-form" style="max-width:760px">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf_condominio']) ?>">
            <div class="form-row">
                <div class="form-group">
                    <label for="condNome">Nome do condomínio</label>
                    <input id="condNome" class="input-field-default-m" name="nome" maxlength="100" required>
                </div>
                <div class="form-group">
                    <label for="condCnpj">CNPJ</label>
                    <input id="condCnpj" class="input-field-default-m" name="cnpj" inputmode="numeric" maxlength="18" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="condLogradouro">Logradouro</label>
                    <input id="condLogradouro" class="input-field-default-m" name="logradouro" maxlength="100" required>
                </div>
                <div class="form-group">
                    <label for="condNumero">Número</label>
                    <input id="condNumero" class="input-field-default-m" type="number" name="numero" min="0" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="condBairro">Bairro</label>
                    <input id="condBairro" class="input-field-default-m" name="bairro" maxlength="100" required>
                </div>
                <div class="form-group">
                    <label for="condCidade">Cidade</label>
                    <input id="condCidade" class="input-field-default-m" name="cidade" maxlength="100" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="condUf">UF</label>
                    <input id="condUf" class="input-field-default-m" name="uf" maxlength="2" required>
                </div>
                <div class="form-group">
                    <label for="condCep">CEP</label>
                    <input id="condCep" class="input-field-default-m" name="cep" inputmode="numeric" maxlength="9" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="condTelefone">Telefone</label>
                    <input id="condTelefone" class="input-field-default-m" name="telefone" inputmode="tel" maxlength="15">
                </div>
                <div class="form-group">
                    <label for="condEmail">E-mail</label>
                    <input id="condEmail" class="input-field-default-m" type="email" name="email" maxlength="100">
                </div>
            </div>
            <div class="form-group">
                <label for="condPlano">Plano</label>
                <select id="condPlano" name="plano" class="select-medium-iconR" required>
                    <option value="">Selecione</option>
                    <?php foreach ($planos as $plano): ?>
                    <option value="<?= (int) $plano['idPlano'] ?>"><?= htmlspecialchars($plano['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($chamadosPendentes): ?>
            <fieldset class="form-group">
                <legend>Vincular chamados antigos criados por esta conta</legend>
                <?php foreach ($chamadosPendentes as $chamado): ?>
                <label style="display:block;margin:8px 0">
                    <input type="checkbox" name="chamados[]" value="<?= (int) $chamado['idChamados'] ?>" checked>
                    #<?= str_pad((int) $chamado['idChamados'], 3, '0', STR_PAD_LEFT) ?>
                    <?= htmlspecialchars($chamado['titulo']) ?>
                    (<?= htmlspecialchars($chamado['dataPedida']) ?>)
                </label>
                <?php endforeach; ?>
                <p>Os chamados desta conta estão selecionados por padrão. Desmarque os que não devem ser associados ao novo condomínio.</p>
            </fieldset>
            <?php endif; ?>
            <div class="form-buttons">
                <a class="cancel-button" href="./ocorrencias.php">Cancelar</a>
                <button class="register-button" type="submit">Criar condomínio</button>
            </div>
        </form>
        <?php endif; ?>
    </section>
    </div></div></div>
    <script>if (window.lucide) lucide.createIcons();</script>
</body>
</html>