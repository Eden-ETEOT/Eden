<?php
session_start();

if (empty($_SESSION["condominio"]["cnpj"])) {
    header("Location: CadastroSindico3.php");
    exit;
}

$logradouro = $_SESSION["condominio"]["logradouro"] ?? "";
$numero = $_SESSION["condominio"]["numero"] ?? "";
$bairro = $_SESSION["condominio"]["bairro"] ?? "";
$cidade = $_SESSION["condominio"]["cidade"] ?? "";
$uf = $_SESSION["condominio"]["uf"] ?? "";
$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $logradouro = trim($_POST["logradouro"]);
    $numero = trim($_POST["numero"]);
    $bairro = trim($_POST["bairro"]);
    $cidade = trim($_POST["Cidade"]);
    $uf = trim($_POST["uf"]);

    if (empty($logradouro) || empty($numero) || empty($bairro) || empty($cidade) || empty($uf)) {
        $erro = "Preencha todos os campos!";
    }

    if (empty($erro)) {
        $_SESSION["condominio"]["logradouro"] = $logradouro;
        $_SESSION["condominio"]["numero"] = $numero;
        $_SESSION["condominio"]["bairro"] = $bairro;
        $_SESSION["condominio"]["cidade"] = $cidade;
        $_SESSION["condominio"]["uf"] = $uf;

        header("Location: CadastroSindico5.php");
        exit;
    }
}

$estados = [
    "AC" => "Acre", "AL" => "Alagoas", "AP" => "Amapá", "AM" => "Amazonas",
    "BA" => "Bahia", "CE" => "Ceará", "DF" => "Distrito Federal", "ES" => "Espírito Santo",
    "GO" => "Goiás", "MA" => "Maranhão", "MT" => "Mato Grosso", "MS" => "Mato Grosso do Sul",
    "MG" => "Minas Gerais", "PA" => "Pará", "PB" => "Paraíba", "PR" => "Paraná",
    "PE" => "Pernambuco", "PI" => "Piauí", "RJ" => "Rio de Janeiro", "RN" => "Rio Grande do Norte",
    "RS" => "Rio Grande do Sul", "RO" => "Rondônia", "RR" => "Roraima", "SC" => "Santa Catarina",
    "SP" => "São Paulo", "SE" => "Sergipe", "TO" => "Tocantins"
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Cadastro Síndico 4</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../CSS/CadastroSindico.css">
</head>
<body>
  <main class="panel-rigth">

    <form class="form" action="CadastroSindico4.php" method="post">

      <figure aria-label="Logo da empresa">
        <img src="../../assets/Logo.png" alt="Logo da empresa" class="logo-image">
      </figure>

      <header>
        <h1 class="title">Crie sua Conta</h1>
        <p class="subtitle">Endereço</p>
      </header>

      <?php if (!empty($erro)): ?>
        <p class="alerta-danger"><?php echo htmlspecialchars($erro); ?></p>
      <?php endif; ?>

      <section class="content">

        <div class="field">
          <label for="logradouro">Logradouro</label>
          <input type="text" id="logradouro" name="logradouro" value="<?php echo htmlspecialchars($logradouro); ?>" required>
        </div>

        <div class="field">
          <label for="numero">Número</label>
          <input type="text" id="numero" name="numero" value="<?php echo htmlspecialchars($numero); ?>" required>
        </div>

        <div class="field">
          <label for="bairro">Bairro</label>
          <input type="text" id="bairro" name="bairro" value="<?php echo htmlspecialchars($bairro); ?>" required>
        </div>

        <div class="field">
          <label for="Cidade">Cidade</label>
          <input type="text" id="Cidade" name="Cidade" value="<?php echo htmlspecialchars($cidade); ?>" required>
        </div>

        <div class="field">
          <label for="uf">UF</label>
          <div class="field">
            <select id="uf" name="uf" required>
              <option value="" disabled <?php echo empty($uf) ? "selected" : ""; ?>>Selecione</option>
              <?php foreach ($estados as $sigla => $nomeEstado): ?>
                <option value="<?php echo $sigla; ?>" <?php echo ($uf == $sigla) ? "selected" : ""; ?>>
                  <?php echo $nomeEstado; ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
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
