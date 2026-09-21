<?php
session_start();
require_once "../config/conexao.php";

if (empty($_SESSION['reset_verified']) || empty($_SESSION['reset_id_usuario']) || empty($_SESSION['reset_id_token'])) {
    header("Location: recuperar-senha.php");
    exit();
}

$msg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $senha = $_POST["password"] ?? '';
    $confirmar = $_POST["confirm-password"] ?? '';

    if (strlen($senha) < 8) {
        $msg = 'A senha precisa ter pelo menos 8 caracteres.';
    } elseif ($senha !== $confirmar) {
        $msg = 'As senhas não coincidem.';
    } else {
        try {
            $sql = "SELECT idResetSenha FROM resetSenha
                    WHERE idResetSenha = :id
                      AND idUsuario = :idUsuario
                      AND verificado = 1
                      AND usado = 0
                      AND dataExpiracao >= NOW()";
            $stmt = $conexao->prepare($sql);
            $stmt->execute([
                'id'        => $_SESSION['reset_id_token'],
                'idUsuario' => $_SESSION['reset_id_usuario'],
            ]);

            if (!$stmt->fetch()) {
                $msg = 'Sua verificação expirou. Solicite a recuperação de senha novamente.';
            } else {
                $hash = password_hash($senha, PASSWORD_DEFAULT);

                $conexao->beginTransaction();

                $updateUser = $conexao->prepare("UPDATE usuario SET senha = :senha WHERE idUsuario = :id");
                $updateUser->execute(['senha' => $hash, 'id' => $_SESSION['reset_id_usuario']]);

                $updateToken = $conexao->prepare("UPDATE resetSenha SET usado = 1 WHERE idResetSenha = :id");
                $updateToken->execute(['id' => $_SESSION['reset_id_token']]);

                $conexao->commit();

                // Limpa a sessão de recuperação — ela não deve mais ser reutilizável.
                unset($_SESSION['reset_email'], $_SESSION['reset_id_usuario'], $_SESSION['reset_verified'], $_SESSION['reset_id_token']);

                header("Location: senha-alterada.php");
                exit();
            }
        } catch (PDOException $e) {
            $msg = 'Erro de banco de dados: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Criar nova senha</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../CSS/LoginMockup.css">
</head>
<body>

  <aside class="panel-left" aria-label="Imagem ilustrativa"></aside>

  <main class="panel-right">
    <form class="form" method="POST" action="">

      <figure aria-label="Logo da empresa">
        <img src="../assets/Logo.png" alt="Logo da empresa" class="logo-image">
      </figure>

      <header>
        <h1 class="title">Criar nova senha</h1>
        <p class="subtitle">Digite sua nova senha abaixo.</p>
      </header>

      <?php if ($msg): ?>
        <p class="msg msg-error"><?= htmlspecialchars($msg) ?></p>
      <?php endif; ?>

      <section class="content">
        <div class="field">
          <label for="password">Nova senha</label>
          <input
            type="password"
            id="password"
            name="password"
            placeholder="Digite sua nova senha"
            minlength="8"
            required
          >
        </div>

        <div class="field">
          <label for="confirm-password">Confirmar senha</label>
          <input
            type="password"
            id="confirm-password"
            name="confirm-password"
            placeholder="Digite novamente a senha"
            minlength="8"
            required
          >
        </div>
      </section>

      <footer class="footer-form">
        <button type="submit">Redefinir senha</button>
      </footer>

    </form>
  </main>

</body>
</html>
