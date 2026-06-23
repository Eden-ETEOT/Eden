<?php
session_start();
require_once "../../config/conexao.php";

$msg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["usuario"]);
    $senha = $_POST["senha"];
    
        try {
            $sql = "SELECT * FROM usuario WHERE email = :email";
            $stmt = $conexao->prepare($sql);
            $stmt->execute(["email" => $email]);

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario && password_verify($senha, $usuario["senha"])) {
                $_SESSION["id_usuario"] = $usuario["idUsuario"];
                header("Location: /GitHub/Eden/2026/landing-page/index.html");
                exit();

            } else {
                $msg = 'Email ou senha inválidos.';
            }
        } catch (PDOException $e) {
            $msg = 'Erro de banco de dados: ' . $e->getMessage();
        }
    }
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../CSS/LoginMockup.css">
  <link rel="stylesheet" href="../../css/root.css">
</head>
<body>

  <figure class="panel-left">
    <img src="../../assets/imageLogin.png" alt="Ilustração de login" class="panel-left-image">
  </figure>

  <main class="panel-right">

    <form action="" method="POST" class="form">

      <figure aria-label="Logo da empresa">
        <img src="../../assets/logo.png" alt="Logo da empresa" class="logo-image">
      </figure>

      <header>
        <h1 class="title">Bem-vindo de volta</h1>
        <p class="subtitle">Acesse sua conta para continuar</p>
      </header>


      <section class="content">

        <div class="field">
          <label for="usuario">E-mail</label>
          <input
            type="email"
            id="usuario"
            name="usuario"
            placeholder="Digite seu e-mail"
            required
          >
        </div>

        <div class="field">
          <label for="senha">Senha</label>
          <input
            type="password"
            id="senha"
            name="senha"
            placeholder="Digite sua senha"
            required
          >
        </div>

      </section>

      <footer class="footer-form">

        <div class="row">
          <label>
            <input type="checkbox" name="lembrar">
            Lembrar login
          </label>

          <a href="#">Esqueceu a senha?</a>
        </div>

        <button type="submit" class="btn-primary-orange-small">
          entrar
        </button>

      </footer>

      <div class="divider" aria-hidden="true">
        <hr>
        <span>ou</span>
        <hr>
      </div>

      <p class="register">
        <span>Não tem uma conta? <a href="../../cadastro/create.php">Crie agora</a></span>
      </p>

    </form>

  </main>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const emailInput = document.querySelector('input[name="usuario"]');
      const passwordInput = document.querySelector('input[name="senha"]');
      const rememberCheckbox = document.querySelector('input[name="lembrar"]');
      const form = document.querySelector('form');

      const saved = localStorage.getItem("edenLoginRemember");
      if (saved) {
        try {
          const data = JSON.parse(saved);
          if (data.email) emailInput.value = data.email;
          if (data.password) passwordInput.value = data.password;
          rememberCheckbox.checked = true;
        } catch (e) {
          localStorage.removeItem("edenLoginRemember");
        }
      }

      form.addEventListener("submit", function() {
        if (rememberCheckbox.checked) {
          localStorage.setItem("edenLoginRemember", JSON.stringify({
            email: emailInput.value,
            password: passwordInput.value
          }));
        } else {
          localStorage.removeItem("edenLoginRemember");
        }
      });
    });
  </script>

</body>
</html>
