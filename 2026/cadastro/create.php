<?php
require_once "../config/conexao.php";

$msg = '';
$msg_tipo = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);
    $senha = $_POST["senha"];
    $cpf = trim($_POST["cpf"]);
    $telefone = trim($_POST["telefone"]);

    try {
        $sql = "INSERT INTO usuario (nome, email, senha, cpf, telefone)
                VALUES (:nome, :email, :senha, :cpf, :telefone)";

        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(":nome", $nome);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":senha", password_hash($senha, PASSWORD_DEFAULT));
        $stmt->bindParam(":cpf", $cpf);
        $stmt->bindParam(":telefone", $telefone);

        if ($stmt->execute()) {
            $msg = 'Conta criada com sucesso! Redirecionando para o login...';
            $msg_tipo = 'success';
            header("refresh:2;url=../Login/loginMockup/index.php");
        }
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $msg = 'Este e-mail ou CPF já está cadastrado.';
            $msg_tipo = 'error';
        } else {
            $msg = 'Erro ao cadastrar: ' . $e->getMessage();
            $msg_tipo = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Criar conta - Eden Systems</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../CSS/LoginMockup.css">
    <style>
        .msg-success {
            color: var(--green3-default);
            background: var(--green3-50);
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            font-family: var(--fonteTextos);
            font-size: 0.875rem;
        }
        .msg-error {
            color: var(--red-default);
            background: var(--red-50);
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            font-family: var(--fonteTextos);
            font-size: 0.875rem;
        }
        input[type="text"],
        input[type="tel"] {
            width: 100%;
            padding: var(--spacing200) var(--spacing300);
            border: 1px solid var(--gray200);
            color: var(--corNeutra2);
            font-size: 1rem;
            outline: none;
            border-radius: 8px;
            height: 2rem;
        }
        input[type="text"]::placeholder,
        input[type="tel"]::placeholder {
            color: var(--gray300);
        }
        .panel-left-cadastro {
            background: var(--gray300);
            background-image: url(../../2026/assets/imageLogin.png);
            width: 50vw;
            height: 100vh;
        }
        .footer-cadastro {
            display: flex;
            flex-direction: column;
            width: 100%;
            gap: var(--spacing400);
            margin-top: var(--spacing400);
        }
        .login-link {
            text-align: center;
            font-size: 0.875rem;
            font-family: var(--fonteTextos);
            color: var(--corNeutra2);
        }
        .login-link a {
            font-weight: var(--bold);
            color: var(--orange1-default);
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <aside class="panel-left-cadastro"></aside>

    <main class="panel-right">

        <form action="" method="POST" class="form">

            <figure class="logo" aria-label="Logo da empresa"></figure>

            <header>
                <h1 class="title">Criar conta</h1>
                <p class="subtitle">Preencha os dados para se cadastrar</p>
            </header>

            <?php if ($msg): ?>
                <p class="msg-<?= $msg_tipo ?>"><?= htmlspecialchars($msg) ?></p>
            <?php endif; ?>

            <section class="content">

                <div class="field">
                    <label for="nome">Nome completo</label>
                    <input type="text" id="nome" name="nome" placeholder="Digite seu nome" required>
                </div>

                <div class="field">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" placeholder="Digite seu e-mail" required>
                </div>

                <div class="field">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" placeholder="Crie uma senha" required>
                </div>

                <div class="field">
                    <label for="cpf">CPF</label>
                    <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" required>
                </div>

                <div class="field">
                    <label for="telefone">Telefone</label>
                    <input type="tel" id="telefone" name="telefone" placeholder="(21) 99999-9999" required>
                </div>

            </section>

            <footer class="footer-cadastro">

                <button type="submit" class="btn-primary-orange-small">
                    Cadastrar
                </button>

                <p class="login-link">
                    Já tem uma conta? <a href="../Login/loginMockup/index.php">Faça login</a>
                </p>

            </footer>

        </form>

    </main>

</body>
</html>
