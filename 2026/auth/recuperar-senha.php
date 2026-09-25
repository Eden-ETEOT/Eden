<?php
session_start();
require_once "../config/conexao.php";
require_once "../config/enviarEmail.php";

$msg = '';
$tipoMsg = 'error'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["usuario"]);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = 'Digite um e-mail válido.';
    } else {
        try {
            $sql = "SELECT idUsuario, nome FROM usuario WHERE email = :email AND ativo = 1";
            $stmt = $conexao->prepare($sql);
            $stmt->execute(["email" => $email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                $msg = 'Este e-mail não está cadastrado no sistema.';
                $tipoMsg = 'error';
            } else {
                $msg = 'Se este e-mail estiver cadastrado, um código de verificação foi enviado.';
                $tipoMsg = 'success';
            }

            if ($usuario) {
                $codigo = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $expira = date('Y-m-d H:i:s', strtotime('+15 minutes'));

                $sqlInsert = "INSERT INTO resetSenha (idUsuario, codigo, dataExpiracao)
                              VALUES (:idUsuario, :codigo, :dataExpiracao)";
                $stmtInsert = $conexao->prepare($sqlInsert);
                $stmtInsert->execute([
                    'idUsuario'     => $usuario['idUsuario'],
                    'codigo'        => $codigo,
                    'dataExpiracao' => $expira,
                ]);

                $nome = $usuario['nome'];
                $corpo = "
                    <p>Olá, {$nome}.</p>
                    <p>Use o código abaixo para redefinir sua senha no Éden Systems:</p>
                    <h2 >{$codigo}</h2>
                    <p>Esse código expira em 15 minutos. Se você não solicitou isso, ignore este e-mail.</p>
                ";

                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_id_usuario'] = $usuario['idUsuario'];
                session_write_close(); // libera a trava: outros cliques não enfileiram atrás do SMTP
                $resultado = enviarEmail($email, 'Código para redefinir sua senha', $corpo);

                if ($resultado['ok']) {
                    header("Location: verificar-email.php");
                    exit();
                } else {
                    // Falha real de envio (ex.: credenciais SMTP erradas em mailer_config.php)
                    $msg = 'Não foi possível enviar o e-mail agora. Tente novamente em instantes.';
                    $tipoMsg = 'error';
                }
            }
        } catch (PDOException $e) {
            $msg = 'Erro de banco de dados: ' . $e->getMessage();
            $tipoMsg = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Recuperar senha</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../CSS/LoginMockup.css">
<?php include '../Elements/favicon.php'; ?>
</head>
<body>

  <aside class="panel-left" aria-label="Imagem ilustrativa">

  </aside>

  <main class="panel-right">

    <form class="form" method="POST" action="">

      <figure aria-label="Logo da empresa">
        <img src="../assets/Logo.png" alt="Logo da empresa" class="logo-image">
      </figure>

      <header>
        <h1 class="title">Recuperar senha</h1>
        <p class="subtitle">Digite seu e-mail para redefinir a senha.</p>
      </header>

      <?php if ($msg): ?>
        <p class="msg msg-<?= $tipoMsg ?>"><?= htmlspecialchars($msg) ?></p>
      <?php endif; ?>

      <section class="content">

        <div class="field">
          <label for="usuario">E-mail</label>
          <input
            type="email"
            id="usuario"
            name="usuario"
            placeholder="Digite seu e-mail"
            required
            class ="input-field-default-sm"
          >
        </div>

      </section>

      <footer class="footer-form">

        <button type="submit" class="btn-primary-orange-small">
          Recuperar senha
        </button>

      </footer>

      <div class="divider" aria-hidden="true">
        <hr>
        <span>Ou</span>
        <hr>
      </div>

      <p class="register">
        <a href="login.php">Voltar para login</a>
      </p>

    </form>

  </main>

</body>
</html>
