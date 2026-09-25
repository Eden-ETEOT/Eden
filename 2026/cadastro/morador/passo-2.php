<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    include "../../config/conexao.php";

    // ---- Recebe os dados da etapa 1 (nome, cpf, foto) e valida ----
    if (isset($_POST["cpf"])) {
        $nomeRecebido = trim($_POST["nome"]);
        $cpfRecebido = trim($_POST["cpf"]);

        $erroEtapa1 = "";

        if (empty($nomeRecebido) || empty($cpfRecebido)) {
            $erroEtapa1 = "Preencha todos os campos!";
        } else {
            $stmt = $conexao->prepare("SELECT idUsuario FROM usuario WHERE CPF = :cpf");
            $stmt->execute(["cpf" => $cpfRecebido]);
            if ($stmt->rowCount() > 0) {
                $erroEtapa1 = "CPF já cadastrado!";
            }
        }

        if (!empty($erroEtapa1)) {
            $_SESSION["erro_moradorEtapa1"] = $erroEtapa1;
            $_SESSION["cadastroMorador"]["nome"] = $nomeRecebido;
            $_SESSION["cadastroMorador"]["cpf"] = $cpfRecebido;
            header("Location: passo-1.php");
            exit;
        }

        // Foto opcional: move para pasta permanente só se enviada
        $nomeArquivo = null;
        if (!empty($_FILES["foto"]["name"]) && $_FILES["foto"]["error"] === UPLOAD_ERR_OK) {
            $extensao = pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION);
            $nomeArquivo = uniqid("foto_") . "." . $extensao;
            $pastaDestino = "../../uploads/usuarios/";
            move_uploaded_file($_FILES["foto"]["tmp_name"], $pastaDestino . $nomeArquivo);
        }

        $_SESSION["cadastroMorador"]["nome"] = $nomeRecebido;
        $_SESSION["cadastroMorador"]["cpf"] = $cpfRecebido;
        $_SESSION["cadastroMorador"]["foto"] = $nomeArquivo;
    }

    // ---- Recebe a auto-submissão desta própria etapa (email, telefone, senha) e finaliza o cadastro ----
    if (isset($_POST["senha"])) {
        $emailRecebido = trim($_POST["email"]);
        $telefoneRecebido = trim($_POST["telefone"]);
        $senhaRecebida = $_POST["senha"];
        $confirmarSenhaRecebida = $_POST["confirmar_senha"];

        $erro = "";
        if (empty($emailRecebido) || empty($telefoneRecebido) || empty($senhaRecebida) || empty($confirmarSenhaRecebida)) {
            $erro = "Preencha todos os campos!";
        } elseif (!filter_var($emailRecebido, FILTER_VALIDATE_EMAIL)) {
            $erro = "E-mail inválido!";
        } elseif (strlen(preg_replace('/\D/', '', $telefoneRecebido)) < 10 || strlen(preg_replace('/\D/', '', $telefoneRecebido)) > 11) {
            $erro = "Telefone inválido! Use DDD + número.";
        } elseif (strlen($senhaRecebida) < 6) {
            $erro = "A senha deve ter pelo menos 6 caracteres!";
        } elseif ($senhaRecebida !== $confirmarSenhaRecebida) {
            $erro = "As senhas não conferem!";
        }

        if (empty($erro)) {
            $stmt = $conexao->prepare("SELECT idUsuario FROM usuario WHERE email = :email");
            $stmt->execute(["email" => $emailRecebido]);
            if ($stmt->rowCount() > 0) {
                $erro = "E-mail já cadastrado!";
            }
        }

        $_SESSION["cadastroMorador"]["email"] = $emailRecebido;

        if (empty($erro)) {
            $nome = $_SESSION["cadastroMorador"]["nome"];
            $cpf = $_SESSION["cadastroMorador"]["cpf"];
            $foto = $_SESSION["cadastroMorador"]["foto"];
            $senhaCriptografada = password_hash($senhaRecebida, PASSWORD_DEFAULT);

            $stmt = $conexao->prepare(
                "INSERT INTO usuario (email, senha, CPF, telefone, nome, foto)
                 VALUES (:email, :senha, :cpf, :telefone, :nome, :foto)"
            );
            $stmt->execute([
                "email" => $emailRecebido,
                "senha" => $senhaCriptografada,
                "cpf" => $cpf,
                "telefone" => $telefoneRecebido,
                "nome" => $nome,
                "foto" => $foto,
            ]);

            $usuarioId = $conexao->lastInsertId();

            // Login automático com as chaves que Elements/auth.php espera.
            // Conta criada em nível inicial, sem vínculo — o aceite do convite (se houver) vem a seguir.
            include "../../Elements/condominio.php";
            session_regenerate_id(true);
            $_SESSION["id_usuario"] = (int) $usuarioId;
            $_SESSION["nome"] = $nome;
            $_SESSION["id_condominio"] = resolverCondominio($conexao, (int) $usuarioId);
            $tokenPendente = trim($_SESSION["convite_token"] ?? '');
            unset($_SESSION["cadastroMorador"]);

            if ($tokenPendente !== '') {
                header("Location: ../../convite/aceitar.php?token=" . urlencode($tokenPendente));
            } else {
                header("Location: ../../dashboard.php");
            }
            exit;
        }
    }
}

