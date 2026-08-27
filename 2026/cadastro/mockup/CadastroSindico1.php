<?php
session_start();

$nivel = $_GET["nivel"] ?? $_SESSION["nivel"] ?? "adm";
if (in_array($nivel, ["adm", "mor"], true)) {
  $_SESSION["nivel"] = $nivel;
}

$nome = $_SESSION["cadastro"]["nome"] ?? "";
$cpf = $_SESSION["cadastro"]["cpf"] ?? "";
$email = $_SESSION["cadastro"]["email"] ?? "";
$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include "../../config/conexao.php";

    $nome = trim($_POST["nome"]);
    $cpf = trim($_POST["cpf"]);
    $email = trim($_POST["email"]);

    if (empty($nome) || empty($cpf) || empty($email)) {
        $erro = "Preencha todos os campos!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "E-mail inválido!";
    } else {
        // Verifica se CPF ou e-mail já existem
        $stmt = $conexao->prepare("SELECT idUsuario FROM usuario WHERE CPF = :cpf OR email = :email");
        $stmt->execute(["cpf" => $cpf, "email" => $email]);

        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            $erro = "CPF ou e-mail já cadastrado!";
        }
    }

    if (empty($erro)) {
        $_SESSION["cadastro"]["nome"] = $nome;
        $_SESSION["cadastro"]["cpf"] = $cpf;
        $_SESSION["cadastro"]["email"] = $email;

        header("Location: CadastroSindico2.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Cadastro Síndico 1</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../CSS/CadastroSindico.css">
</head>
<body>
  <main class="panel-rigth">

    <form class="form" action="CadastroSindico1.php" method="post">

      <figure aria-label="Logo da empresa">
        <img src="../../assets/Logo.png" alt="Logo da empresa" class="logo-image">
      </figure>

      <header>
        <h1 class="title">Crie sua Conta</h1>
        <p class="subtitle">Insira suas Informações</p>
      </header>

      <?php if (!empty($erro)): ?>
        <p class="alerta-danger"><?php echo htmlspecialchars($erro); ?></p>
      <?php endif; ?>

      <section class="content">

        <div class="field">
          <label for="nome">Nome</label>
          <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($nome); ?>" required>
        </div>

        <div class="field">
          <label for="cpf">CPF</label>
          <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" value="<?php echo htmlspecialchars($cpf); ?>" required>
        </div>

        <div class="field">
          <label for="email">Email</label>
          <input type="text" id="email" name="email" placeholder="@gmail.com" value="<?php echo htmlspecialchars($email); ?>" required>
        </div>

      </section>

      <footer class="footer-form">
        <button type="submit">Próximo</button>
      </footer>

      <div class="divider" aria-hidden="true">
        <hr><span>ou</span><hr>
      </div>

      <p class="register">
        <a href="#">Já tem uma conta? <a href="../../Login/loginMockup/index.php">Faça seu login</a></a>
      </p>

    </form>

  </main>

  <aside class="panel-left" aria-label="Imagem ilustrativa"></aside>

</body>
</html>
