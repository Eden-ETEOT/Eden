<?php
session_start();

if (empty($_SESSION["cadastro"]["nome"]) || empty($_SESSION["cadastro"]["cpf"])) {
    header("Location: passo-1.php");
    exit;
}

$nome = $_SESSION["cadastro"]["nome"];
$cpf = $_SESSION["cadastro"]["cpf"];
$foto = $_SESSION["cadastro"]["foto"] ?? null;

$email = "";
$telefone = "";
$senha = "";
$confirmarSenha = "";
$tipoMorador = "proprietario";
$dataNascimento = "";
$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include "../../config/conexao.php";

    $email = trim($_POST["email"]);
    $telefone = trim($_POST["telefone"]);
    $senha = $_POST["senha"];
    $confirmarSenha = $_POST["confirmar_Senha"];
    $tipoMorador = $_POST["tipo_morador"] ?? "proprietario";
    $dataNascimento = trim($_POST["data_nascimento"]);

    if (empty($email) || empty($telefone) || empty($senha) || empty($confirmarSenha) || empty($dataNascimento)) {
        $erro = "Preencha todos os campos!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "E-mail inválido!";
    } elseif (strlen($senha) < 6) {
        $erro = "A senha deve ter pelo menos 6 caracteres.";
    } elseif ($senha !== $confirmarSenha) {
        $erro = "As senhas não coincidem!";
    } elseif (!in_array($tipoMorador, ["proprietario", "inquilino", "dependente"], true)) {
        $erro = "Tipo de morador inválido.";
    } elseif (!strtotime($dataNascimento)) {
        $erro = "Data de nascimento inválida.";
    } else {
        $stmt = $conexao->prepare("SELECT idUsuario FROM usuario WHERE email = :email OR CPF = :cpf");
        $stmt->execute(["email" => $email, "cpf" => $cpf]);

        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            $erro = "CPF ou e-mail já cadastrado!";
        }
    }

    if (empty($erro)) {
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
                "foto" => $foto,
            ]);

            $usuario_id = $conexao->lastInsertId();

            $stmt = $conexao->prepare(
                "INSERT INTO morador (idUsuario, tipoMorador, dataNascimento)
                 VALUES (:idUsuario, :tipoMorador, :dataNascimento)"
            );
            $stmt->execute([
                "idUsuario" => $usuario_id,
                "tipoMorador" => $tipoMorador,
                "dataNascimento" => $dataNascimento,
            ]);

            $conexao->commit();

            unset($_SESSION["cadastro"]);

            session_regenerate_id(true);
            $_SESSION["id_usuario"] = $usuario_id;
            $_SESSION["usuario_nome"] = $nome;
            $_SESSION["usuario_email"] = $email;
            $_SESSION["logado"] = true;

            header("Location: ../../dashboard.php?tipo=success&msg=" . urlencode("Cadastro de morador realizado com sucesso!"));
            exit;

        } catch (Throwable $e) {
            if ($conexao->inTransaction()) {
                $conexao->rollBack();
            }
            $erro = "Erro ao cadastrar: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Cadastro Morador 2</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../CSS/CadastroMoradorMockup.css">
</head>
<body>
  <main class="panel-left">

    <form class="form" action="passo-2.php" method="post">

      <figure aria-label="Logo da empresa">
        <img src="../../assets/Logo.png" alt="Logo da empresa" class="logo-image">
      </figure>

      <header>
        <h1 class="title">Crie sua Conta</h1>
        <p class="subtitle">Insira suas informações</p>
      </header>

      <?php if (!empty($erro)): ?>
        <p class="alerta-danger"><?php echo htmlspecialchars($erro); ?></p>
      <?php endif; ?>

      <section class="content">

        <div class="field">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" placeholder="@gmail.com" value="<?php echo htmlspecialchars($email); ?>" required>
        </div>

        <div class="field">
          <label for="telefone">Telefone</label>
          <input type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars($telefone); ?>" required>
        </div>

        <div class="field">
          <label for="tipo_morador">Tipo de morador</label>
          <select id="tipo_morador" name="tipo_morador" required>
            <option value="proprietario" <?php echo $tipoMorador === "proprietario" ? "selected" : ""; ?>>Proprietário</option>
            <option value="inquilino" <?php echo $tipoMorador === "inquilino" ? "selected" : ""; ?>>Inquilino</option>
            <option value="dependente" <?php echo $tipoMorador === "dependente" ? "selected" : ""; ?>>Dependente</option>
          </select>
        </div>

        <div class="field">
          <label for="data_nascimento">Data de nascimento</label>
          <input type="date" id="data_nascimento" name="data_nascimento" value="<?php echo htmlspecialchars($dataNascimento); ?>" required>
        </div>

        <div class="field">
          <label for="senha">Senha</label>
          <input type="password" id="senha" name="senha" required>
        </div>

        <div class="field">
          <label for="confirmar_Senha">Confirmar senha</label>
          <input type="password" id="confirmar_Senha" name="confirmar_Senha" required>
        </div>

      </section>

      <footer class="footer-form">
        <button type="submit">Cadastrar</button>
      </footer>

      <div class="divider" aria-hidden="true">
        <hr><span>ou</span><hr>
      </div>

      <p class="register">
        <a href="#">Já tem uma conta? <a href="../../auth/login.php">Faça seu login</a></a>
      </p>

    </form>

  </main>

  <aside class="panel-rigth" aria-label="Imagem ilustrativa"></aside>

</body>
</html>