// Não deixa acessar essa etapa sem ter completado a etapa 1
if (empty($_SESSION["cadastroMorador"]["cpf"])) {
    header("Location: passo-1.php");
    exit;
}

$email = $_POST["email"] ?? ($_SESSION["cadastroMorador"]["email"] ?? "");
$telefone = $_POST["telefone"] ?? "";
$erro = $erro ?? "";
$conviteInfo = null;
try {
    require_once "../../Elements/convites.php";
    $conviteInfo = conviteDaSessao($conexao);
$conviteTokenInvalido = (trim($_SESSION['convite_token'] ?? '') !== '' && $conviteInfo === null);
} catch (Throwable $e) {
    $conviteInfo = null;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Cadastro Morador 2 Mockup</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../CSS/CadastroMoradorMockup.css">
<?php include '../../Elements/favicon.php'; ?>
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
      <?php if ($conviteInfo !== null): ?>
      <p class="alerta-info">Cadastro referente ao convite para <strong><?= htmlspecialchars($conviteInfo['condominioNome']) ?></strong>
      — Bloco <?= htmlspecialchars($conviteInfo['bloco']) ?>, apto <?= htmlspecialchars($conviteInfo['numResid']) ?>
      (<?= htmlspecialchars($conviteInfo['tipoMorador']) ?>).</p>
      <?php endif; ?>
      <?php if ($conviteTokenInvalido): ?>
      <p class="alerta-danger">Este link de convite não é mais válido (expirou ou já foi usado). Você pode concluir o cadastro, mas será preciso pedir um novo link ao síndico para vincular seu apartamento.</p>
      <?php endif; ?>

      <section class="content">

        <div class="field">
          <label for="email">Email</label>
          <input
            type="email"
            id="email"
            name="email"
            placeholder="@gmail.com"
            value="<?php echo htmlspecialchars($email); ?>"
          >
        </div>

        <div class="field">
          <label for="telefone">Telefone</label>
          <input
            type="tel"
            id="telefone" placeholder="(00) 00000-0000" maxlength="15"
            name="telefone"
            value="<?php echo htmlspecialchars($telefone); ?>"
            required
          >
        </div>

        <div class="field">
          <label for="senha">Senha</label>
          <input
            type="password"
            id="senha"
            name="senha"
            required
          >

           <div class="field">
          <label for="confirmar_senha">Confirmar  senha</label>
          <input
            type="password"
            id="confirmar_senha"
            name="confirmar_senha"
            required
          >
       
    
</div>

      </section>

      <footer class="footer-form">

        <button type="submit">
          Cadastrar
        </button>

      </footer>

      <div class="divider" aria-hidden="true">
        <hr>
        <span>xx</span>
        <hr>
      </div>

      <p class="register">
        <a href="#">Já tem uma conta? <a href="../../auth/login.php">Faça seu login</a></a>
      </p>

    </form>

  </main>

  <aside class="panel-rigth" aria-label="Imagem ilustrativa"></aside>


  <script src="../../js/mascaras.js"></script>
</body>
</html>
