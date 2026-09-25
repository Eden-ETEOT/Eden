<?php
session_start();

if (empty($_SESSION["condominio"]["uf"])) {
    header("Location: passo-4.php");
    exit;
}

$telefone_condominio = "";
$email_condominio = "";
$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include "../../config/conexao.php";

    $telefone_condominio = trim($_POST["telefone"]);
    $email_condominio = trim($_POST["email"]);

    if (empty($telefone_condominio) || empty($email_condominio)) {
        $erro = "Preencha todos os campos!";
    } elseif (!filter_var($email_condominio, FILTER_VALIDATE_EMAIL)) {
        $erro = "E-mail do condomínio inválido!";
    } elseif (strlen(preg_replace('/\D/', '', $telefone_condominio)) < 10 || strlen(preg_replace('/\D/', '', $telefone_condominio)) > 11) {
        $erro = "Telefone inválido! Use DDD + número.";
    }

    if (empty($erro)) {
        // Dados do síndico (etapas 1 e 2)
        $nome = $_SESSION["cadastro"]["nome"];
        $cpf = $_SESSION["cadastro"]["cpf"];
        $email = $_SESSION["cadastro"]["email"];
        $telefone = $_SESSION["cadastro"]["telefone"];
        $senha = $_SESSION["cadastro"]["senha"];
        $foto_usuario = $_SESSION["cadastro"]["foto"] ?? null;

        // Dados do condomínio (etapas 3 e 4)
        $cnpj = $_SESSION["condominio"]["cnpj"];
        $nome_condominio = $_SESSION["condominio"]["nome"];
        $cep = $_SESSION["condominio"]["cep"];
        $logradouro = $_SESSION["condominio"]["logradouro"];
        $numero = $_SESSION["condominio"]["numero"];
        $bairro = $_SESSION["condominio"]["bairro"];
        $cidade = $_SESSION["condominio"]["cidade"];
        $uf = $_SESSION["condominio"]["uf"];
        $foto = $_SESSION["condominio"]["foto"] ?? null;

        // Verifica novamente se CPF/e-mail já existem (evita duplicidade em caso de dupla submissão)
        $stmt = $conexao->prepare("SELECT idUsuario FROM usuario WHERE CPF = :cpf OR email = :email");
        $stmt->execute(["cpf" => $cpf, "email" => $email]);

        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            $erro = "CPF ou e-mail já cadastrado!";
        } else {
            $senhaCriptografada = password_hash($senha, PASSWORD_DEFAULT);

            try {
                $conexao->beginTransaction();

                $stmt = $conexao->prepare(
                  "INSERT INTO usuario (nome, CPF, email, telefone, senha, foto)
                   VALUES (:nome, :cpf, :email, :telefone, :senha, :foto)"
                );
                $stmt->execute([
                    "nome" => $nome,
                    "cpf" => $cpf,
                    "email" => $email,
                    "telefone" => $telefone,
                    "senha" => $senhaCriptografada,
                    "foto" => $foto_usuario,
                ]);

                $usuario_id = $conexao->lastInsertId();

                $plano = $conexao->query("SELECT idPlano FROM plano WHERE ativo = 1 ORDER BY idPlano LIMIT 1")->fetchColumn();
                if (!$plano) {
                  throw new RuntimeException("Nenhum plano ativo cadastrado.");
                }

                $stmt = $conexao->prepare(
                  "INSERT INTO condominio
                   (CNPJ, nome, foto, CEP, logradouro, numero, bairro, cidade, UF, telefone, email, Plano_idPlano)
                     VALUES
                   (:cnpj, :nome, :foto, :cep, :logradouro, :numero, :bairro, :cidade, :uf, :telefone, :email, :plano)"
                );
                $stmt->execute([
                    "cnpj" => $cnpj,
                    "nome" => $nome_condominio,
                    "foto" => $foto,
                    "cep" => $cep,
                    "logradouro" => $logradouro,
                    "numero" => $numero,
                    "bairro" => $bairro,
                    "cidade" => $cidade,
                    "uf" => $uf,
                    "telefone" => $telefone_condominio,
                    "email" => $email_condominio,
                    "plano" => $plano
                ]);

                $condominio_id = $conexao->lastInsertId();

                $stmt = $conexao->prepare(
                    "INSERT INTO sindico (idUsuario, Condominio_idCondominio) VALUES (:idUsuario, :idCondominio)"
                );
                $stmt->execute([
                    "idUsuario" => $usuario_id,
                    "idCondominio" => $condominio_id,
                ]);

                $conexao->commit();

                // Limpa os dados temporários do cadastro
                unset($_SESSION["cadastro"]);
                unset($_SESSION["condominio"]);

                // Login automático: cria a sessão do usuário logado
                session_regenerate_id(true);
                $_SESSION["id_usuario"] = $usuario_id;
                $_SESSION["usuario_nome"] = $nome;
                $_SESSION["usuario_email"] = $email;
                $_SESSION["logado"] = true;

                header("Location: ../../dashboard.php?tipo=success&msg=" . urlencode("Cadastro realizado com sucesso!"));
                exit;

            } catch (Throwable $e) {
                if ($conexao->inTransaction()) {
                    $conexao->rollBack();
                }
                $erro = "Erro ao cadastrar: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Cadastro Síndico 5</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../CSS/CadastroSindico.css">
<?php include '../../Elements/favicon.php'; ?>
</head>
<body>
  <main class="panel-rigth">

    <form class="form" action="passo-5.php" method="post">

      <figure aria-label="Logo da empresa">
        <img src="../../assets/Logo.png" alt="Logo da empresa" class="logo-image">
      </figure>

      <header>
        <h1 class="title">Crie sua Conta</h1>
        <p class="subtitle">Contato do condomínio</p>
      </header>

      <?php if (!empty($erro)): ?>
        <p class="alerta-danger"><?php echo htmlspecialchars($erro); ?></p>
      <?php endif; ?>

      <section class="content">

        <div class="field">
          <label for="telefone">Telefone</label>
          <input type="text" id="telefone" name="telefone" placeholder="(00) 00000-0000" maxlength="15" value="<?php echo htmlspecialchars($telefone_condominio); ?>" required>
        </div>

        <div class="field">
          <label for="email">Email</label>
          <input type="text" id="email" name="email" value="<?php echo htmlspecialchars($email_condominio); ?>" required>
        </div>

      </section>

      <footer class="footer-form">
        <button type="submit">Confirmar</button>
      </footer>

      <div class="divider" aria-hidden="true">
        <hr><span>ou</span><hr>
      </div>

      <p class="register">
        <a href="#">Já tem uma conta? <a href="../../auth/login.php">Faça seu login</a></a>
      </p>

    </form>

  </main>

  <aside class="panel-left" aria-label="Imagem ilustrativa"></aside>

  <script src="../../js/mascaras.js"></script>
</body>
</html>
