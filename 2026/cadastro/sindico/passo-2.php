<?php
session_start();

// Não deixa acessar essa etapa sem ter completado a etapa 1
if (empty($_SESSION["cadastro"]["email"])) {
    header("Location: passo-1.php");
    exit;
}

$telefone = $_SESSION["cadastro"]["telefone"] ?? "";
$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $telefone = trim($_POST["telefone"]);
    $senha = $_POST["senha"];
    $confirmar_senha = $_POST["confirmar_senha"];

    if (empty($telefone) || empty($senha) || empty($confirmar_senha)) {
        $erro = "Preencha todos os campos!";
    } elseif (strlen(preg_replace('/\D/', '', $telefone)) < 10 || strlen(preg_replace('/\D/', '', $telefone)) > 11) {
        $erro = "Telefone inválido! Use DDD + número.";
    } elseif (strlen($senha) < 6) {
        $erro = "A senha deve ter pelo menos 6 caracteres!";
    } elseif ($senha !== $confirmar_senha) {
        $erro = "As senhas não conferem!";
    }

    if (empty($erro)) {
        $_SESSION["cadastro"]["telefone"] = $telefone;
        $_SESSION["cadastro"]["senha"] = $senha; // será criptografada só no INSERT final

        header("Location: passo-3.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Cadastro Síndico 2</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../CSS/CadastroSindico.css">
<?php include '../../Elements/favicon.php'; ?>
</head>
<body>
  <main class="panel-rigth">

    <form class="form " action="passo-2.php" method="post">

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
          <label for="telefone">Telefone</label>
          <input type="text" id="telefone" name="telefone" placeholder="(00) 00000-0000" maxlength="15" value="<?php echo htmlspecialchars($telefone); ?>" required>
        </div>

        <div class="field">
          <label for="senha">Senha</label>
          <input type="password" id="senha" name="senha" required>
        </div>

        <div class="field">
          <label for="confirmar_senha">Confirmar senha</label>
          <input type="password" id="confirmar_senha" name="confirmar_senha" required>
        </div>

      </section>

      <footer class="footer-form">
        <button type="submit">Próximo</button>
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
