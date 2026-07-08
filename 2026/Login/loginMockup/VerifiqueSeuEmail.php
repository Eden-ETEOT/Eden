<?php
session_start();
require_once "../../config/conexao.php";
require_once "../../config/enviarEmail.php";

if (empty($_SESSION['reset_email']) || empty($_SESSION['reset_id_usuario'])) {
    header("Location: RecuperarSenha.php");
    exit();
}

$msg = '';
$tipoMsg = 'error';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Reenviar código
    if (isset($_POST['reenviar'])) {
        try {
            $sql = "SELECT nome FROM usuario WHERE idUsuario = :id";
            $stmt = $conexao->prepare($sql);
            $stmt->execute(['id' => $_SESSION['reset_id_usuario']]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            $codigo = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expira = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            $sqlInsert = "INSERT INTO resetSenha (idUsuario, codigo, dataExpiracao)
                          VALUES (:idUsuario, :codigo, :dataExpiracao)";
            $stmtInsert = $conexao->prepare($sqlInsert);
            $stmtInsert->execute([
                'idUsuario'     => $_SESSION['reset_id_usuario'],
                'codigo'        => $codigo,
                'dataExpiracao' => $expira,
            ]);

            $nome = htmlspecialchars($usuario['nome'] ?? '');
            $corpo = "
                <p>Olá, {$nome}.</p>
                <p>Aqui está seu novo código de verificação:</p>
                <h2 style='letter-spacing:4px'>{$codigo}</h2>
                <p>Esse código expira em 15 minutos.</p>
            ";
            $resultado = enviarEmail($_SESSION['reset_email'], 'Novo código para redefinir sua senha', $corpo);

            if($resultado['ok']){
              $msg = 'Um novo código foi enviado para seu e-mail.';
              $tipoMsg = 'success';
            }else{
              $msg = 'Não foi possível reenviar o código agora. Tente novamente em instantes.';
              $tipoMsg = 'error';
            }
        } catch (PDOException $e) {
            $msg = 'Erro de banco de dados: ' . $e->getMessage();
        }
    } else {
        // Confirmar código
        $codigoDigitado = trim($_POST["codigo"] ?? '');

        if (!preg_match('/^\d{6}$/', $codigoDigitado)) {
            $msg = 'Digite os 6 dígitos do código.';
        } else {
            try {
                $sql = "SELECT idResetSenha FROM resetSenha
                        WHERE idUsuario = :idUsuario
                          AND codigo = :codigo
                          AND usado = 0
                          AND dataExpiracao >= NOW()
                        ORDER BY idResetSenha DESC
                        LIMIT 1";
                $stmt = $conexao->prepare($sql);
                $stmt->execute([
                    'idUsuario' => $_SESSION['reset_id_usuario'],
                    'codigo'    => $codigoDigitado,
                ]);
                $reset = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($reset) {
                    $update = $conexao->prepare("UPDATE resetSenha SET verificado = 1 WHERE idResetSenha = :id");
                    $update->execute(['id' => $reset['idResetSenha']]);

                    $_SESSION['reset_verified'] = true;
                    $_SESSION['reset_id_token'] = $reset['idResetSenha'];

                    header("Location: RedefinirSenhaMockup.php");
                    exit();
                } else {
                    $msg = 'Código inválido ou expirado.';
                }
            } catch (PDOException $e) {
                $msg = 'Erro de banco de dados: ' . $e->getMessage();
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
  <title>Verifique seu e-mail</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../CSS/LoginMockup.css">
</head>
<body>

  <aside class="panel-left" aria-label="Imagem ilustrativa"></aside>

  <main class="panel-right">

    <form class="form" method="POST" action="">

      <figure aria-label="Logo da empresa">
        <img src="../../assets/Logo.png" alt="Logo da empresa" class="logo-image">
      </figure>

      <header>
        <h1 class="title">Verifique seu e-mail</h1>
        <p class="subtitle">Enviamos um código de verificação para <?= htmlspecialchars($_SESSION['reset_email']) ?>.</p>
      </header>

      <?php if ($msg): ?>
        <p class="msg msg-<?= $tipoMsg ?>"><?= htmlspecialchars($msg) ?></p>
      <?php endif; ?>

      <section class="content">

        <div class="field">
          <label for="codigo">Código de verificação</label>
          <input
            type="text"
            id="codigo"
            name="codigo"
            placeholder="Digite o código de 6 dígitos"
            inputmode="numeric"
            pattern="\d{6}"
            maxlength="6"
            required
            class ="input-field-default-sm"
          >
        </div>

      </section>

      <footer class="footer-form">

        <button type="submit" class="btn-primary-orange-small">
          Verificar código
        </button>

      </footer>

      <div class="divider" aria-hidden="true">
        <hr>
        <span>Ou</span>
        <hr>
      </div>

      <p class="register">
        <span> Não recebeu? <button type="submit" name="reenviar" value="1" class="link-button" formnovalidate>Reenviar e-mail</button></span>
      
      </p>

    </form>

  </main>

</body>
</html>
