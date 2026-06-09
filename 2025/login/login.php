<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in - Morador</title>
    <link rel="stylesheet" href="../css/Login.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <!-- Background Image -->
        <img src="../assets/imageLogin.png" 
             alt="Background" 
             class="background-image">

        <!-- Content -->
         <div class="form">
            <div class="content-wrapper">
            <!-- Logo -->
            <img src="../assets/logoFundoBranco.png" 
                 alt="Logo" 
                 class="logo">

            <!-- Title -->
            <h1 class="title">Bem-vindo de volta!</h1>

            <!-- Subtitle -->
            <p class="subtitle">Insira suas credenciais para acessar sua conta.</p>

            <!-- Login Form -->
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Processar o formulário aqui
                $usuario = $_POST['usuario'] ?? '';
                $senha = $_POST['senha'] ?? '';
                $lembrar = isset($_POST['lembrar']);
                
                // Adicione aqui a lógica de autenticação
                // Por exemplo: validar com banco de dados
                
                // Exemplo de redirecionamento após login bem-sucedido:
                // header('Location: dashboard.php');
                // exit;
            }
            ?>

            <form method="POST" action="../dashboard/dashboard.php">
                <!-- Campo Usuário -->
                <div class="form-group">
                    <label for="usuario" class="form-label">Usuário</label>
                    <input type="email" 
                           id="usuario" 
                           name="usuario" 
                           class="form-input" 
                           placeholder="Digite seu e-mail"
                           required>
                </div>

                <!-- Campo Senha -->
                <div class="form-group">
                    <label for="senha" class="form-label">Senha</label>
                    <input type="password" 
                           id="senha" 
                           name="senha" 
                           class="form-input" 
                           placeholder="***********"
                           required>
                </div>

                <!-- Checkbox e Link Esqueceu Senha -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <div class="checkbox-wrapper" style="margin-bottom: 0;">
                        <input type="checkbox" 
                               id="lembrar" 
                               name="lembrar" 
                               class="checkbox">
                        <label for="lembrar" class="checkbox-label">Lembrar credenciais</label>
                    </div>
                    <a href="#" class="forgot-password">Esqueceu sua senha?</a>
                </div>

                <!-- Botão Entrar -->
                <button type="submit" class="btn-submit">ENTRAR</button>
            </form>

            <!-- Divider -->
            <div class="divider">
                <div class="divider-line"></div>
                <span class="divider-text">Ou</span>
                <div class="divider-line"></div>
            </div>

            <!-- Link Criar Conta -->
            <p class="signup-text">
                Não tem conta? 
                <a href="../cadastrar/cadastrar.php" class="signup-link">Criar conta</a>
            </p>
            </div>
        </div>
    </div>
</body>
</html>
