<?php
session_start();

$nome = $_SESSION["cadastroMorador"]["nome"] ?? "";
$cpf = $_SESSION["cadastroMorador"]["cpf"] ?? "";

$erro = $_SESSION["erro_moradorEtapa1"] ?? "";
unset($_SESSION["erro_moradorEtapa1"]);
$conviteInfo = null;
try {
    include "../../config/conexao.php";
    include "../../Elements/convites.php";
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
  <title>Cadastro Morador 1 Mockup</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../CSS/CadastroMoradorMockup.css">
<?php include '../../Elements/favicon.php'; ?>
</head>
<body>
  <main class="panel-left">

    <form class="form" action="passo-2.php" method="post" enctype="multipart/form-data">

      <figure aria-label="Logo da empresa">
        <img src="../../assets/Logo.png" alt="Logo da empresa" class="logo-image">
      </figure>

      <header>
        <h1 class="title">Crie sua Conta</h1>
        <p class="subtitle">Insira Suas Informações</p>
      </header>

      <?php if (!empty($erro)): ?>
      <p class="alerta-danger"><?php echo htmlspecialchars($erro); ?></p>
      <?php endif; ?>
      <?php if ($conviteInfo !== null): ?>
      <p class="alerta-info">Você foi convidado(a) para <strong><?= htmlspecialchars($conviteInfo['condominioNome']) ?></strong>
      — Bloco <?= htmlspecialchars($conviteInfo['bloco']) ?>, apto <?= htmlspecialchars($conviteInfo['numResid']) ?>
      (<?= htmlspecialchars($conviteInfo['tipoMorador']) ?>). Complete seu cadastro para aceitar.</p>
      <?php endif; ?>
      <?php if ($conviteTokenInvalido): ?>
      <p class="alerta-danger">Este link de convite não é mais válido (expirou ou já foi usado). Você pode concluir o cadastro, mas será preciso pedir um novo link ao síndico para vincular seu apartamento.</p>
      <?php endif; ?>

      <section class="content">

        <div class="field">
          <label for="nome">Nome</label>
          <input
            type="text"
            id="nome"
            name="nome"
            value="<?php echo htmlspecialchars($nome); ?>"
          >
        </div>

        <div class="field foto">
          <label for="cpf">CPF</label>
          <input
            type="text"
            id="cpf"
            name="cpf"
            placeholder="000.000.000-00"
            value="<?php echo htmlspecialchars($cpf); ?>"
            inputmode="numeric"
            maxlength="14"
            autocomplete="off"
            required
          >
        </div>

        <div class="field">
          <label for="foto">Foto</label>
          <input type="file" id="foto" name="foto" accept="image/*">
        </div>

      </section>

      <footer class="footer-form">

        <button type="submit">
          Próximo
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
