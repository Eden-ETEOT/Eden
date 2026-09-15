<?php
session_start();

if (empty($_SESSION["cadastro"]["senha"])) {
    header("Location: passo-2.php");
    exit;
}

$cnpj = $_SESSION["condominio"]["cnpj"] ?? "";
$nome_condominio = $_SESSION["condominio"]["nome"] ?? "";
$cep = $_SESSION["condominio"]["cep"] ?? "";
$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include "../../config/conexao.php";

    $cnpj = trim($_POST["cnpj"]);
    $nome_condominio = trim($_POST["nome"]);
    $cep = trim($_POST["CEP"]);

    if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] !== UPLOAD_ERR_NO_FILE) {
      $foto = $_FILES["foto"];
      $tiposPermitidos = ["image/jpeg" => "jpg", "image/png" => "png", "image/gif" => "gif", "image/webp" => "webp"];
      $tamanhoMaximo = 5 * 1024 * 1024;
      $imagem = $foto["error"] === UPLOAD_ERR_OK ? getimagesize($foto["tmp_name"]) : false;

      if ($foto["error"] !== UPLOAD_ERR_OK || $foto["size"] > $tamanhoMaximo || $imagem === false || !isset($tiposPermitidos[$imagem["mime"]])) {
        $erro = "Envie uma imagem JPG, PNG, GIF ou WEBP de até 5 MB.";
      } else {
        $diretorioUpload = __DIR__ . "/../../uploads/condominios";
        if (!is_dir($diretorioUpload) && !mkdir($diretorioUpload, 0755, true)) {
          $erro = "Não foi possível preparar o envio da foto.";
        } else {
          $nomeArquivo = bin2hex(random_bytes(16)) . "." . $tiposPermitidos[$imagem["mime"]];
          $caminhoArquivo = $diretorioUpload . DIRECTORY_SEPARATOR . $nomeArquivo;

          if (!move_uploaded_file($foto["tmp_name"], $caminhoArquivo)) {
            $erro = "Não foi possível salvar a foto.";
          } else {
            $_SESSION["condominio"]["foto"] = "uploads/condominios/" . $nomeArquivo;
          }
        }
      }
    }

    if (empty($cnpj) || empty($nome_condominio) || empty($cep)) {
        $erro = "Preencha todos os campos!";
    } else {
        // Verifica se o CNPJ já está cadastrado
        $stmt = $conexao->prepare("SELECT idCondominio FROM condominio WHERE CNPJ = :cnpj");
        $stmt->execute(["cnpj" => $cnpj]);

        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            $erro = "Condomínio já cadastrado (CNPJ já existe)!";
        }
    }

    if (empty($erro)) {
        $_SESSION["condominio"]["cnpj"] = $cnpj;
        $_SESSION["condominio"]["nome"] = $nome_condominio;
        $_SESSION["condominio"]["cep"] = $cep;

        header("Location: passo-4.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Cadastro Síndico 3</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../CSS/CadastroSindico.css">
<?php include '../../Elements/favicon.php'; ?>
</head>
<body>
  <main class="panel-rigth">

    <form class="form" action="passo-3.php" method="post" enctype="multipart/form-data">

      <figure aria-label="Logo da empresa">
        <img src="../../assets/Logo.png" alt="Logo da empresa" class="logo-image">
      </figure>

      <header>
        <h1 class="title">Crie sua Conta</h1>
        <p class="subtitle">Cadastrar Condomínio</p>
      </header>

      <?php if (!empty($erro)): ?>
        <p class="alerta-danger"><?php echo htmlspecialchars($erro); ?></p>
      <?php endif; ?>

      <section class="content">

        <div class="field">
          <label for="cnpj">CNPJ</label>
          <input type="text" id="cnpj" name="cnpj" placeholder="00.000.000/0000-00" value="<?php echo htmlspecialchars($cnpj); ?>" inputmode="numeric" maxlength="18" autocomplete="off" required>
        </div>

        <div class="field">
          <label for="nome">Nome</label>
          <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($nome_condominio); ?>" required>
        </div>

        <div class="field">
          <label for="CEP">CEP</label>
          <input type="text" id="CEP" name="CEP" value="<?php echo htmlspecialchars($cep); ?>" required>
        </div>

        <div class="field">
          <label for="foto">Foto do condomínio</label>
          <input type="file" id="foto" name="foto" accept="image/*">
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
